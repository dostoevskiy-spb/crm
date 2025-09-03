# Инструкция: реализация PHP-кода по ТЗ с DDD и PSR-12 (фреймворк-независимо, интеграция с Laravel 12, маппинг Doctrine)

> Цель: создать чистую, сопровождаемую кодовую базу, где **Domain** и **Application** слои не зависят от фреймворка, а интеграция с **Laravel 12** происходит исключительно через **Infrastructure**. Для хранения данных используется **Doctrine ORM** (атрибуты PHP), при необходимости через обёртку для Laravel.  PostgreSQL 18

---

## 0) Общие принципы

- Соблюдать **PSR-12** для форматирования и стиля.
- Кодировать на **PHP ≥ 8.2** (типизация, readonly, enums, атрибуты).
- DDD: **чистый домен**, явные **Value Object**, **Entity**, **Domain Service**, **Repository интерфейсы**, **Domain Event**.
- **Никаких ссылок на Laravel** в `Domain` и `Application`.
- Инфраструктура отвечает за:
    - реализацию репозиториев через **Doctrine**,
    - интеграцию с компонентами Laravel (Service Provider, контейнер, очереди/шина событий/конфиги/логгер),
    - миграции/скрипты и настройки.
- Все побочные эффекты идут через **порт/адаптер**.
- Паттерны: **CQRS (команды/запросы)** в Application слое, **Unit of Work/Transaction Script** через Doctrine `EntityManager`.

---

## 1) Структура каталогов (моно-репо)

```
app/
  Domain/
    {BoundedContext}/
      Model/
        Entity/
        ValueObject/
        Enum/``
        Event/
        Exception/
        Service/            # Domain Services (pure)
      Repository/
        {Aggregate}Repository.php  # интерфейсы
  Application/
    {BoundedContext}/
      Command/
      Query/
      DTO/
      Handler/
      Service/              # Application Services (use-cases)
      Exception/
      Port/                 # интерфейсы портов (например EmailSenderPort)
  Infrastructure/
    Persistence/
      Doctrine/
        Mapping/            # если нужны XML/YAML (по умолчанию — атрибуты в Entity)
        Repository/         # реализации репозиториев
        Migrations/         # миграции Doctrine (если выбрано)
        DoctrineFactory.php
        Transactional.php
    Bus/
      CommandBus/           # адаптер под выбранный шиной
      QueryBus/
      EventBus/
    Http/
      Controller/           # тонкие контроллеры Laravel, делегируют в Application
      Request/
      Middleware/
    Queue/
    Mail/
    Logger/
    Providers/
      DomainServiceProvider.php
      ApplicationServiceProvider.php
      InfrastructureServiceProvider.php
  Shared/
    Clock/
    Id/
    Bus/
    Serializer/
bootstrap/
config/
tests/
```

> **Примечание:** если используется «мульти-модуль», допускается отдельный пакет/namespace на контекст, но принципы сохраняются.


## 4) Домейн-слой (чистый PHP)

### 4.1 Пример Value Object

```php
<?php
declare(strict_types=1);

namespace App\Domain\Catalog\Model\ValueObject;

final readonly class Sku
{
    public function __construct(private string $value)
    {
        $v = trim($value);
        if ($v === '' || mb_strlen($v) > 64) {
            throw new \InvalidArgumentException('Invalid SKU');
        }
        $this->value = $v;
    }

    public function value(): string { return $this->value; }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string { return $this->value; }
}
```

### 4.2 Пример Entity (с атрибутами Doctrine)

```php
<?php
declare(strict_types=1);

namespace App\Domain\Catalog\Model\Entity;

use App\Domain\Catalog\Model\ValueObject\Sku;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'product')]
class Product
{
    #[ORM\Id]
    #[ORM\Column(type: 'guid')]
    private string $id;

    #[ORM\Embedded(class: Sku::class, columnPrefix: 'sku_')]
    private Sku $sku;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private string $price;

    public function __construct(string $id, Sku $sku, string $name, string $price)
    {
        $this->id    = $id;
        $this->sku   = $sku;
        $this->name  = $name;
        $this->price = $price;
    }

    public function id(): string { return $this->id; }
    public function sku(): Sku { return $this->sku; }
    public function name(): string { return $this->name; }
    public function price(): string { return $this->price; }

    public function rename(string $newName): void
    {
        $this->name = $newName;
    }
}
```

### 4.3 Репозиторий (порт)

