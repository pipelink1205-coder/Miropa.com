<?php

/**
 * Configuración diferencial del wizard de publicación Moda.
 * Los contextos se resuelven desde la categoría hoja (tipo) vía FashionPublishContext.
 */
return [

    'size_extras' => ['Única'],

    'listing_modes' => [
        ['value' => 'compra_protegida', 'label' => 'Compra protegida', 'description' => 'El comprador paga dentro de la plataforma'],
        ['value' => 'trato_directo', 'label' => 'Trato directo', 'description' => 'Solo contacto por chat, sin pago en app'],
    ],

    'contexts' => [
        'ropa' => [
            'size_keys' => ['ropa'],
            'show_brand' => true,
            'brand_required' => true,
            'show_color' => true,
            'show_measurements' => true,
            'show_size_mismatch' => true,
            'show_size_note' => false,
            'show_listing_type' => false,
            'photo_tips' => [
                'Foto de frente con buena luz natural',
                'Foto de la parte de atrás',
                'Etiqueta con marca y talla',
                'Zoom en defectos o detalles de uso',
            ],
        ],
        'calzado' => [
            'size_keys' => ['calzado'],
            'show_brand' => true,
            'brand_required' => true,
            'show_color' => true,
            'show_measurements' => false,
            'show_sole_length' => true,
            'show_size_mismatch' => false,
            'show_size_note' => false,
            'show_listing_type' => false,
            'photo_tips' => [
                'Par de zapatos de frente',
                'Suela y tacón (si aplica)',
                'Interior y etiqueta de talla',
            ],
        ],
        'accesorios' => [
            'size_keys' => [],
            'default_size' => 'Única',
            'show_brand' => true,
            'brand_required' => false,
            'show_color' => true,
            'show_measurements' => false,
            'show_size_mismatch' => false,
            'show_size_note' => false,
            'show_listing_type' => false,
            'photo_tips' => [
                'Foto general del accesorio',
                'Detalle de marca o placa',
                'Interior (bolsos, carteras)',
            ],
        ],
        'ninos_bebe' => [
            'size_keys' => ['bebe'],
            'show_brand' => true,
            'brand_required' => false,
            'show_color' => true,
            'show_measurements' => false,
            'show_size_mismatch' => false,
            'show_size_note' => true,
            'show_listing_type' => true,
            'photo_tips' => [
                'Prenda sobre fondo neutro (sin rostro del bebé)',
                'Etiqueta de talla por meses',
                'Detalle de estado y cierre',
            ],
        ],
        'ninos_nina_nino' => [
            'size_keys' => ['toddler', 'nino_nina'],
            'show_brand' => true,
            'brand_required' => false,
            'show_color' => true,
            'show_measurements' => false,
            'show_size_mismatch' => true,
            'show_size_note' => true,
            'show_listing_type' => true,
            'photo_tips' => [
                'Prenda extendida o en perchero',
                'Etiqueta de talla',
                'Detalle de uso o manchas',
            ],
        ],
    ],

];
