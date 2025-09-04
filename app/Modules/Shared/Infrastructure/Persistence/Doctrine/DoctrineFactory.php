<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure\Persistence\Doctrine;

use App\Modules\Individual\Infrastructure\Persistence\Doctrine\Types\Id;
use App\Modules\Individual\Infrastructure\Persistence\Doctrine\Types\IndividualStatusType;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;

final class DoctrineFactory
{
    public static function create(array $dbParams, array $entityPaths, bool $isDevMode = false): EntityManagerInterface
    {
        // $config = ORMSetup::createAttributeMetadataConfiguration(
        // Use XML metadata configuration
        $config = ORMSetup::createXMLMetadataConfig(
            paths: $entityPaths,
            isDevMode: $isDevMode,
        );
        // Mirror proxy settings like createConfiguration() to keep runtime behavior
        $config->setProxyDir(sys_get_temp_dir());
        $config->setProxyNamespace('DoctrineProxies');
        $config->setAutoGenerateProxyClasses($isDevMode);

        // Register custom types
        if (! Type::hasType(IndividualStatusType::NAME)) {
            Type::addType(IndividualStatusType::NAME, IndividualStatusType::class);
        }

        if (! Type::hasType(Id::NAME)) {
            Type::addType(Id::NAME, Id::class);
        }

        $connection = DriverManager::getConnection($dbParams, $config);

        // Platform type mapping hint (for schema diff / introspection)
        try {
            $platform = $connection->getDatabasePlatform();
            if (method_exists($platform, 'registerDoctrineTypeMapping')) {
                $platform->registerDoctrineTypeMapping(IndividualStatusType::NAME, 'string');
            }
        } catch (\Throwable $e) {
            // ignore mapping failures
        }

        return new EntityManager($connection, $config);
    }
}
