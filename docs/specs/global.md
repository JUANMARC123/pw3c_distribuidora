# Módulo Global / Transversal

## 1. Arquitectura Técnica (SDD)

### Archivos de Código Fuente
- `app/Http/Controllers/ApiController.php` — base controller con helpers `jsonResponse`, `errorResponse`, `safePaginate`
- `app/Providers/EventServiceProvider.php` — registro de eventos y subscribers
- `app/Providers/AppServiceProvider.php`
- `app/Providers/AuthServiceProvider.php`
- `app/Providers/RouteServiceProvider.php`
- `app/Providers/BroadcastServiceProvider.php`
- `app/Exceptions/Handler.php`
- `app/Console/Kernel.php`

### ApiController
- **Propósito:** Clase base para todos los controladores de la API.
- **Métodos heredados:**
  - `jsonResponse($data, $message, $statusCode, $headers)` — respuesta JSON estándar
  - `errorResponse($message, $statusCode, $errors)` — respuesta de error
  - `safePaginate($query, $perPage)` — paginación con captura de errores
- **Uso:** Todos los controladores (`AuthController`, `PedidoController`, etc.) extienden `ApiController`.

### EventServiceProvider
- **Propósito:** Registro centralizado de eventos y subscribers.
- **Eventos registrados en `$listen`:**
  - `Illuminate\Auth\Events\Registered` → `Illuminate\Auth\Listeners\SendEmailVerificationNotification`
- **Subscribers registrados en `boot()`:**
  - `RutaIniciada` → listeners de pedidos, repartidor, vehículo
  - `EntregaCompletada` → listeners de pedidos, despacho, llegada parada
  - `EntregaFallida` → listeners de pedidos, despacho, llegada parada
  - `DevolucionProducto` → listeners de pedidos, despacho, llegada parada
  - `RepartidorRegresa` → listeners de repartidor, vehículo

---

## 2. Especificación de Comportamiento (BDD)

### Escenario G1: Configuración del sistema
```
Dado un entorno de aplicación configurado
Entonces la configuración CORS permite api/* desde cualquier origen
Y la autenticación usa guard "web" con modelo Usuario
Y la base de datos MySQL usa utf8mb4
Y los filesystems tienen disco local y public
```
- **Test Unit:** `Global/Config/ConfigTest.php`

### Escenario G2: Registro de eventos y subscribers
```
Dado el EventServiceProvider registrado
Entonces tiene el evento Registered en $listen
Y al ejecutar boot(), registra los 5 eventos de dominio como subscribers:
RutaIniciada, EntregaCompletada, EntregaFallida, DevolucionProducto, RepartidorRegresa
```
- **Test Unit:** `Global/Providers/EventServiceProviderTest.php`

### Escenario G3: Sin debuggers en producción
```
Dado el código fuente en app/
Entonces ningún archivo contiene dd(), dump(), var_dump(), print_r() o exit()
```
- **Test Unit:** `ArchTest.php`

---

## 3. Trazabilidad de Tests

### Tests Unit
| Archivo de Test | Namespace |
|---|---|
| `tests/Unit/Global/Config/ConfigTest.php` | `Tests\Unit\Global\Config` |
| `tests/Unit/Global/Providers/EventServiceProviderTest.php` | `Tests\Unit\Global\Providers` |
| `tests/Unit/ArchTest.php` | Sin namespace (root) |
| `tests/Unit/ExampleTest.php` | Sin namespace (root) |
