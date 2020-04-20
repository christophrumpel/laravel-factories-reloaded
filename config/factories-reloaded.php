<?php

return [
    /*
     * Defines where your models are located.
     * Will be used to find your models while generating new factories.
     */
    'models_paths' => [
        base_path('app'),
    ],

    /**
     * Defines where your new factories should be stored.
     */
    'factories_path' => base_path('tests/Factories'),

    /**
     * Defines the namespace ofr your new factories.
     */
    'factories_namespace' => 'Tests\Factories',

    /**
     * Defines where your Laravel factories are located.
     * They are used while generating new factories.
     */
    'vanilla_factories_path' => database_path('factories'),
];
