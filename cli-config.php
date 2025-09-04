<?php

declare(strict_types=1);

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\EntityManagerInterface;

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';

/** @var \Illuminate\Contracts\Console\Kernel $kernel */
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

/** @var EntityManagerInterface $em */
$em = $app->make(EntityManagerInterface::class);

$config = new PhpFile(__DIR__.'/migrations.php');

return DependencyFactory::fromEntityManager(
    $config,
    new ExistingEntityManager($em)
);
