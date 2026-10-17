# Módulo de Logística

## 1. Arquitectura Técnica (SDD)

### Archivos de Código Fuente
- `app/Http/Controllers/Repartidor/RepartidorController.php` — CRUD repartidores, cambiar estado
- `app/Http/Controllers/Repartidor/RepartidorVehiculoController.php` — asignar/liberar vehículo
- `app/Http/Controllers/Vehiculo/VehiculoController.php` — CRUD vehículos, cambiar estado
- `app/Http/Controllers/Logistica/HojaRutaController.php` — gestión de hojas de ruta (iniciar, registrar llegada, entregas, devoluciones, regreso)
- `app/Services/Logistica/AsignacionVehiculoService.php` — lógica de asignación de vehículos
- `app/Events/RutaIniciada.php`, `app/Events/RepartidorRegresa.php`
- `app/Events/EntregaCompletada.php`, `app/Events/EntregaFallida.php`, `app/Events/DevolucionProducto.php`
- `app/Listeners/ActualizarEstadoPedidosListener.php`
- `app/Listeners/ActualizarEstadoRepartidorListener.php`
- `app/Listeners/ActualizarEstadoVehiculoListener.php`
- `app/Listeners/RegistrarLlegadaParadaListener.php`

### Stack
- **Controladores:** `RepartidorController`, `RepartidorVehiculoController`, `VehiculoController`, `HojaRutaController`
- **Event-Driven:** 5 eventos de dominio orquestan cambios de estado
- **Patrón Observer:** 5 listeners agrupados como subscribers en `EventServiceProvider`
- **Servicio:** `AsignacionVehiculoService` (asignación/liberación con cierre automático de asignación anterior)

### Endpoints

| Método | Ruta | Controlador |
|--------|------|-------------|
| CRUD | `/api/repartidores[/{id}]` | `RepartidorController` |
| POST | `/api/repartidores/{id}/cambiar-estado` | `RepartidorController::cambiarEstado` |
| POST | `/api/repartidores/{id}/asignacion-vehiculo` | `RepartidorVehiculoController::asignar` |
| DELETE | `/api/repartidores/{id}/asignacion-vehiculo` | `RepartidorVehiculoController::liberar` |
| CRUD | `/api/vehiculos[/{id}]` | `VehiculoController` |
| POST | `/api/vehiculos/{id}/cambiar-estado` | `VehiculoController::cambiarEstado` |
| CRUD | `/api/hojas-ruta[/{id}]` | `HojaRutaController` |
| POST | `/api/hojas-ruta/{id}/iniciar` | `HojaRutaController::iniciarRuta` |
| POST | `/api/hojas-ruta/{id}/registrar-llegada` | `HojaRutaController::registrarLlegada` |
| POST | `/api/hojas-ruta/{id}/entrega-completada` | `HojaRutaController::marcarEntregaCompletada` |
| POST | `/api/hojas-ruta/{id}/entrega-fallida` | `HojaRutaController::marcarEntregaFallida` |
| POST | `/api/hojas-ruta/{id}/devolucion` | `HojaRutaController::marcarDevolucion` |
| POST | `/api/hojas-ruta/{id}/regreso-almacen` | `HojaRutaController::regresoAlmacen` |
| GET | `/api/logistica/pedidos-pendientes` | `HojaRutaController::pedidosPendientes` |
| GET | `/api/logistica/repartidores-disponibles` | `HojaRutaController::repartidoresDisponibles` |
| GET | `/api/logistica/rutas-activas` | `HojaRutaController::rutasActivas` |

### Flujo de Eventos
```
RutaIniciada → PedidosListener (→aprobado), RepartidorListener (→en_ruta), VehiculoListener (→en_movimiento)
EntregaCompletada → PedidosListener (→entregado), DespachoListener (→entregado), LlegadaParadaListener (hora)
EntregaFallida → PedidosListener (→pendiente), DespachoListener (→incidencia), LlegadaParadaListener (hora)
DevolucionProducto → PedidosListener (→entregado), DespachoListener (→incidencia), LlegadaParadaListener (hora)
RepartidorRegresa → RepartidorListener (→disponible), VehiculoListener (→operativo)
```

---

## 2. Especificación de Comportamiento (BDD)

### Escenario L1: CRUD de repartidores
```
Dado un administrador autenticado
Cuando crea un repartidor con usuario, CI y categoría de licencia
Entonces el repartidor se registra
Y puede asignar/liberar vehículos al repartidor
```
- **Test Feature:** `Logistica/RepartidorCRUDTest.php`
- **Test Unit:** `Logistica/Models/RepartidorTest.php`
- **Controlador:** `RepartidorController`, `RepartidorVehiculoController`

### Escenario L2: CRUD de vehículos
```
Dado un administrador autenticado
Cuando crea un vehículo con placa, marca y modelo
Entonces el vehículo se registra
Y no se permite crear vehículos con placa duplicada
Y se puede cambiar su estado
```
- **Test Feature:** `Logistica/VehiculoCRUDTest.php`
- **Test Unit:** `Logistica/Models/VehiculoTest.php`
- **Controlador:** `VehiculoController`

