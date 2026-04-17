<?php

return [
    /**
     * Default namespaces for generated components.
     */
    'namespaces' => [
        'controller' => 'App\\Http\\Controllers\\Api\\V1',
        'service'    => 'App\\Services',
        'repository' => 'App\\Repositories',
        'dto'        => 'App\\DTOs',
        'request'    => 'App\\Http\\Requests\\Api\\V1',
        'resource'   => 'App\\Http\\Resources\\Api\\V1',
        'policy'     => 'App\\Policies',
        'test'       => 'Tests\\Feature',
    ],

    /**
     * Default paths for generated components.
     */
    'paths' => [
        'controller' => app_path('Http/Controllers/Api/V1'),
        'service'    => app_path('Services'),
        'repository' => app_path('Repositories'),
        'dto'        => app_path('DTOs'),
        'request'    => app_path('Http/Requests/Api/V1'),
        'resource'   => app_path('Http/Resources/Api/V1'),
        'policy'     => app_path('Policies'),
        'test'       => base_path('tests/Feature'),
    ],

    /**
     * Path to published stubs (after running vendor:publish).
     * The command checks here first before falling back to package stubs.
     */
    'stubs_path' => base_path('stubs/starter-kit'),
];
