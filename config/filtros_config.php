<?php

// CONFIGURACIÓN DE FILTROS PARA EL MENÚ EN EL LISTADO DE ORDENES, ACA AGREGA LAS CATEGORIAS CON LA QUE APARECERÁN LOS FILTROS EN EL MENÚ DE ORDENES
// CADA CATEGORÍA DEBE LAS SUBCATEGORIAS CREADAS EN TU GESTION DE CATEGORIAS

return [
    'BEBIDAS' => [
        'Bebidas',
        'jarras',
        'jugos',
        'aguas',
        'sodas',
        'refrescos',
    ],
    'COMIDA' => [
        'entradas',
        'platos_fuertes',
        'ensaladas',
        'Sopas'
    ],
    'POSTRES' => [
        'helados',
        'pasteles',
        'frutas',
        'dulces'
    ],
    'ALCOHOL' => [
        'cervezas',
        'vinos',
        'licores',
        'cocktails'
    ],
    'PIZZAS' => [
        'Pizzas',
        'especiales',
        'vegetarianas',
        'sin_gluten'
    ],
];