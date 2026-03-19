<?php
/**
 * Database Backup Script
 * 
 * Usage: 
 *   php backup_database.php          (creates backup in default location)
 *   php backup_database.php backup   (creates manual backup)
 *   php backup_database.php restore backup_filename.sql (restore from backup)
 * 
 * Setup Cron Job for automatic backups:
 *   Linux/Mac: 0 2 * * * cd /var/www/html && php backup_database.php
 *   Windows Task Scheduler: php.exe "C:\backup_database.php"
 */

// ============================================================================
// CONFIGURATION
// ============================================================================

require_once 'config.php';
require_once 'env.example.php';

$config = require 'env.example.php';

$backup_dir = __DIR__ . '/backups/';
$max_age_days = $config['backup']['retention_days'];
$compress = $config['backup']['compress'];

// Create backup directory if it doesn't exist
if (!is_dir($backup_dir)) {
    mkdir($backup_dir, 0755, true);
}

// ============================================================================
// FUNCTIONS
// ============================================================================

/**
 * Create a database backup
 */
function create_backup($conn, $database, $backup_dir, $compress = true) {
    $timestamp = date('Y-m-d_H-i-s');
    $backup_file = $backup_dir . 'backup_' . $database . '_' . $timestamp . '.sql';
    
    // Get all tables
    $result = $conn->query("SHOW TABLES");
    if (!$result) {
        echo "Error: " . $conn->error . "\n";
        return false;
    }
    
    $backup_content = "-- Database Backup: " . $database . "\n";
    $backup_content .= "-- Created: " . date('Y-m-d H:i:s') . "\n";
    $backup_content .= "-- PHP Version: " . phpversion() . "\n";
    $backup_content .= "-- MySQL Version: " . $conn->get_server_info() . "\n";
    $backup_content .= "\n\n";
    
    // Add DROP DATABASE statement
    $backup_content .= "DROP DATABASE IF EXISTS `" . $database . "`;\n";
    $backup_content .= "CREATE DATABASE `" . $database . "`;\n";
    $backup_content .= "USE `" . $database . "`;\n\n";
    
    // Get table structure and data
    while ($row = $result->fetch_row()) {
        $table = $row[0];
        
        // Get CREATE TABLE statement
        $create_result = $conn->query("SHOW CREATE TABLE `" . $table . "`");
        if ($create_result) {
            $create_row = $create_result->fetch_row();
            $backup_content .= "\n-- Table structure for table `" . $table . "`\n";
            $backup_content .= "DROP TABLE IF EXISTS `" . $table . "`;\n";
            $backup_content .= $create_row[1] . ";\n\n";
        }
        
        // Get table data
        $data_result = $conn->query("SELECT * FROM `" . $table . "`");
        if ($data_result && $data_result->num_rows > 0) {
            $backup_content .= "-- Dumping data for table `" . $table . "`\n";
            
            while ($data_row = $data_result->fetch_assoc()) {
                $columns = array_keys($data_row);
                $values = array_values($data_row);
                
                // Escape values
                $values = array_map(function($val) use ($conn) {
                    if ($val === null) {
                        return 'NULL';
                    }
                    return "'" . $conn->real_escape_string($val) . "'";
                }, $values);
                
                $backup_content .= "INSERT INTO `" . $table . "` (" . 
                    implode(", ", array_map(function($col) { return "`" . $col . "`"; }, $columns)) . 
                    ") VALUES (" . implode(", ", $values) . ");\n";
            }
            $backup_content .= "\n";
        }
    }
    
    // Write to file
    $file_handle = fopen($backup_file, 'w');
    if (!$file_handle) {
        echo "Error: Cannot write to backup directory. Check permissions.\n";
        return false;
    }
    
    fwrite($file_handle, $backup_content);
    fclose($file_handle);
    
    // Compress if enabled
    if ($compress && extension_loaded('zlib')) {
        $compressed_file = $backup_file . '.gz';
        if (exec("gzip -9 " . escapeshellarg($backup_file) . " -c > " . escapeshellarg($compressed_file))) {
            unlink($backup_file);
            echo "✓ Backup created successfully (compressed): " . basename($compressed_file) . "\n";
            return $compressed_file;
        }
    }
    
    echo "✓ Backup created successfully: " . basename($backup_file) . "\n";
    return $backup_file;
}

/**
 * List all backups
 */
function list_backups($backup_dir) {
    $files = scandir($backup_dir, SCANDIR_SORT_DESCENDING);
    $backups = [];
    
    foreach ($files as $file) {
        if (in_array($file, ['.', '..'])) continue;
        if (strpos($file, 'backup_') === 0) {
            $stat = stat($backup_dir . $file);
            $backups[] = [
                'name' => $file,
                'size' => format_bytes($stat['size']),
                'date' => date('Y-m-d H:i:s', $stat['mtime'])
            ];
        }
    }
    
    return $backups;
}