```php
<?php
declare(strict_types=1);

namespace App\Domain\Catalog\Repository;

use App\Domain\Catalog\Model\Entity\Product;

interface ProductRepository
{
    public function byId(string $id): ?Product;
    public function bySku(string $sku): ?Product;
    public function add(Product $product): void;
    public function remove(Product $product): void;
}
```

### 4.4 Доменные события (опционально)

```php
<?php
declare(strict_types=1);

namespace App\Domain\Catalog\Model\Event;

use App\Domain\Catalog\Model\Entity\Product;

final readonly class ProductCreated
{
    public function __construct(public string $productId) {}
}
```

---

## 5) Application-слой (use-cases, CQRS)

### 5.1 DTO + Command

```php
<?php
declare(strict_types=1);

namespace App\Application\Catalog\DTO;

final readonly class CreateProductDTO
{
    public function __construct(
        public string $sku,
        public string $name,
        public string $price
    ) {}
}
```

```php
<?php
declare(strict_types=1);

namespace App\Application\Catalog\Command;

use App\Application\Catalog\DTO\CreateProductDTO;

final readonly class CreateProductCommand
{
    public function __construct(public CreateProductDTO $dto) {}
}
```

### 5.2 Handler (не зависит от фреймворка)

```php
<?php
declare(strict_types=1);

namespace App\Application\Catalog\Handler;

use App\Application\Catalog\Command\CreateProductCommand;
use App\Domain\Catalog\Model\Entity\Product;
use App\Domain\Catalog\Model\ValueObject\Sku;
use App\Domain\Catalog\Repository\ProductRepository;

final class CreateProductHandler
{
    public function __construct(private ProductRepository $products, private \App\Shared\Id\UuidGenerator $uuid) {}

    public function __invoke(CreateProductCommand $cmd): string
    {
        $id = $this->uuid->next();
        $product = new Product($id, new Sku($cmd->dto->sku), $cmd->dto->name, $cmd->dto->price);

        $this->products->add($product);

        return $id;
    }
}
```

### 5.3 Query + Handler

```php
<?php
declare(strict_types=1);

namespace App\Application\Catalog\Query;

final readonly class GetProductQuery
{
    public function __construct(public string $id) {}
}
```

```php
<?php
declare(strict_types=1);

namespace App\Application\Catalog\Handler;

use App\Application\Catalog\Query\GetProductQuery;
use App\Domain\Catalog\Repository\ProductRepository;

final class GetProductHandler
{
    public function __construct(private ProductRepository $products) {}

    public function __invoke(GetProductQuery $q): ?array
    {
        $p = $this->products->byId($q->id);
        if (!$p) {
            return null;
        }
        return [
            'id' => $p->id(),
            'sku' => (string)$p->sku(),
            'name' => $p->name(),
            'price' => $p->price(),
        ];
    }
}
```

---

## 6) Infrastructure: Doctrine ORM + интеграция с Laravel

### 6.1 Фабрика EntityManager

Если используется **laravel-doctrine/orm**, настройте через их сервис-провайдер. Если вручную — создайте фабрику:

```php
<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;

final class DoctrineFactory
{
    public static function create(array $dbParams, array $entityPaths): \Doctrine\ORM\EntityManagerInterface
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: $entityPaths,
            isDevMode: app()->has('env') ? app()->environment('local') : false,
        );

        $connection = DriverManager::getConnection($dbParams, $config);

        return new EntityManager($connection, $config);
    }
}
```

Пример параметров (подтягивайте из env/config Laravel):

```php
$dbParams = [
  'dbname'   => env('DB_DATABASE'),
  'user'     => env('DB_USERNAME'),
  'password' => env('DB_PASSWORD'),
  'host'     => env('DB_HOST'),
  'driver'   => 'pdo_mysql', // или pdo_pgsql
  'charset'  => 'utf8mb4',
];
```

### 6.2 Реализация репозитория

```php
<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Catalog\Model\Entity\Product;
use App\Domain\Catalog\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineProductRepository implements ProductRepository
{
    public function __construct(private EntityManagerInterface $em) {}

    public function byId(string $id): ?Product
    {
        return $this->em->find(Product::class, $id);
    }

    public function bySku(string $sku): ?Product
    {
        return $this->em->getRepository(Product::class)
            ->findOneBy(['sku.value' => $sku]); // при embeddable может отличаться
    }

    public function add(Product $product): void
    {
        $this->em->persist($product);
        $this->em->flush();
    }

    public function remove(Product $product): void
    {
        $this->em->remove($product);
        $this->em->flush();
    }
}
```

> **Замечание:** для `Embedded` полей имя колонки может быть `sku_value`. Уточните в атрибутах или настройте **Embeddables** корректно.

### 6.3 Транзакции (декоратор/хелпер)

