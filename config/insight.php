<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Insight Lab Admin User IDs
    |--------------------------------------------------------------------------
    |
    | User IDs yang memiliki akses admin di Insight Lab.
    | Admin bisa: CRUD categories, delete any insight/comment, dll.
    |
    */
    'admin_user_ids' => [
        1, // Default: User dengan ID 1
        // Tambahkan user ID lain yang perlu akses admin
    ],

    /*
    |--------------------------------------------------------------------------
    | Point System Configuration
    |--------------------------------------------------------------------------
    */
    'points' => [
        'insight_create' => 1,
        'comment_create' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Upload Limits
    |--------------------------------------------------------------------------
    */
    'media_limits' => [
        'video' => [
            'max_count' => 1,
            'max_size' => 102400, // 100MB in KB
            'allowed_mimes' => ['mp4'],
        ],
        'image' => [
            'max_count' => 10,
            'max_size' => 10240, // 10MB in KB
            'allowed_mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        ],
        'file' => [
            'max_count' => 5,
            'max_size' => 10240, // 10MB in KB
            'allowed_mimes' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'dwg', 'dxf', 'zip', 'rar'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | S3 Storage Paths
    |--------------------------------------------------------------------------
    */
    'storage_paths' => [
        'video' => 'insights/videos',
        'image' => 'insights/images',
        'file' => 'insights/files',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limits' => [
        'create_insight' => 5, // per hour
        'create_comment' => 20, // per hour
        'upload_media' => 10, // per hour
    ],
];