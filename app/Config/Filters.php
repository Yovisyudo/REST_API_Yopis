<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;


class Filters extends BaseConfig
{
    
    public array $aliases = [
    'csrf'          => CSRF::class,
    'toolbar'       => DebugToolbar::class,
    'honeypot'      => Honeypot::class,
    'invalidchars'  => InvalidChars::class,
    'secureheaders' => SecureHeaders::class,
    
    // Custom Filters
    'cors'          => \App\Filters\CorsFilter::class,
    'auth'          => \App\Filters\AuthFilter::class,
    
    // PERBAIKAN DI SINI:
    // Arahkan 'firebase_auth' ke AuthFilter, bukan FirebaseAuth
    'firebase_auth' => \App\Filters\AuthFilter::class, 
];
    public array $globals = [
        'before' => [
            // 'honeypot',
            // 'csrf',
            // 'invalidchars',
        ],
        'after' => [
            'toolbar',
            // 'honeypot',
            // 'secureheaders',
        ],
    ];

    public array $methods = [];

    public array $filters = [];

    
}
