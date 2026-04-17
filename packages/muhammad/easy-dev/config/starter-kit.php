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
    ],

    /**
     * Default paths for generated components.
     */
    'paths' => [
        'controller' => app_path('Http/Controllers/Api/V1'),
        'service'    => app_path('Services'),
        'repository' => app_path('Repositories'),
        'dto'        => app_path('DTOs'),
    ],

    /**
     * Stubs path.
     */
    'stubs_path' => base_path('stubs/starter-kit'),
];
