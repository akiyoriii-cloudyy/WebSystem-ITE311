<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;
use CodeIgniter\Filters\SecureHeaders;

class Filters extends BaseFilters
{
    // Aliases make filters easier to use in routes or globally
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        'roleauth'      => \App\Filters\RoleAuth::class, 
    ];

    // Required filters are always applied (before or after)
    public array $required = [
        'before' => [],
        'after'  => [],
    ];

    // Global filters (apply to every request)
    public array $globals = [
        'before' => [
            // enable CSRF protection on all POST requests
            'csrf',
        ],
        'after' => [
            // show debug toolbar only in development
            'toolbar',
        ],
    ];

    // Filters by HTTP method (optional)
    public array $methods = [];

    // Filters by URI pattern (optional)
    public array $filters = [];
}
