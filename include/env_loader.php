<?php
function loadEnv($path) {
    if(!file_exists($path)) {
        return;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach($lines as $line) {
        // Skip comments
        if(strpos(trim($line), '#') === 0) continue;
        
        if(strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remove Process Env Overwrite Check for simplicity in this context, or keep strict?
            // Let's just set it.
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Load .env from project root
loadEnv(__DIR__ . '/../.env');
?>
