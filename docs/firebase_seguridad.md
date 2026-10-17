# Seguridad de Firebase Realtime Database

## Resumen

La base de datos paso de reglas abiertas (`.read: true, .write: true`) a un
esquema **Nivel A**: cierre por defecto, escrituras limitadas a rutas concretas
con validacion y `viajes_activos` de solo lectura para clientes. El backend
escribe `viajes_activos` con un **access_token de administrador** (cuenta de
servicio), por lo que las reglas no le afectan.

Reglas en `database.rules.json`.

## Que protege y que no

Protege:
- No se puede borrar ni sobrescribir la base completa.
- Solo se puede escribir en `usuarios/{uid}/info` y `usuarios/{uid}/ubicacion`.
- Validacion de tipos y rango de coordenadas (rechaza `NaN`, strings, fuera de rango).
- `viajes_activos` no es escribible por clientes (solo backend admin).

No protege (limitacion de no usar autenticacion de clientes):
- Cualquiera con la `databaseURL` puede **leer** el GPS de todos.
- Se puede **falsear** la ubicacion de cualquier `uid` (con valores validos).

Si en el futuro se necesita impedir el spoofing, migrar a **custom tokens**
(ver plan Nivel C).

## Pasos obligatorios en el servidor / Firebase Console

1. **Backup**: Firebase Console -> Realtime Database -> Exportar JSON.
2. **Cuenta de servicio**:
   - Firebase Console -> Configuracion del proyecto -> Cuentas de servicio ->
     "Generar nueva clave privada". Descargar el JSON.
   - Crear la carpeta y colocar el JSON:
     `mkdir -p storage/app/firebase`
     y dejarlo en `storage/app/firebase/service-account.json` (la carpeta esta
     ignorada por git), con permisos restrictivos (`chmod 600`).
   - `.env`: `FIREBASE_CREDENTIALS=storage/app/firebase/service-account.json`
     (acepta ruta relativa a la raiz del proyecto o absoluta).
3. **Publicar reglas**:
   - Opcion A (CLI): `firebase deploy --only database`
     (necesita `firebase.json`, ya incluido; indicar `--project <id>`).
   - Opcion B (Console): pegar el contenido de `database.rules.json` en
     Realtime Database -> Reglas -> Publicar.
4. **Probar** con el simulador de reglas de la consola:
   - Web (staff) lee `usuarios` -> permitido.
   - App escribe `usuarios/{id}/info` con `id === {id}` -> permitido.
   - App escribe `ubicacion` valida -> permitido; `latitud = 999` -> denegado.
   - Cliente intenta escribir `viajes_activos/...` -> denegado.
   - Backend (con token) escribe `viajes_activos/...` -> permitido.
5. **Limpiar** el backend para que no haya claves muertas: `FIREBASE_API_KEY`
   ya no se usa en el backend (sigue usandose en el cliente web/app).

## Notas

- Sin `service-account.json`, `FirebaseAuthService::accessToken()` devuelve
  `null` y el backend escribe sin token (util solo en local con reglas
  abiertas). En produccion con reglas cerradas **es obligatorio** el JSON.
- El token se cachea ~50 min (`firebase_rtdb_access_token`). Si se cambia la
  cuenta de servicio, limpiar cache (`php artisan cache:clear`).
