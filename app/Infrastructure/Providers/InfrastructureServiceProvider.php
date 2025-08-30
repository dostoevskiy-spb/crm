<?php

declare(strict_types=1);

namespace App\Infrastructure\Providers;

use App\Domain\Individual\Contracts\IndividualRepositoryInterface;
use App\Domain\LegalEntity\Contracts\LegalEntityRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\DoctrineFactory;
use App\Infrastructure\Persistence\Doctrine\Repository\DoctrineIndividualRepository;
use App\Infrastructure\Persistence\Doctrine\Repository\DoctrineLegalEntityRepository;
use App\Infrastructure\Persistence\Doctrine\Transactional;
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
                    'pdo'    => $laravelConn->getPdo(),
                ];
                if ($isMemory) {
                    $dbParams['memory'] = true;
                }
            } else {
                // Force PostgreSQL in non-testing environments per project stack
                $pg = (array) config('database.connections.pgsql', []);
                $dbParams = [
                    'driver'   => 'pdo_pgsql',
                    'host'     => $pg['host'] ?? '127.0.0.1',
                    'port'     => $pg['port'] ?? 5432,
                    'dbname'   => $pg['database'] ?? null,
                    'user'     => $pg['username'] ?? null,
                    'password' => $pg['password'] ?? null,
                    'charset'  => $pg['charset'] ?? 'utf8',
                ];
            }

            $entityPaths = [
                base_path('app/Domain/Individual/Models'),
                base_path('app/Domain/Individual/ValueObjects'),
                base_path('app/Domain/LegalEntity/Models'),
                base_path('app/Domain/LegalEntity/ValueObjects'),
            ];

            $em = DoctrineFactory::create(
                $dbParams,
                $entityPaths,
                $app->environment('local')
            );

            // Ensure schema exists for sqlite during tests
            if ($isTesting) {
                $metadata = $em->getMetadataFactory()->getAllMetadata();
                if (!empty($metadata)) {
                    $tool = new SchemaTool($em);
                    try {
                        $tool->updateSchema($metadata, true);
                    } catch (\Throwable $e) {
                        // ignore; Laravel migrations may have already provisioned schema
                    }
                }
            }

            return $em;
        });

        $this->app->bind(LegalEntityRepositoryInterface::class, DoctrineLegalEntityRepository::class);
        $this->app->bind(IndividualRepositoryInterface::class, DoctrineIndividualRepository::class);
        $this->app->bind(Transactional::class);
    }
}
