<?php 
class FilterConfig
{
    private static $config = null;
    
    public static function load()
    {
        if (self::$config === null) {
            self::$config = include __DIR__ . '/filtros_config.php';
        }
        return self::$config;
    }
    
    public static function getSubcategoriesByPilar($pilar)
    {
        $config = self::load();
        $pilar = strtoupper($pilar);
        
        return isset($config[$pilar]) ? $config[$pilar] : [];
    }
    
    public static function getAllPilares()
    {
        $config = self::load();
        return array_keys($config);
    }
    
    public static function findPilarBySubcategoria($subcategoria)
    {
        $config = self::load();
        
        foreach ($config as $pilar => $subcategorias) {
            if (in_array(strtolower($subcategoria), array_map('strtolower', $subcategorias))) {
                return $pilar;
            }
        }
        
        return null;
    }
}