```php
<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use Doctrine\ORM\EntityManagerInterface;

final class Transactional
{
    public function __construct(private EntityManagerInterface $em) {}

    /**
     * @template T
     * @param callable():T $fn
     * @return T
     */
    public function run(callable $fn): mixed
    {
        return $this->em->wrapInTransaction($fn);
    }
}
```

### 6.4 Интеграция с контейнером Laravel (Service Providers)

```php
<?php
declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Domain\Catalog\Repository\ProductRepository;use App\Modules\Product\Infrastructure\Persistence\Doctrine\Repository\DoctrineProductRepository;use App\Modules\Shared\Infrastructure\Persistence\Doctrine\DoctrineFactory;use Doctrine\ORM\EntityManagerInterface;use Illuminate\Support\ServiceProvider;

final class InfrastructureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EntityManagerInterface::class, function ($app) {
            $dbParams = [
                'dbname'   => config('database.connections.mysql.database'),
                'user'     => config('database.connections.mysql.username'),
                'password' => config('database.connections.mysql.password'),
                'host'     => config('database.connections.mysql.host'),
                'driver'   => 'pdo_mysql',
                'charset'  => 'utf8mb4',
            ];
            $entityPaths = [base_path('app/Domain')];
            return DoctrineFactory::create($dbParams, $entityPaths);
        });

        $this->app->bind(ProductRepository::class, DoctrineProductRepository::class);
    }
}
```

Регистрация провайдера в `config/app.php` (providers).

### 6.5 Тонкие контроллеры Laravel

```php
<?php
declare(strict_types=1);

namespace App\Infrastructure\Http\Controller;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Application\Catalog\Command\CreateProductCommand;
use App\Application\Catalog\DTO\CreateProductDTO;
use App\Application\Catalog\Handler\CreateProductHandler;

final class ProductController extends Controller
{
    public function store(Request $request, CreateProductHandler $handler)
    {
        $dto = new CreateProductDTO(
            sku: (string)$request->input('sku'),
            name: (string)$request->input('name'),
            price: (string)$request->input('price'),
        );

        $id = $handler(new CreateProductCommand($dto));

        return response()->json(['id' => $id], 201);
    }
}
```

> Контроллер **ничего не знает** о Doctrine/EntityManager. Только Application-слой.

---

## 7) Конфигурация Doctrine: Embeddables, типы и пр.

### 7.1 Embeddable для `Sku`

```php
<?php
declare(strict_types=1);

namespace App\Domain\Catalog\Model\ValueObject;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class Sku
{
    #[ORM\Column(name: 'sku_value', type: 'string', length: 64)]
    private string $value;

    public function __construct(string $value)
    {
        $v = trim($value);
        if ($v === '' || mb_strlen($v) > 64) {
            throw new \InvalidArgumentException('Invalid SKU');
        }
        $this->value = $v;
    }

    public function value(): string { return $this->value; }
    public function __toString(): string { return $this->value; }
}
```

### 7.2 Кастомные типы (опционально)

Если нужны «сильные» типы (Money, Email и т.д.), создайте кастомные доктрин-типы и зарегистрируйте в фабрике EntityManager.

---

## 8) Шина команд/запросов (порт/адаптер)

### 8.1 Порт в Application

```php
<?php
namespace App\Application\Shared\Port;

interface CommandBus { public function dispatch(object $command): mixed; }
interface QueryBus   { public function ask(object $query): mixed; }
```

### 8.2 Адаптер (пример для Tactician)

```php
<?php
declare(strict_types=1);

namespace App\Infrastructure\Bus\CommandBus;

use App\Application\Shared\Port\CommandBus;
use League\Tactician\CommandBus as LeagueBus;

final class TacticianCommandBus implements CommandBus
{
    public function __construct(private LeagueBus $bus) {}
    public function dispatch(object $command): mixed { return $this->bus->handle($command); }
}
```

> Регистрация хендлеров — в конфигурации буса (mapping команда → хендлер). В Laravel — через провайдер.

---

## 9) Тестирование

- **Unit** для домена: тестируйте сущности, VO, доменные сервисы (чисто, без БД).
- **Application**: тестируйте хендлеры с **in-memory** репозиториями/портами.
- **Infrastructure**: интеграционные тесты c Doctrine (sqlite :memory:).

Пример in-memory репозитория:

