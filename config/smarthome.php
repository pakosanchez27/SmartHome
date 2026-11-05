<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Identidad del producto
    |--------------------------------------------------------------------------
    |
    | Valores por defecto del hogar. Cada hogar puede sobrescribirlos en la
    | base de datos (EP-03); aquí viven las convenciones de la aplicación.
    |
    */

    'name' => env('APP_NAME', 'SmartHome'),

    'locale' => env('APP_LOCALE', 'es'),

    'timezone' => env('APP_TIMEZONE', 'America/Mexico_City'),

    'currency' => [
        'code' => 'MXN',
        'symbol' => '$',
        'locale' => 'es_MX',
        // DECIMAL(14,2): 12 enteros + 2 decimales.
        'storage_precision' => 14,
        'storage_scale' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cantidades e inventario
    |--------------------------------------------------------------------------
    |
    | Toda cantidad se guarda en unidad base (g, ml, pieza). Las equivalencias
    | se declaran por producto (ADR-0006).
    |
    */

    'quantity' => [
        // DECIMAL(12,3)
        'storage_precision' => 12,
        'storage_scale' => 3,
        'base_units' => ['g', 'ml', 'piece'],
        'allow_negative_stock' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Archivos y comprobantes
    |--------------------------------------------------------------------------
    |
    | El disco "local" apunta a storage/app/private: no es accesible por URL
    | pública. Los archivos se sirven solo con autorización (EP-03/EP-04).
    |
    */

    'uploads' => [
        'disk' => 'local',
        'max_kilobytes' => 5120,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Presupuesto
    |--------------------------------------------------------------------------
    |
    | Los cortes de quincena son configurables por hogar (ADR-0001); este
    | valor solo alimenta el alta inicial del hogar.
    |
    */

    'budget' => [
        'default_period' => 'quincenal',
        'default_cut_days' => [15, 0], // 0 = último día del mes.
        'states' => ['borrador', 'planeado', 'activo', 'cerrado'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Inteligencia artificial
    |--------------------------------------------------------------------------
    |
    | Solo backend. La IA nunca escribe datos: propone y Laravel ejecuta tras
    | confirmación (RG-09). Ver ADR-0009.
    |
    */

    'ai' => [
        'enabled' => (bool) env('OPENROUTER_API_KEY'),
        'retention_days' => 30,
        'max_requests_per_day' => 100,
    ],

];
