<?php
/**
 * Configuración de widgets CCV.
 *
 * Publicar con:
 *   php artisan vendor:publish --tag=ccv-widgets-config
 *
 * Solo se activa si este archivo existe en config/ de la app receptora.
 * Sin este archivo, las rutas y recursos de widgets no se registran.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Widgets que esta app ofrece al lanzador
    |--------------------------------------------------------------------------
    |
    | Claves del array = slug único del widget (usado en la URL y el lanzador).
    |
    | Campos por widget:
    |   name         string        Nombre legible para el lanzador
    |   type         string        chatbot | survey | notification | announcement
    |   view         string        Vista Blade. Prefijo 'sso-client::widgets.' para
    |                              vistas del paquete, o nombre directo para vistas
    |                              propias de la app ('widgets.mi-anuncio')
    |   layout       string        Layout Blade. Default: 'sso-client::widgets.layout'
    |   middleware   array         Middleware adicional (además del SSO que ya aplica)
    |   check_class  string|null   Solo para type='announcement'. FQCN de una clase
    |                              invocable: __invoke(Request $request): bool
    |                              El lanzador llama GET /widgets/{slug}/check?token=...
    |                              antes de mostrar el anuncio. Si retorna false, no
    |                              se muestra en esa visita. Omitir = siempre visible.
    |   enabled      bool          false = no se registra la ruta ni aparece en manifest
    */

    'widgets' => [

        'example-chatbot' => [
            'name'       => 'Chatbot de ejemplo',
            'type'       => 'chatbot',
            'view'       => 'sso-client::widgets.example.chatbot',
            'layout'     => 'sso-client::widgets.layout',
            'middleware' => [],
            'enabled'    => true,
        ],

    ],

];