/**
 * Delete old backups
 */
function cleanup_old_backups($backup_dir, $max_age_days) {
    $count = 0;
    $files = scandir($backup_dir);
    $cutoff_time = time() - ($max_age_days * 86400);
    
    foreach ($files as $file) {
        if (in_array($file, ['.', '..'])) continue;
        if (strpos($file, 'backup_') !== 0) continue;
        
        $file_path = $backup_dir . $file;
        if (filemtime($file_path) < $cutoff_time) {
            unlink($file_path);
            $count++;
        }
    }
    
    if ($count > 0) {
        echo "✓ Deleted " . $count . " old backup(s)\n";
    }
    
    return $count;
}

/**
 * Restore from backup
 */
function restore_backup($conn, $backup_file, $backup_dir) {
    $full_path = $backup_dir . $backup_file;
    
    if (!file_exists($full_path)) {
        echo "Error: Backup file not found: " . $backup_file . "\n";
        return false;
    }
    
    // Check if file is compressed
    if (substr($full_path, -3) === '.gz' && extension_loaded('zlib')) {
        $tmp_file = $backup_dir . 'restore_tmp_' . time() . '.sql';
        exec("gunzip -c " . escapeshellarg($full_path) . " > " . escapeshellarg($tmp_file));
        $full_path = $tmp_file;
    }
    
    // Read SQL file
    $sql = file_get_contents($full_path);
    if (!$sql) {
        echo "Error: Cannot read backup file\n";
        return false;
    }
    
    // Split statements
    $statements = array_filter(array_map('trim', explode(";\n", $sql)));
    
    $executed = 0;
    $errors = 0;
    
    foreach ($statements as $statement) {
        if (empty($statement)) continue;
        
        if (!$conn->query($statement)) {
            echo "Error executing statement: " . $conn->error . "\n";
            $errors++;
        } else {
            $executed++;
        }
    }
    
    // Cleanup temporary file
    if (isset($tmp_file) && file_exists($tmp_file)) {
        unlink($tmp_file);
    }
    
    if ($errors === 0) {
        echo "✓ Restore completed successfully. Executed " . $executed . " statements.\n";
        return true;
    } else {
        echo "⚠ Restore completed with " . $errors . " error(s).\n";
        return false;
    }
}

/**
 * Format bytes to human readable
 */
function format_bytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// ============================================================================
// MAIN EXECUTION
// ============================================================================

// Prevent direct execution from web browser
if (php_sapi_name() !== 'cli') {
    die("This script can only be run from the command line.\n");
}

$command = isset($argv[1]) ? $argv[1] : 'backup';

echo "\n=== Omanbapa Database Backup Manager ===\n\n";

switch ($command) {
    case 'backup':
        echo "Creating database backup...\n";
        create_backup($conn, $config['db']['database'], $backup_dir, $compress);
        cleanup_old_backups($backup_dir, $max_age_days);
        echo "✓ Backup process completed\n\n";
        break;
    
    case 'list':
        echo "Available backups:\n\n";
        $backups = list_backups($backup_dir);
        if (empty($backups)) {
            echo "No backups found.\n";
        } else {
            printf("%-40s %-15s %s\n", "Filename", "Size", "Date Created");
            echo str_repeat("-", 90) . "\n";
            foreach ($backups as $backup) {
                printf("%-40s %-15s %s\n", $backup['name'], $backup['size'], $backup['date']);
            }
        }
        echo "\n";
        break;
    
    case 'restore':
        if (!isset($argv[2])) {
            echo "Usage: php backup_database.php restore <backup_filename>\n";
            echo "\nAvailable backups:\n";
            $backups = list_backups($backup_dir);
            foreach ($backups as $backup) {
                echo "  - " . $backup['name'] . " (" . $backup['size'] . ")\n";
            }
        } else {
            $backup_file = $argv[2];
            echo "Restoring from backup: " . $backup_file . "\n";
            restore_backup($conn, $backup_file, $backup_dir);
        }
        echo "\n";
        break;
    
    case 'cleanup':
        echo "Cleaning up old backups (older than " . $max_age_days . " days)...\n";
        cleanup_old_backups($backup_dir, $max_age_days);
        echo "✓ Cleanup completed\n\n";
        break;
    
    case 'help':
    default:
        echo "Usage: php backup_database.php [command]\n\n";
        echo "Commands:\n";
        echo "  backup   - Create a new database backup (default)\n";
        echo "  list     - List all available backups\n";
        echo "  restore  - Restore from a backup file\n";
        echo "  cleanup  - Delete old backups\n";
        echo "  help     - Show this help message\n\n";
        echo "Examples:\n";
        echo "  php backup_database.php\n";
        echo "  php backup_database.php list\n";
        echo "  php backup_database.php restore backup_omanbapa_store_2024-01-15_14-30-45.sql.gz\n\n";
}

$conn->close();
?>
