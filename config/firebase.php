<?php

/*
 * Ruta al JSON de la cuenta de servicio de Firebase. Se usa para que el
 * backend obtenga un access_token con rol admin y pueda escribir en nodos
 * protegidos por reglas (p. ej. viajes_activos). Nunca debe estar dentro
 * del docroot ni subirse al repositorio.
 *
 * Acepta rutas absolutas o relativas a la raiz del proyecto (se resuelven
 * aqui para que funcionen tanto en CLI como bajo el docroot web).
 */
$arc = env('FIREBASE_CREDENTIALS', 'storage/app/firebase/service-account.json');
$arcEsAbsoluta =
    str_starts_with($arc, '/') ||
    str_starts_with($arc, '\\\\') ||
    (bool) preg_match('#^[A-Za-z]:[\\\\/]#', $arc);

return [
    'database_url' => env('FIREBASE_DATABASE_URL', 'https://mapboxfirebase-a79b5-default-rtdb.firebaseio.com'),
    'api_key' => env('FIREBASE_API_KEY', 'AIzaSyC6ocm9J7hQTTf_eRpfrZlZgbt50JHQlIE'),
    'credentials' => $arcEsAbsoluta ? $arc : base_path($arc),
];
