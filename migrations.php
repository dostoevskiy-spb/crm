<?php

declare(strict_types=1);

// Doctrine Migrations PhpFile configuration
// This file MUST return an array or a Configuration instance.
// The DependencyFactory is created in cli-config.php where Laravel is bootstrapped.

return [
    'table_storage' => [
        'table_name' => 'doctrine_migration_versions',
        'version_column_name' => 'version',
        'version_column_length' => 191,
        'executed_at_column_name' => 'executed_at',
        'execution_time_column_name' => 'execution_time',
    ],

    'migrations_paths' => [
        'Database\\DoctrineMigrations' => 'database/doctrine-migrations',
    ],

    'all_or_nothing' => true,
    'transactional' => true,
    'check_database_platform' => true,
];