```php
<?php
declare(strict_types=1);

namespace Tests\Doubles\Repository;

use App\Domain\Catalog\Model\Entity\Product;
use App\Domain\Catalog\Repository\ProductRepository;

final class InMemoryProductRepository implements ProductRepository
{
    /** @var array<string,Product> */
    private array $data = [];

    public function byId(string $id): ?Product { return $this->data[$id] ?? null; }

    public function bySku(string $sku): ?Product
    {
        foreach ($this->data as $p) {
            if ((string)$p->sku() === $sku) return $p;
        }
        return null;
    }

    public function add(Product $product): void { $this->data[$product->id()] = $product; }
    public function remove(Product $product): void { unset($this->data[$product->id()]); }
}
```

---

## 10) Сценарий работы И-агента (чек-лист)

1. **Прочитать ТЗ**, выделить bounded contexts, агрегаты, команды/запросы, внешние интеграции.
2. **Смоделировать домен**: Entity, VO, Repository интерфейсы, Domain Events. Никаких зависимостей от Laravel/Doctrine.
3. **Спроектировать Application**: DTO, Command/Query + Handlers, Ports.
4. **Реализовать Infrastructure**:
    - Doctrine EntityManager (через фабрику или laravel-doctrine).
    - Doctrine репозитории под интерфейсы Domain.
    - Service Providers для биндингов в контейнер Laravel.
    - Адаптеры портов: Bus, Mailer, Logger, Clock, Id.
    - Контроллеры Laravel — тонкие, оркестрируют только Application.
5. **Настроить качество**: PHPCS (PSR-12), PHPStan, (опц.) CS Fixer.
6. **Написать тесты**: Unit для Domain, Application с in-memory, Integration для Infra.
7. **Проверить транзакционные границы**: write-use-cases под `Transactional`.
8. **Проверить сериализацию** (если нужна): DTO маппинг вне домена.
9. **Документация**: краткая README по запуску, миграциям Doctrine, провайдерам и маршрутам.

---

## 11) PSR-12 напоминания

- `declare(strict_types=1);` в каждом файле.
- Один класс на файл; именование классов — `StudlyCase`, методов/свойств — `camelCase`.
- Импорты отсортированы; длинные аргументы и списки переносятся на новые строки.
- Фигурные скобки по PSR-12, пробелы вокруг операторов, trailing comma в многострочных массивах.

---

## 12) Пример routes (Laravel) — только как транспорт

```php
<?php
use App\Modules\Product\Infrastructure\Http\Controller\ProductController;use Illuminate\Support\Facades\Route;

Route::post('/products', [ProductController::class, 'store']);
Route::get('/products/{id}', function (string $id, \App\Application\Catalog\Handler\GetProductHandler $handler) {
    $result = $handler(new \App\Application\Catalog\Query\GetProductQuery($id));
    return $result ? response()->json($result) : response()->noContent(404);
});
```

---

## 13) Частые ошибки и как их избежать

- **Утечки фреймворка в домен**: запрещены `Illuminate\*` или фасады внутри Domain/Application.
- **Активный рекорд** в домене: хранение логики в Eloquent-моделях — не допускается. В домене — только чистые сущности и логика.
- **Смешение слоёв**: контроллеры не должны знать о Doctrine и репозиториях напрямую.
- **Отсутствие транзакций** для нескольких write-операций: оборачивайте use-case в `Transactional`.
- **Сильная связность DTO ↔ Entity**: DTO — только для входа/выхода Application; маппинг — в хендлере/сервисе.

---

## 14) Быстрый старт (шаги)

1. `composer require doctrine/orm doctrine/dbal` (+ при желании `laravel-doctrine/orm`).
2. Создать `InfrastructureServiceProvider` и зарегистрировать.
3. Настроить `DoctrineFactory` и `EntityManagerInterface` биндинг.
4. Создать `Product` (Entity), `Sku` (VO), `ProductRepository` (интерфейс).
5. Реализовать `DoctrineProductRepository`.
6. Написать `CreateProductCommand`, `CreateProductHandler`, `GetProductQuery`, `GetProductHandler`.
7. Пробросить роуты и тонкий контроллер.
8. Запустить PHPCS/PHPStan, дописать тесты.
9. Проверить end-to-end через HTTP.


---

### Финальная проверка перед сдачей

- [ ] Domain/Application **не импортируют** классы из Laravel или Doctrine.
- [ ] Все публичные API use-case имеют чёткие DTO/команды/запросы.
- [ ] Doctrine отображение корректно (атрибуты/embeddables, имена колонок).
- [ ] Провайдеры Laravel регистрируют все биндинги (репозитории, EM, порты).
- [ ] Unit/Integration тесты проходят; статический анализ — без ошибок.
- [ ] PSR-12 соблюдён по всему проекту.

— Конец инструкции —
