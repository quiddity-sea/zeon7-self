<?php
/**
 * Configuration loader - loads .env file into $_ENV
 */

$envFile = __DIR__ . '/../../.env';

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        // Skip comments
        if (strpos($line, '#') === 0) {
            continue;
        }
        
        // Parse key=value
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Set in $_ENV
            $_ENV[$key] = $value;
            
            // Also set in $_SERVER for compatibility
            $_SERVER[$key] = $value;
        }
    }
}
