# Legal Entity Bounded Context

Реализация bounded context "Юридические лица" для CRM системы согласно DDD принципам.

## Архитектура

### Domain Layer (чистый PHP)
- **LegalEntity** - основная сущность юридического лица
- **Value Objects**: `LegalEntityUid`, `CompanyName`, `TaxNumber`
- **Repository Interface**: `LegalEntityRepositoryInterface`

### Application Layer (use-cases)
- **Commands**: `CreateLegalEntityCommand`
- **Queries**: `GetLegalEntityQuery`, `GetLegalEntitiesQuery`
- **Handlers**: `CreateLegalEntityHandler`, `GetLegalEntityHandler`, `GetLegalEntitiesHandler`
- **DTOs**: `CreateLegalEntityDTO`

### Infrastructure Layer (интеграция с Laravel)
- **Doctrine Repository**: `DoctrineLegalEntityRepository`
- **Service Provider**: `InfrastructureServiceProvider`
- **Controller**: `LegalEntityController`
- **Migration**: `2025_08_28_225900_create_legal_entity_table`

## API Endpoints

### POST /api/legal-entities
Создание нового юридического лица

```json
{
  "shortName": "ООО \"Тест\"",
  "fullName": "Общество с ограниченной ответственностью \"Тест\"",
  "ogrn": "1234567890123",
  "inn": "1234567890",
  "kpp": "123456789",
  "legalAddress": "123456, г. Москва, ул. Тестовая, д. 1",
  "phoneNumber": "+7 495 123-45-67",
  "email": "test@example.com",
  "creatorUid": "550e8400-e29b-41d4-a716-446655440000"
}
```

### GET /api/legal-entities/{uid}
Получение юридического лица по UID

### GET /api/legal-entities
Получение списка юридических лиц с фильтрацией

Параметры фильтрации:
- `shortName` - поиск по сокращенному наименованию
- `inn` - поиск по ИНН
- `phoneNumber` - поиск по телефону
- `email` - поиск по email
- `curatorUid` - фильтр по куратору

## Валидация

- **Сокращенное наименование**: 1-20 символов
- **Полное наименование**: 1-255 символов
- **ОГРН**: ровно 13 цифр
- **ИНН**: ровно 10 цифр (уникальный)
- **КПП**: ровно 9 цифр

## Команды для разработки

```bash
# Установка зависимостей
composer install

# Запуск миграций
php artisan migrate

# Проверка качества кода
composer qa

# Запуск тестов
composer test
```

## Тестирование

Созданы unit-тесты для:
- Domain сущностей и Value Objects
- Application handlers
- Валидации и бизнес-логики

Все тесты следуют принципам чистой архитектуры с использованием mock-объектов для изоляции слоев.
