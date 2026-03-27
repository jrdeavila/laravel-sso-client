<?php

return [
    // Secret compartido con el lanzador — debe coincidir con SSO_SHARED_SECRET del lanzador
    'secret' => env('SSO_SECRET'),

    // URL base del lanzador para redirecciones de error
    'launcher_url' => env('SSO_LAUNCHER_URL'),

    // TTL en segundos — valor por defecto del lanzador: 60
    'token_ttl' => env('SSO_TOKEN_TTL', 60),

    // Ruta destino en la receptora tras login exitoso
    'redirect_after_login' => env('SSO_REDIRECT_AFTER_LOGIN', '/dashboard'),

    // Nombre del query param que trae el token — debe coincidir con el lanzador
    'token_param' => env('SSO_TOKEN_PARAM', 'token'),

    // Campo del payload JWT que lleva el ID del usuario — debe coincidir con el lanzador
    'user_id_field' => env('SSO_USER_ID_FIELD', 'sub'),

    // Modelo Eloquent del usuario en la receptora
    'user_model' => env('SSO_USER_MODEL', 'App\\Models\\User'),
];
