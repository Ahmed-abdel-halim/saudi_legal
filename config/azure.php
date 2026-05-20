<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Azure AI Search
    |--------------------------------------------------------------------------
    | إعدادات Azure Cognitive Search للبحث الذكي في البيانات القانونية
    */
    'search' => [
        'endpoint' => env('AZURE_SEARCH_ENDPOINT', ''),
        'key'      => env('AZURE_SEARCH_KEY', ''),
        'index'    => env('AZURE_SEARCH_INDEX', 'legal-documents'),
        // Feature flag: true = Azure Search, false = Keyword Search القديم
        'enabled'  => env('AZURE_SEARCH_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Azure Blob Storage
    |--------------------------------------------------------------------------
    | تخزين الملفات القانونية والـ JSONL والتقارير
    */
    'storage' => [
        'name'      => env('AZURE_STORAGE_NAME', ''),
        'key'       => env('AZURE_STORAGE_KEY', ''),
        'url'       => env('AZURE_STORAGE_URL', ''),
        'containers' => [
            'legal_data'    => env('AZURE_CONTAINER_LEGAL', 'legal-data'),
            'user_uploads'  => env('AZURE_CONTAINER_UPLOADS', 'user-uploads'),
            'exports'       => env('AZURE_CONTAINER_EXPORTS', 'exports'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Azure MySQL
    |--------------------------------------------------------------------------
    | بيانات قاعدة البيانات على Azure (تُستخدم في الـ production)
    */
    'mysql' => [
        'host'     => env('AZURE_DB_HOST', ''),
        'port'     => env('AZURE_DB_PORT', 3306),
        'database' => env('AZURE_DB_DATABASE', 'radif'),
        'username' => env('AZURE_DB_USERNAME', ''),
        'password' => env('AZURE_DB_PASSWORD', ''),
        'ssl_ca'   => env('AZURE_DB_SSL_CA', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Azure App Service
    |--------------------------------------------------------------------------
    | إعدادات النشر
    */
    'app' => [
        'name'           => env('AZURE_APP_NAME', 'radiif-app'),
        'resource_group' => env('AZURE_RESOURCE_GROUP', 'radiif-rg'),
        'region'         => env('AZURE_REGION', 'eastus2'),
    ],

];
