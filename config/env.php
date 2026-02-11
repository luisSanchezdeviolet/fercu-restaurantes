<?php
/**
 * Cargador de variables de entorno desde archivo .env
 * 
 * Las variables se cargan en $_ENV y putenv() para que estén disponibles
 * en toda la aplicación. El archivo .env no debe subirse a Git.
 */

function loadEnv($path = null) {
    if ($path === null) {
        $path = dirname(__DIR__) . '/.env';
    }
    
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        $line = trim($line);
        // Ignorar comentarios
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        // Parsear KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            // Remover comillas
            if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            if (!array_key_exists($name, $_ENV)) {
                putenv("$name=$value");
                $_ENV[$name] = $value;
            }
        }
    }
    
    return true;
}

// Cargar .env automáticamente si se incluye este archivo
loadEnv();
