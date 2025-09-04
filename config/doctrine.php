<?php

return [
    // Relative paths from base_path(); modules can append theirs at runtime
    'mapping_paths' => [
        'app/Modules/LegalEntity/Infrastructure/Persistence/Doctrine/Mapping',
        'app/Modules/Individual/Infrastructure/Persistence/Doctrine/Mapping',
        'app/Modules/Product/Infrastructure/Persistence/Doctrine/Mapping',
        'app/Modules/Equipment/Infrastructure/Persistence/Doctrine/Mapping',
        'app/Modules/User/Infrastructure/Persistence/Doctrine/Mapping',
    ],
    'extensions' => [
        //        LaravelDoctrine\Extensions\Loggable\LoggableExtension::class
    ],
];
