<?php
/**
 * Environment Configuration Template
 * 
 * IMPORTANT: 
 * 1. Copy this file to "env.php" 
 * 2. Update values for your environment
 * 3. Add "env.php" to .gitignore to prevent credentials from being committed
 * 
 * Usage: Include this file in config.php before creating database connections
 */

// ============================================================================
// ENVIRONMENT DETECTION
// ============================================================================
// You can also use: $_ENV, getenv(), or check server headers

$environment = 'development'; // Change to 'production' for live server
// $environment = getenv('APP_ENV') ?? 'development';

// ============================================================================
// DATABASE CONFIGURATION
// ============================================================================

$db_config = [
    'development' => [
        'host'     => 'localhost',
        'user'     => 'root',
        'password' => '',
        'database' => 'omanbapa_store',
        'port'     => 3306,
        'charset'  => 'utf8mb4'
    ],
    'testing' => [
        'host'     => 'test-db.example.com',
        'user'     => 'test_user',
        'password' => 'test_password',
        'database' => 'omanbapa_store_test',
        'port'     => 3306,
        'charset'  => 'utf8mb4'
    ],
    'production' => [
        'host'     => 'prod-db.example.com',
        'user'     => 'prod_user',
        'password' => 'CHANGE_THIS_SECURE_PASSWORD',
        'database' => 'omanbapa_store_prod',
        'port'     => 3306,
        'charset'  => 'utf8mb4'
    ]
];

$db = $db_config[$environment];

// ============================================================================
// SESSION CONFIGURATION
// ============================================================================

$session_config = [
    'development' => [
        'lifetime'     => 3600,           // 1 hour
        'secure'       => false,
        'httponly'     => true,
        'samesite'     => 'Lax'
    ],
    'production' => [
        'lifetime'     => 1800,           // 30 minutes
        'secure'       => true,           // HTTPS only
        'httponly'     => true,
        'samesite'     => 'Strict'
    ]
];

$session = $session_config[$environment] ?? $session_config['production'];

// ============================================================================
// ERROR HANDLING CONFIGURATION
// ============================================================================

$error_config = [
    'development' => [
        'display_errors'   => 1,
        'error_reporting'  => E_ALL,
        'log_errors'       => 1,
        'log_file'         => __DIR__ . '/logs/php-errors.log'
    ],
    'production' => [
        'display_errors'   => 0,          // Never show errors to users
        'error_reporting'  => E_ALL,      // Still log everything
        'log_errors'       => 1,
        'log_file'         => '/var/log/php-errors.log'
    ]
];

$error = $error_config[$environment];

// ============================================================================
// DEBUG MODE
// ============================================================================

$debug = [
    'development' => true,
    'testing'     => false,
    'production'  => false
];

define('DEBUG_MODE', $debug[$environment] ?? false);

// ============================================================================
// SECURITY CONFIGURATION
// ============================================================================

$security = [
    'password_algorithm'    => PASSWORD_BCRYPT,  // For password hashing
    'password_cost'         => 12,               // Bcrypt cost factor
    'csrf_token_lifetime'   => 3600,             // 1 hour
    'login_attempts_max'    => 5,
    'login_lockout_duration'=> 900,              // 15 minutes in seconds
];

// ============================================================================
// APPLICATION CONFIGURATION
// ============================================================================

$app_config = [
    'name'              => 'OBOADE NYAME HARDWARES Store',
    'version'           => '1.0.0',
    'timezone'          => 'Africa/Accra',
    'currency'          => '₵',
    'low_stock_threshold' => 10,
];

// ============================================================================
// EMAIL CONFIGURATION (for future notifications)
// ============================================================================

$email_config = [
    'development' => [
        'driver'    => 'log',           // Use log instead of actually sending
        'from'      => 'noreply@localhost',
        'host'      => 'localhost',
        'port'      => 1025
    ],
    'production' => [
        'driver'    => 'smtp',
        'from'      => 'noreply@omanbapa.com',
        'host'      => 'smtp.host.com',
        'port'      => 587,
        'username'  => 'smtp_user',
        'password'  => 'smtp_password',
        'encryption'=> 'tls'
    ]
];

$email = $email_config[$environment] ?? $email_config['production'];

// ============================================================================
// API CONFIGURATION (for future mobile app integration)
// ============================================================================

$api_config = [
    'enabled'           => true,
    'version'           => '1.0',
    'rate_limit'        => 100,        // requests per hour
    'token_lifetime'    => 86400,      // 24 hours in seconds
];

// ============================================================================
// CACHING CONFIGURATION
// ============================================================================

$cache_config = [
    'driver'            => 'file',     // file, redis, memcached
    'ttl'               => 3600,       // default cache time-to-live
    'cache_dir'         => __DIR__ . '/cache/'
];

// ============================================================================
// LOGGING CONFIGURATION
// ============================================================================

$log_config = [
    'enable_file_logging'     => true,
    'log_directory'           => __DIR__ . '/logs/',
    'log_level'               => $environment === 'production' ? 'warning' : 'debug',
    'enable_database_logging' => false,
    'max_log_file_size'       => 10485760,  // 10MB
];

// ============================================================================
// BACKUP CONFIGURATION
// ============================================================================

$backup_config = [
    'enabled'             => true,
    'backup_directory'    => '/backups/',
    'schedule'            => '0 2 * * *',  // Daily at 2 AM (cron format)
    'retention_days'      => 30,
    'compress'            => true,
    'encrypt'             => $environment === 'production',
];

// ============================================================================
// EXPORT CONFIGURATION (for easy use in config.php)
// ============================================================================

return [
    'environment' => $environment,
    'db'          => $db,
    'session'     => $session,
    'error'       => $error,
    'security'    => $security,
    'app'         => $app_config,
    'email'       => $email,
    'api'         => $api_config,
    'cache'       => $cache_config,
    'log'         => $log_config,
    'backup'      => $backup_config,
];
?>
