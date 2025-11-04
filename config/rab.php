<?php

return [

    /*
    |--------------------------------------------------------------------------
    | RAB System Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your RAB system. This value is used when the
    | framework needs to display the system name.
    |
    */

    'name' => env('RAB_SYSTEM_NAME', 'RAB Automation System'),

    /*
    |--------------------------------------------------------------------------
    | RAB Version
    |--------------------------------------------------------------------------
    |
    | Current version of RAB system
    |
    */

    'version' => '2.0.0',

    /*
    |--------------------------------------------------------------------------
    | Default Financial Settings
    |--------------------------------------------------------------------------
    |
    | These are the default financial settings for new projects.
    | Users can override these values when creating projects.
    |
    */

    'defaults' => [
        'overhead_percentage' => 10.00,
        'profit_percentage' => 10.00,
        'ppn_percentage' => 11.00,
    ],

    /*
    |--------------------------------------------------------------------------
    | Project Status Options
    |--------------------------------------------------------------------------
    |
    | Available status options for projects
    |
    */

    'project_statuses' => [
        'draft' => 'Draft',
        'active' => 'Aktif',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ],

    /*
    |--------------------------------------------------------------------------
    | Item Types
    |--------------------------------------------------------------------------
    |
    | Available item types for materials, labor, and equipment
    |
    */

    'item_types' => [
        'material' => 'Material',
        'labor' => 'Upah',
        'equipment' => 'Alat',
    ],

    /*
    |--------------------------------------------------------------------------
    | Item Categories (for AHSP composition)
    |--------------------------------------------------------------------------
    |
    | Categories used in AHSP composition items
    |
    */

    'item_categories' => [
        'material' => 'Bahan',
        'labor' => 'Upah',
        'equipment' => 'Alat',
    ],

    /*
    |--------------------------------------------------------------------------
    | BOQ Item Types
    |--------------------------------------------------------------------------
    |
    | Types of items that can be added to BOQ
    |
    */

    'boq_item_types' => [
        'ahsp' => 'AHSP',
        'custom' => 'Custom',
    ],

    /*
    |--------------------------------------------------------------------------
    | Region Types
    |--------------------------------------------------------------------------
    |
    | Types of regions (city or regency)
    |
    */

    'region_types' => [
        'city' => 'Kota',
        'regency' => 'Kabupaten',
    ],

    /*
    |--------------------------------------------------------------------------
    | AHSP Source Types
    |--------------------------------------------------------------------------
    |
    | Types of AHSP sources (master or custom)
    |
    */

    'ahsp_source_types' => [
        'master' => 'Master AHSP',
        'custom' => 'Custom AHSP',
    ],

    /*
    |--------------------------------------------------------------------------
    | Item Price Source Types
    |--------------------------------------------------------------------------
    |
    | Source types for item prices
    |
    */

    'price_source_types' => [
        'system' => 'Sistem',
        'manual' => 'Manual',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Settings
    |--------------------------------------------------------------------------
    |
    | Default pagination settings for RAB lists
    |
    */

    'pagination' => [
        'default_per_page' => 15,
        'max_per_page' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Calculation Precision
    |--------------------------------------------------------------------------
    |
    | Decimal precision for calculations and currency
    |
    */

    'precision' => [
        'coefficient' => 4,    // Max 4 decimal places for coefficients
        'volume' => 4,         // Max 4 decimal places for volumes
        'price' => 2,          // Max 2 decimal places for prices
        'percentage' => 2,     // Max 2 decimal places for percentages
    ],

    /*
    |--------------------------------------------------------------------------
    | Currency Settings
    |--------------------------------------------------------------------------
    |
    | Currency formatting settings
    |
    */

    'currency' => [
        'code' => 'IDR',
        'symbol' => 'Rp',
        'decimals' => 0,
        'decimal_separator' => ',',
        'thousands_separator' => '.',
        'format' => 'Rp %s',  // %s will be replaced with formatted number
    ],

    /*
    |--------------------------------------------------------------------------
    | AHSP Code Pattern
    |--------------------------------------------------------------------------
    |
    | Regular expression pattern for AHSP codes
    |
    */

    'ahsp_code_pattern' => '/^[0-9.]+$/',  // e.g., 2.2.1.5.7

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    |
    | Settings for file uploads (documents, attachments, etc.)
    |
    */

    'uploads' => [
        'max_size' => 10240,  // Max file size in KB (10 MB)
        'allowed_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],
        'path' => 'rab/uploads',
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Settings
    |--------------------------------------------------------------------------
    |
    | Settings for exporting RAB data (PDF, Excel, etc.)
    |
    */

    'export' => [
        'pdf' => [
            'engine' => 'dompdf',  // Options: dompdf, wkhtmltopdf, snappy
            'paper_size' => 'A4',
            'orientation' => 'portrait',
        ],
        'excel' => [
            'format' => 'xlsx',
            'include_charts' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Settings
    |--------------------------------------------------------------------------
    |
    | Cache settings for RAB calculations and data
    |
    */

    'cache' => [
        'enabled' => env('RAB_CACHE_ENABLED', true),
        'ttl' => env('RAB_CACHE_TTL', 3600),  // Cache TTL in seconds (1 hour)
        'prefix' => 'rab:',
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Maximum and minimum values for validation
    |
    */

    'validation' => [
        'max_coefficient' => 999999.9999,
        'max_volume' => 999999.9999,
        'max_percentage' => 100,
        'min_percentage' => 0,
        'max_project_name_length' => 255,
        'max_description_length' => 5000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Template Settings
    |--------------------------------------------------------------------------
    |
    | Settings for project templates
    |
    */

    'templates' => [
        'allow_user_templates' => true,
        'max_user_templates' => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Trail Settings
    |--------------------------------------------------------------------------
    |
    | Settings for logging and audit trail
    |
    */

    'audit' => [
        'enabled' => env('RAB_AUDIT_ENABLED', true),
        'log_changes' => true,
        'log_calculations' => false,  // Set true to log all calculation operations
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-tenant Settings
    |--------------------------------------------------------------------------
    |
    | Settings for multi-tenant support
    |
    */

    'multi_tenant' => [
        'enabled' => true,
        'strict_mode' => true,  // Users can only see their own data
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Enable or disable specific features
    |
    */

    'features' => [
        'custom_ahsp' => true,           // Allow users to create custom AHSP
        'template_sharing' => true,      // Allow sharing templates between users
        'project_comparison' => true,    // Enable project comparison feature
        'advanced_reporting' => true,    // Enable advanced reports
        'export_to_pdf' => true,        // Enable PDF export
        'export_to_excel' => true,      // Enable Excel export
        'price_history' => true,        // Track price history
        'notifications' => true,        // Enable system notifications
    ],

    /*
    |--------------------------------------------------------------------------
    | Date Format Settings
    |--------------------------------------------------------------------------
    |
    | Date format for display and input
    |
    */

    'date_format' => [
        'display' => 'd/m/Y',           // Display format: 04/11/2025
        'input' => 'Y-m-d',             // Input format: 2025-11-04
        'datetime' => 'd/m/Y H:i:s',    // DateTime format
    ],

    /*
    |--------------------------------------------------------------------------
    | System Messages
    |--------------------------------------------------------------------------
    |
    | System-wide messages and notifications
    |
    */

    'messages' => [
        'maintenance_mode' => 'Sistem sedang dalam pemeliharaan. Silakan coba beberapa saat lagi.',
        'data_updated' => 'Data berhasil diperbarui.',
        'calculation_success' => 'Kalkulasi berhasil dilakukan.',
        'export_success' => 'Data berhasil diekspor.',
    ],

    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    |
    | Settings for API endpoints
    |
    */

    'api' => [
        'rate_limit' => 60,  // Requests per minute
        'timeout' => 30,     // Request timeout in seconds
        'version' => 'v1',
        'prefix' => 'api',
    ],

];