### Escenario L3: Asignación de vehículo
```
Dado un repartidor y vehículos existentes
Cuando se asigna un vehículo a un repartidor
Entonces se crea la asignación sin fecha_devolucion
Y si había una asignación anterior, se cierra con fecha_devolucion
Y al liberar, la asignación activa se cierra
```
- **Test Unit:** `Logistica/Services/AsignacionVehiculoServiceTest.php`, `Logistica/Models/AsignacionVehiculoRepartidorTest.php`
- **Servicio:** `AsignacionVehiculoService`

### Escenario L4: Hoja de ruta con ciclo completo
```
Dado un administrador autenticado
Cuando crea una hoja de ruta con repartidor, vehículo y pedidos
Entonces la hoja de ruta se crea con estado "planificada"
Y al iniciarla pasa a "en_progreso"
Y se pueden registrar llegadas, completar entregas, reportar fallos y devoluciones
Y al regresar al almacén la ruta finaliza
```
- **Test Feature:** `Logistica/HojaRutaTest.php`
- **Test Unit:** `Logistica/Models/HojaRutaTest.php`, `Logistica/Models/HojaRutaDetalleTest.php`
- **Controlador:** `HojaRutaController`

### Escenario L5: Eventos de ruta y su efecto
```
Dado que se inicia una ruta (RutaIniciada)
Entonces los pedidos pendientes pasan a "aprobado"
Y el repartidor pasa a "en_ruta"
Y el vehículo pasa a "en_movimiento"
Cuando el repartidor regresa (RepartidorRegresa)
Entonces el repartidor vuelve a "activo"
Y el vehículo vuelve a "disponible"
```
- **Test Unit:** `Logistica/Events/RutaIniciadaTest.php`, `Logistica/Events/RepartidorRegresaTest.php`
- **Test Unit:** `Logistica/Listeners/ActualizarEstadoPedidosListenerTest.php`, `Logistica/Listeners/ActualizarEstadoRepartidorListenerTest.php`, `Logistica/Listeners/ActualizarEstadoVehiculoListenerTest.php`
- **Test Unit:** `Logistica/Listeners/RegistrarLlegadaParadaListenerTest.php`

### Escenario L6: Consultas logísticas
```
Dado un administrador autenticado
Cuando consulta GET /api/logistica/pedidos-pendientes
Entonces obtiene la lista de pedidos disponibles para ruta
Y puede consultar repartidores disponibles y rutas activas
```
- **Test Feature:** `Logistica/HojaRutaTest.php`
- **Controlador:** `HojaRutaController`

---

## 3. Trazabilidad de Tests

### Tests Feature
| Archivo de Test | Grupo Pest | Controlador |
|---|---|---|
| `tests/Feature/Logistica/RepartidorCRUDTest.php` | `repartidores, crud` | `RepartidorController` |
| `tests/Feature/Logistica/VehiculoCRUDTest.php` | `vehiculos, crud` | `VehiculoController` |
| `tests/Feature/Logistica/HojaRutaTest.php` | `hojas-ruta, logistica` | `HojaRutaController` |

### Tests Unit
| Archivo de Test | Namespace |
|---|---|
| `tests/Unit/Logistica/Models/RepartidorTest.php` | `Tests\Unit\Logistica\Models` |
| `tests/Unit/Logistica/Models/VehiculoTest.php` | `Tests\Unit\Logistica\Models` |
| `tests/Unit/Logistica/Models/HojaRutaTest.php` | `Tests\Unit\Logistica\Models` |
| `tests/Unit/Logistica/Models/HojaRutaDetalleTest.php` | `Tests\Unit\Logistica\Models` |
| `tests/Unit/Logistica/Models/AsignacionVehiculoRepartidorTest.php` | `Tests\Unit\Logistica\Models` |
| `tests/Unit/Logistica/Events/RutaIniciadaTest.php` | `Tests\Unit\Logistica\Events` |
| `tests/Unit/Logistica/Events/RepartidorRegresaTest.php` | `Tests\Unit\Logistica\Events` |
| `tests/Unit/Logistica/Listeners/ActualizarEstadoPedidosListenerTest.php` | `Tests\Unit\Logistica\Listeners` |
| `tests/Unit/Logistica/Listeners/ActualizarEstadoRepartidorListenerTest.php` | `Tests\Unit\Logistica\Listeners` |
| `tests/Unit/Logistica/Listeners/ActualizarEstadoVehiculoListenerTest.php` | `Tests\Unit\Logistica\Listeners` |
| `tests/Unit/Logistica/Listeners/RegistrarLlegadaParadaListenerTest.php` | `Tests\Unit\Logistica\Listeners` |
| `tests/Unit/Logistica/Services/AsignacionVehiculoServiceTest.php` | `Tests\Unit\Logistica\Services` |
