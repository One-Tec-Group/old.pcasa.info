<?php

return [
    //menus
    ['name' => 'menus', 'value' => json_encode([
        [
            'position' => 'dashboard',
            'name' => 'Dashboard',
            'items' => [['type' => 'route','order' => 1,'condition' => 'admin','position' => 0,'label' => 'Admin Area','action' => 'admin']],
        ]
    ])],

    //branding
    ['name' => 'branding.site_name', 'value' => 'Architect'],

    //builder
    ['name' => 'builder.routing_type', 'value' => 'regular'],
    ['name' => 'builder.googgle_fonts_api_key', 'value' => 'AIzaSyDhc_8NKxXjtv69htFcUPe6A7oGSQ4om2o'],
    ['name' => 'builder.template_categories', 'value' => json_encode(['Landing Page', 'Blog', 'Portfolio'])],
];
