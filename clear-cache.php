<?php
// Script temporal para limpiar caché
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache limpiado exitosamente\n";
} else {
    echo "⚠️ OPcache no está habilitado\n";
}

if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "✅ APC cache limpiado\n";
}

echo "\n🔄 Por favor actualiza tu navegador con Ctrl+F5 (hard refresh)\n";
echo "📁 Verificando archivos...\n";
echo "Index.php modificado: " . date("Y-m-d H:i:s", filemtime('index.php')) . "\n";
?>

