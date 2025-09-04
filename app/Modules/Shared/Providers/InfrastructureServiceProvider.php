<?php

declare(strict_types=1);

namespace App\Modules\Shared\Providers;

use App\Modules\Equipment\Domain\Contracts\EquipmentRepositoryInterface;
use App\Modules\Equipment\Infrastructure\Persistence\Doctrine\Repository\DoctrineEquipmentRepository;
use App\Modules\Individual\Domain\Contracts\IndividualRepositoryInterface;
use App\Modules\Individual\Infrastructure\Persistence\Doctrine\Repository\DoctrineIndividualRepository;
use App\Modules\LegalEntity\Domain\Contracts\LegalEntityRepositoryInterface;
use App\Modules\LegalEntity\Infrastructure\Persistence\Doctrine\Repository\DoctrineLegalEntityRepository;
use App\Modules\Product\Domain\Contracts\ProductRepositoryInterface;
use App\Modules\Product\Infrastructure\Persistence\Doctrine\Repository\DoctrineProductRepository;
use App\Modules\Shared\Infrastructure\Persistence\Doctrine\DoctrineFactory;
use App\Modules\Shared\Infrastructure\Persistence\Doctrine\Transactional;
use App\Modules\User\Domain\Contracts\UserRepositoryInterface;
use App\Modules\User\Infrastructure\Persistence\Doctrine\Repository\DoctrineUserRepository;
use Doctrine\DBAL\Schema\AbstractAsset;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Illuminate\Support\ServiceProvider;

final class InfrastructureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EntityManagerInterface::class, function ($app) {
            $isTesting = $app->environment('testing') || method_exists($app, 'runningUnitTests') && $app->runningUnitTests();

            if ($isTesting) {
                // Testing uses sqlite and MUST share PDO with Laravel's default connection
                /** @var \Illuminate\Database\Connection $laravelConn */
                $laravelConn = $app['db']->connection(); // default is sqlite in tests
                $isMemory = ($laravelConn->getConfig('database') === ':memory:');
                $dbParams = [
                    'driver' => 'pdo_sqlite',
                    'pdo' => $laravelConn->getPdo(),
                ];
                if ($isMemory) {
                    $dbParams['memory'] = true;
                }
            } else {
                // Force PostgreSQL in non-testing environments per project stack
                $pg = (array) config('database.connections.pgsql', []);
                $dbParams = [
                    'driver' => 'pdo_pgsql',
                    'host' => $pg['host'] ?? '127.0.0.1',
                    'port' => $pg['port'] ?? 5432,
                    'dbname' => $pg['database'] ?? null,
                    'user' => $pg['username'] ?? null,
                    'password' => $pg['password'] ?? null,
                    'charset' => $pg['charset'] ?? 'utf8',
                ];
            }

            // XML mapping files are placed under Infrastructure mapping directory
            $mappingPaths = (array) config('doctrine.mapping_paths', []);
            $entityPaths = array_map(static fn ($path) => base_path($path), $mappingPaths);

            $em = DoctrineFactory::create(
                $dbParams,
                $entityPaths,
                $app->environment('local')
            );

            $config = $em->getConnection()->getConfiguration();
            $tablesWhiteList = [
                'legal_entity',
                'individual',
                'equipment',
                'product',
            ];

            $allow = '/^(?:' . implode('|', $tablesWhiteList) . ')$/i';

            $config->setSchemaAssetsFilter(
                static function (string|AbstractAsset $asset) use ($allow): bool {
                    $name = $asset instanceof AbstractAsset ? $asset->getName() : $asset;
                    return (bool) preg_match($allow, $name);
                }
            );

            // Ensure schema exists for sqlite during tests
            if ($isTesting) {
                $metadata = $em->getMetadataFactory()->getAllMetadata();
                if (! empty($metadata)) {
                    $tool = new SchemaTool($em);
                    try {
                        $tool->updateSchema($metadata);
                    } catch (\Throwable $e) {
                        // ignore; Laravel migrations may have already provisioned schema
                    }
                }
            }

            return $em;
        });

        $this->app->bind(IndividualRepositoryInterface::class, DoctrineIndividualRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, DoctrineProductRepository::class);
        $this->app->bind(EquipmentRepositoryInterface::class, DoctrineEquipmentRepository::class);
        $this->app->bind(LegalEntityRepositoryInterface::class, DoctrineLegalEntityRepository::class);
        $this->app->bind(UserRepositoryInterface::class, DoctrineUserRepository::class);
        $this->app->bind(Transactional::class);
    }
}
