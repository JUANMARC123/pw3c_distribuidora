# Testing - Pw3c Distribuidora

> **Versión:** 4.0
> **Total de tests:** 246 (90 Feature + 156 Unit)
> **Framework:** Pest PHP v1 (sintaxis 100% Pest)

---

## Índice

1. [Arquitectura](#1-arquitectura)
2. [Estructura de archivos](#2-estructura-de-archivos)
3. [Tests Feature (HTTP)](#3-tests-feature-http)
4. [Tests Unit (por módulo)](#4-tests-unit-por-módulo)
5. [Cómo ejecutar](#5-cómo-ejecutar)

---

## 1. Arquitectura

### Framework
Pest PHP v1 sobre PHPUnit 9. Todos los tests usan `it()`, `test()`, `beforeEach()`, `expect()`.

### Base de datos
- SQLite en memoria (`:memory:`)
- `Tests\TestCase` usa `RefreshDatabase` + ejecuta `CatalogoSeeder` en `setUp()`
- `uses(TestCase::class)->in('Feature')` y `uses(TestCase::class)->in('Unit')` en `Pest.php`

### Helpers globales (`tests/Pest.php`)
| Función | Descripción |
|---|---|
| `actingAsAdmin()` | Crea usuario + rol Administrador + autentica Sanctum |
| `actingAsSupervisor()` | Crea usuario + rol Supervisor + autentica Sanctum |
| `actingAsOperador()` | Crea usuario + rol Operador + autentica Sanctum |
| `actingAsRepartidor()` | Crea usuario + rol Repartidor + autentica Sanctum |

---

## 2. Estructura de archivos

```
tests/
├── Pest.php
├── TestCase.php
├── Feature/
│   ├── ExampleTest.php
│   ├── Auth/                              ↔ docs/specs/auth.md
│   │   ├── LoginTest.php
│   │   ├── LogoutTest.php
│   │   ├── RegisterTest.php
│   │   ├── UserTest.php
│   │   └── UsuarioCRUDTest.php
│   ├── Catalogo/                          ↔ docs/specs/catalogos.md
│   │   ├── FarmaciaCRUDTest.php
│   │   └── ProductoCRUDTest.php
│   ├── Logistica/                         ↔ docs/specs/logistica.md
│   │   ├── HojaRutaTest.php
│   │   ├── RepartidorCRUDTest.php
│   │   └── VehiculoCRUDTest.php
│   ├── Pedido/                            ↔ docs/specs/pedidos.md
│   │   └── PedidoCRUDTest.php
│   ├── Despacho/                          ↔ docs/specs/despachos.md
│   │   ├── CambiarEstadoDespachoTest.php
│   │   ├── CicloCompletoDespachoTest.php
│   │   ├── IncidenciaEvidenciaTest.php
│   │   └── ValidarEvidenciaEntregaTest.php
│   ├── Caja/                              ↔ docs/specs/caja.md
│   │   └── CajaDiariaTest.php
│   └── Reporte/                           ↔ docs/specs/reportes.md
│       └── ReportesTest.php
└── Unit/                              ↔ docs/specs/*.md (8 módulos)
    ├── ArchTest.php
    ├── ExampleTest.php
    ├── Auth/                            ↔ docs/specs/auth.md
    │   ├── Models/UsuarioTest.php
    │   └── Requests/
    │       ├── LoginRequestTest.php
    │       ├── RegisterRequestTest.php
    │       └── UsuarioStoreRequestTest.php
    ├── Catalogo/                        ↔ docs/specs/catalogos.md
    │   └── Models/
    │       ├── FarmaciaTest.php
    │       ├── ContactoFarmaciaTest.php
    │       └── ProductoTest.php
    ├── Pedido/                          ↔ docs/specs/pedidos.md
    │   ├── Models/
    │   │   ├── PedidoTest.php
    │   │   └── DetallePedidoTest.php
    │   └── Services/
    │       └── PedidoPdfExtractorTest.php
    ├── Logistica/                       ↔ docs/specs/logistica.md
    │   ├── Models/
    │   │   ├── RepartidorTest.php
    │   │   ├── VehiculoTest.php
    │   │   ├── HojaRutaTest.php
    │   │   ├── HojaRutaDetalleTest.php
    │   │   └── AsignacionVehiculoRepartidorTest.php
    │   ├── Events/
    │   │   ├── RutaIniciadaTest.php
    │   │   └── RepartidorRegresaTest.php
    │   ├── Listeners/
    │   │   ├── ActualizarEstadoPedidosListenerTest.php
    │   │   ├── ActualizarEstadoRepartidorListenerTest.php
    │   │   ├── ActualizarEstadoVehiculoListenerTest.php
    │   │   └── RegistrarLlegadaParadaListenerTest.php
    │   └── Services/
    │       └── AsignacionVehiculoServiceTest.php
    ├── Despacho/                        ↔ docs/specs/despachos.md
    │   ├── Models/
    │   │   ├── DespachoTest.php
    │   │   ├── EvidenciaEntregaTest.php
    │   │   └── IncidenciaTest.php
    │   ├── Events/
    │   │   ├── EntregaCompletadaTest.php
    │   │   ├── EntregaFallidaTest.php
    │   │   └── DevolucionProductoTest.php
    │   └── Listeners/
    │       └── ActualizarEstadoDespachoListenerTest.php
    ├── Caja/                            ↔ docs/specs/caja.md
    │   └── Models/
    │       ├── CajaRepartidorTest.php
    │       └── GastoOperativoTest.php
    └── Global/                          ↔ docs/specs/global.md
        ├── Config/ConfigTest.php
        └── Providers/EventServiceProviderTest.php
```

---

## 3. Tests Feature (HTTP)

### `Auth/LoginTest.php` — 5 tests
| Test | Lo que valida |
|---|---|
| `loguea con credenciales correctas` | POST `/api/auth/login` con email+password correctos → 200, respuesta contiene `user`, `token`, `permisos` |
| `rechaza contraseña incorrecta` | POST con email correcto + password incorrecto → 422 |
| `rechaza email inexistente` | POST con email que no existe → 422 |
| `rechaza login sin credenciales` | POST con payload vacío → 422 |
| `bloquea usuario inactivo` | POST con usuario con estado `bloqueado` → 403 |

### `Auth/RegisterTest.php` — 4 tests
| Test | Lo que valida |
|---|---|
| `registra un nuevo usuario` | POST `/api/auth/register` con datos válidos → 201, respuesta contiene `user`, `token`, `permisos` |
| `rechaza email duplicado` | POST con email ya registrado → 422 |
| `requiere nombre, apellido, email y password` | POST con payload vacío → 422 |
| `requiere password minimo 6 caracteres` | POST con password < 6 caracteres → 422 |

### `Auth/LogoutTest.php` — 2 tests
| Test | Lo que valida |
|---|---|
| `cierra sesion correctamente` | POST `/api/auth/logout` autenticado → 200, `success: true` |
| `requiere autenticacion para logout` | POST sin token → 401 |

### `Auth/UserTest.php` — 3 tests
| Test | Lo que valida |
|---|---|
| `obtiene el usuario autenticado` | GET `/api/auth/user` → 200, email coincide |
| `incluye permisos del usuario` | Respuesta incluye `user` y `permisos` en `data` |
| `requiere autenticacion` | GET sin token → 401 |

### `Auth/UsuarioCRUDTest.php` — 6 tests
| Test | Lo que valida |
|---|---|
| `lista usuarios paginados` | GET `/api/usuarios` → 200 con `success`, `data`, `meta` |
| `crea un usuario` | POST `/api/usuarios` → 201, email correcto en respuesta |
| `muestra un usuario` | GET `/api/usuarios/{id}` → 200, `id_usuario` correcto |
| `actualiza un usuario` | PUT `/api/usuarios/{id}` → nombre se actualiza en BD |
| `elimina un usuario` | DELETE `/api/usuarios/{id}` → usuario eliminado de BD |
| `bloquea un usuario` | PUT `/api/usuarios/{id}/bloquear` → estado pasa a `bloqueado` |

### `Catalogo/ProductoCRUDTest.php` — 6 tests
| Test | Lo que valida |
|---|---|
| `lista productos` | GET `/api/productos` → 200 |
| `crea un producto` | POST `/api/productos` → 201, nombre correcto |
| `muestra un producto` | GET `/api/productos/{id}` → 200 |
| `actualiza un producto` | PUT `/api/productos/{id}` → nombre se actualiza en BD |
| `elimina un producto` | DELETE `/api/productos/{id}` → 200 |
| `valida datos requeridos del producto` | POST vacío → 422 |

### `Catalogo/FarmaciaCRUDTest.php` — 7 tests
| Test | Lo que valida |
|---|---|
| `lista farmacias` | GET `/api/farmacias` → 200 |
| `crea una farmacia` | POST `/api/farmacias` → 201, nombre correcto |
| `muestra una farmacia` | GET `/api/farmacias/{id}` → 200 |
| `actualiza una farmacia` | PUT `/api/farmacias/{id}` → nombre se actualiza en BD |
| `elimina una farmacia` | DELETE `/api/farmacias/{id}` → 200 |
| `gestiona contactos de farmacia` | POST contacto + GET farmacia → contacto listado en `contactos` |
| `valida datos requeridos al crear farmacia` | POST vacío → 422 |

### `Logistica/VehiculoCRUDTest.php` — 7 tests
| Test | Lo que valida |
|---|---|
| `lista vehiculos` | GET `/api/vehiculos` → 200 |
| `crea un vehiculo` | POST `/api/vehiculos` → 201, placa correcta |
| `muestra un vehiculo` | GET `/api/vehiculos/{id}` → 200 |
| `actualiza un vehiculo` | PUT `/api/vehiculos/{id}` → estado cambia en BD |
| `cambia estado del vehiculo` | POST `/api/vehiculos/{id}/cambiar-estado` → 200 |
| `elimina un vehiculo` | DELETE `/api/vehiculos/{id}` → 200 |
| `valida placa unica` | POST con placa duplicada → 422 |

### `Logistica/RepartidorCRUDTest.php` — 8 tests
| Test | Lo que valida |
|---|---|
| `lista repartidores` | GET `/api/repartidores` → 200 |
| `crea un repartidor` | POST `/api/repartidores` → 201 |
| `muestra un repartidor` | GET `/api/repartidores/{id}` → 200 |
| `actualiza un repartidor` | PUT `/api/repartidores/{id}` → categoría licencia se actualiza |
| `cambia estado del repartidor` | POST `/api/repartidores/{id}/cambiar-estado` → 200 |
| `elimina un repartidor` | DELETE `/api/repartidores/{id}` → 200 |
| `asigna vehiculo al repartidor` | POST `/api/repartidores/{id}/asignacion-vehiculo` → 201 |
| `remueve vehiculo del repartidor` | DELETE `/api/repartidores/{id}/asignacion-vehiculo` → 200 |

### `Logistica/HojaRutaTest.php` — 7 tests
| Test | Lo que valida |
|---|---|
| `lista hojas de ruta` | GET `/api/hojas-ruta` → 200 |
| `crea una hoja de ruta con pedidos` | POST con pedidos → 201, respuesta incluye `detalles` |
| `inicia una ruta planificada` | POST `/api/hojas-ruta/{id}/iniciar` → estado `en_progreso` |
| `rechaza iniciar ruta no planificada` | POST sobre ruta no-planificada → 422 |
| `obtiene pedidos pendientes` | GET `/api/logistica/pedidos-pendientes` → 200 |
| `obtiene repartidores disponibles` | GET `/api/logistica/repartidores-disponibles` → 200 |
| `obtiene rutas activas` | GET `/api/logistica/rutas-activas` → 200 |

### `Pedido/PedidoCRUDTest.php` — 8 tests
| Test | Lo que valida |
|---|---|
| `lista pedidos` | GET `/api/pedidos` → 200 |
| `crea un pedido` | POST `/api/pedidos` → 201 |
| `muestra un pedido con detalles` | GET `/api/pedidos/{id}` → 200 con estructura `detalles` |
| `actualiza un pedido` | PUT `/api/pedidos/{id}` → estado cambia a `cancelado` |
| `cambia estado del pedido` | POST `/api/pedidos/{id}/cambiar-estado` → 200 |
| `elimina un pedido` | DELETE `/api/pedidos/{id}` → 200 |
| `gestiona detalles del pedido` | POST detalle → 201, GET pedido → detalle en `detalles` |
| `valida datos requeridos del pedido` | POST vacío → 422 |

### `Despacho/CambiarEstadoDespachoTest.php` — 6 tests
| Test | Lo que valida |
|---|---|
| `cambia estado de pendiente a entregado` | Transición `pendiente` → `entregado` persiste en BD |
| `cambia estado de pendiente a incidencia` | Transición `pendiente` → `incidencia` persiste en BD |
| `cambia estado de entregado a incidencia` | Transición `entregado` → `incidencia` → 200 |
| `rechaza estado invalido` | POST con string inválido → 422 |
| `requiere campo estado_despacho` | POST sin campo → 422 |
| `404 si el despacho no existe` | POST a ID inexistente → 404 |

### `Despacho/ValidarEvidenciaEntregaTest.php` — 2 tests
| Test | Lo que valida |
|---|---|
| `Escenario 1: Bloquea la finalizacion si el despacho no tiene ninguna evidencia` | POST sin evidencias → 422 con mensaje de error |
| `Escenario 2: Permite completar la entrega si el despacho ya tiene una evidencia registrada` | POST con evidencias → 200, estado `entregado` en BD |

### `Despacho/IncidenciaEvidenciaTest.php` — 2 tests
| Test | Lo que valida |
|---|---|
| `gestiona incidencias en un despacho` | POST incidencia + GET despacho → incidencia listada |
| `gestiona evidencias en un despacho` | POST evidencia (archivo) + GET despacho → evidencia listada |

### `Despacho/CicloCompletoDespachoTest.php` — 3 tests
| Test | Lo que valida |
|---|---|
| `completa el ciclo de vida completo de un despacho vía eventos` | Crea pedido → ruta → inicia ruta → llega a parada → completa entrega → regresa. Todo el flujo termina con estado `entregado` |
| `registra incidencia en un despacho` | Entrega fallida → estado del despacho es `incidencia` |
| `registra devolucion en un despacho` | Devolución de producto → estado del despacho es `incidencia` |

### `Logistica/HojaRutaTest.php` — 7 tests
| Test | Lo que valida |
|---|---|
| `lista hojas de ruta` | GET `/api/hojas-ruta` → 200 |
| `crea una hoja de ruta con pedidos` | POST con pedidos → 201, respuesta incluye `detalles` |
| `inicia una ruta planificada` | POST `/api/hojas-ruta/{id}/iniciar` → estado `en_progreso` |
| `rechaza iniciar ruta no planificada` | POST sobre ruta no-planificada → 422 |
| `obtiene pedidos pendientes` | GET `/api/logistica/pedidos-pendientes` → 200 |
| `obtiene repartidores disponibles` | GET `/api/logistica/repartidores-disponibles` → 200 |
| `obtiene rutas activas` | GET `/api/logistica/rutas-activas` → 200 |

### `Caja/CajaDiariaTest.php` — 6 tests
| Test | Lo que valida |
|---|---|
| `abre una caja` | POST `/api/caja/abrir` → 201 |
| `registra un ingreso en caja abierta` | POST `/api/caja/ingreso` → 200 |
| `registra un gasto en caja abierta` | POST `/api/caja/gasto` → 200 |
| `cierra una caja` | PUT `/api/caja/cerrar/{id}` → estado `cerrada` |
| `consulta caja del dia` | GET `/api/caja/dia` → 200 |
| `obtiene resumen diario` | GET `/api/caja/resumen` → 200 |

### `Reporte/ReportesTest.php` — 7 tests
| Test | Lo que valida |
|---|---|
| `obtiene resumen del sistema` | GET `/api/reportes/resumen` → 200 con `success`/`data` |
| `obtiene pedidos por estado` | GET `/api/reportes/pedidos-por-estado` → 200 |
| `obtiene despachos por estado` | GET `/api/reportes/despachos-por-estado` → 200 |
| `obtiene pedidos por dia` | GET `/api/reportes/pedidos-por-dia` → 200 |
| `obtiene repartidores por estado` | GET `/api/reportes/repartidores-por-estado` → 200 |
| `obtiene vehiculos por estado` | GET `/api/reportes/vehiculos-por-estado` → 200 |
| `obtiene incidencias por tipo` | GET `/api/reportes/incidencias-por-tipo` → 200 |

### `ExampleTest.php` — 1 test
| Test | Lo que valida |
|---|---|
| `the application returns a successful response` | GET `/` → 200 |

---

## 4. Tests Unit (por módulo)

### Auth — `tests/Unit/Auth/`
#### Models/UsuarioTest.php — 10 tests
| Test | Lo que valida |
|---|---|
| `table name` | `getTable()` → `'usuarios'` |
| `primary key` | `getKeyName()` → `'id_usuario'` |
| `fillable` | `getFillable()` contiene `nombre`, `apellido`, `email`, `password_hash`, `estado` |
| `hidden attributes` | `getHidden()` contiene `password_hash`, `remember_token` |
| `get auth password returns password hash` | `getAuthPassword()` === `password_hash` |
| `has api tokens trait` | Usa `Laravel\Sanctum\HasApiTokens` |
| `has roles trait` | Usa `Spatie\Permission\Traits\HasRoles` |
| `has one repartidor` | Relación HasOne con `Repartidor` |
| `has many pedidos` | Relación HasMany con `Pedido` |
| `create and assign role` | Factory + `assignRole('Administrador')` + `hasRole('Administrador')` → true |

#### Requests/LoginRequestTest.php — 4 tests
| Test | Lo que valida |
|---|---|
| `authorize devuelve true` | `authorize()` → `true` |
| `rules contiene campos requeridos` | `rules()` tiene claves `email` y `password` |
| `email es required y email` | Regla de email contiene `required` y `email` |
| `password es required string y min 6` | Regla de password contiene `required`, `string`, `min:6` |

#### Requests/RegisterRequestTest.php — 6 tests
| Test | Lo que valida |
|---|---|
| `authorize devuelve true` | `authorize()` → `true` |
| `rules contiene todos los campos` | `rules()` tiene `nombre`, `apellido`, `email`, `password`, `telefono` |
| `nombre es required string max 100` | Regla contiene `required`, `string`, `max:100` |
| `email tiene unique` | Regla contiene `unique:usuarios,email` |
| `password es required string min 6` | Regla contiene `required`, `min:6` |
| `telefono es nullable` | Regla contiene `nullable` |

#### Requests/UsuarioStoreRequestTest.php — 6 tests
| Test | Lo que valida |
|---|---|
| `authorize devuelve true` | `authorize()` → `true` |
| `rules contiene todos los campos` | `rules()` tiene `nombre`, `apellido`, `email`, `password`, `telefono`, `id_estado_usuario`, `roles` |
| `telefono es required no nullable` | Regla contiene `required` y no `nullable` |
| `id estado usuario requiere integer y exists` | Regla contiene `exists:estados_usuario,id_estado_usuario` |
| `roles es array opcional` | Regla contiene `sometimes` y `array` |
| `roles items requieren integer y exists` | Regla de `roles.*` contiene `exists:roles,id_rol` |

---

### Catalogo — `tests/Unit/Catalogo/`
#### Models/FarmaciaTest.php — 5 tests
| Test | Lo que valida |
|---|---|
| `table name` | `'farmacias'` |
| `primary key` | `'id_farmacia'` |
| `fillable` | `nombre`, `direccion`, `latitud`, `longitud` |
| `has many contactos` | HasMany `ContactoFarmacia` |
| `has many pedidos` | HasMany `Pedido` |

#### Models/ContactoFarmaciaTest.php — 4 tests
| Test | Lo que valida |
|---|---|
| `table name` | `'contactos_farmacia'` |
| `primary key` | `'id_contacto'` |
| `fillable` | `id_farmacia`, `nombre_contacto`, `telefono` |
| `belongs to farmacia` | BelongsTo `Farmacia` por `id_farmacia` |

#### Models/ProductoTest.php — 4 tests
| Test | Lo que valida |
|---|---|
| `table name` | `'productos'` |
| `primary key` | `'id_producto'` |
| `fillable` | `nombre`, `precio_unitario`, `unidad_medida` |
| `has many detalle pedidos` | HasMany `DetallePedido` |

---

### Pedido — `tests/Unit/Pedido/`
#### Models/PedidoTest.php — 8 tests
| Test | Lo que valida |
|---|---|
| `table name` | `'pedidos'` |
| `primary key` | `'id_pedido'` |
| `fillable` | `id_farmacia`, `id_usuario`, `estado`, `total` |
| `belongs to farmacia` | BelongsTo `Farmacia` |
| `belongs to usuario` | BelongsTo `Usuario` |
| `has many detalles` | HasMany `DetallePedido` |
| `has one despacho` | HasOne `Despacho` |
| `has many hoja ruta detalles` | HasMany `HojaRutaDetalle` |

#### Models/DetallePedidoTest.php — 5 tests
| Test | Lo que valida |
|---|---|
| `table name` | `'detalle_pedido'` |
| `primary key` | `'id_detalle'` |
| `fillable` | `id_pedido`, `id_producto`, `cantidad_solicitada`, `precio_unitario`, `subtotal` |
| `belongs to pedido` | BelongsTo `Pedido` |
| `belongs to producto` | BelongsTo `Producto` |

#### Services/PedidoPdfExtractorTest.php — 7 tests
| Test | Lo que valida |
|---|---|
| `normalizar convierte a minusculas y quita acentos` | `normalizar('Á é Í ó Ú')` → solo contiene [a-z ] con al menos `e` y `o`. `'Hello World'` → `'hello world'` |
| `esLineaNoProducto detecta encabezados` | Líneas como `FECHA:`, `TOTAL BS`, `Cliente:`, `FORMA DE PAGO:`, `RECIBI CONFORME` → true |
| `extraerCantidad detecta numero antes de unidad` | `'2 UD Paracetamol'` → 2.0, `'3 CAJA Ibuprofeno'` → 3.0 |
| `extraerMontos extrae valores decimales` | `'2 UD Paracetamol 5.00 10.00'` → `[5.0, 10.0]` |
| `deduplicar acumula cantidades del mismo producto` | 2 filas con id_producto=1 se fusionan en 1 con cantidad 5 |
| `buscarProducto encuentra coincidencia exacta` | Crea producto, busca en línea → encuentra |
| `buscarProducto retorna null sin coincidencia` | Crea producto, busca línea sin relación → null |

---

### Logistica — `tests/Unit/Logistica/`
#### Models/RepartidorTest.php — 9 tests
| Test | Lo que valida |
|---|---|
| `table name` | `'repartidores'` |
| `primary key` | `'id_repartidor'` |
| `fillable` | `id_usuario`, `ci`, `categoria_licencia`, `estado` |
| `belongs to usuario` | BelongsTo `Usuario` por `id_usuario` |
| `has many asignaciones vehiculo` | HasMany `AsignacionVehiculoRepartidor` |
| `has one asignacion activa` | HasOne `AsignacionVehiculoRepartidor` (scope) |
| `has many hojas ruta` | HasMany `HojaRuta` |
| `has many cajas` | HasMany `CajaRepartidor` |
| `asignacion activa scoped to null fecha devolucion` | Crea 2 asignaciones → `asignacionActiva` retorna solo la activa |

#### Models/VehiculoTest.php — 5 tests
| Test | Lo que valida |
|---|---|
| `table name` | `'vehiculos'` |
| `primary key` | `'id_vehiculo'` |
| `fillable` | `placa`, `marca`, `modelo`, `estado` |
| `has many asignaciones` | HasMany `AsignacionVehiculoRepartidor` |
| `has many hojas ruta` | HasMany `HojaRuta` |

#### Models/HojaRutaTest.php — 7 tests
| Test | Lo que valida |
|---|---|
| `table name` | `'hojas_ruta'` |
| `primary key` | `'id_hoja_ruta'` |
| `fillable` | `fecha`, `id_repartidor`, `id_vehiculo`, `estado_ruta` |
| `belongs to repartidor` | BelongsTo `Repartidor` |
| `belongs to vehiculo` | BelongsTo `Vehiculo` |
| `has many detalles ordenados` | HasMany `HojaRutaDetalle` |
| `has one caja` | HasOne `CajaRepartidor` |

#### Models/HojaRutaDetalleTest.php — 6 tests
| Test | Lo que valida |
|---|---|
| `table name` | `'hoja_ruta_detalles'` |
| `primary key` | `'id_detalle'` |
| `fillable` | `id_hoja_ruta`, `id_pedido`, `orden_visita` |
| `belongs to hoja ruta` | BelongsTo `HojaRuta` |
| `belongs to pedido` | BelongsTo `Pedido` |
| `has many despachos` | HasMany `Despacho` |

#### Models/AsignacionVehiculoRepartidorTest.php — 6 tests
| Test | Lo que valida |
|---|---|
| `table name` | `'asignaciones_vehiculo_repartidor'` |
| `primary key` | `'id_asignacion'` |
| `fillable` | `id_repartidor`, `id_vehiculo`, `fecha_asignacion`, `fecha_devolucion` |
| `casts` | `fecha_asignacion` y `fecha_devolucion` casteados a `datetime` |
| `belongs to repartidor` | BelongsTo `Repartidor` por `id_repartidor` |
| `belongs to vehiculo` | BelongsTo `Vehiculo` por `id_vehiculo` |

#### Events/RutaIniciadaTest.php — 2 tests
| Test | Lo que valida |
|---|---|
| `constructor asigna propiedades` | `$event->hojaRuta`, `->pedidoIds`, `->idVehiculo` se asignan correctamente |
| `usa traits correctos` | Usa `Dispatchable`, `InteractsWithSockets`, `SerializesModels` |

#### Events/RepartidorRegresaTest.php — 2 tests
| Test | Lo que valida |
|---|---|
| `constructor asigna propiedades` | `$event->hojaRuta`, `->idRepartidor`, `->idVehiculo` se asignan correctamente |
| `usa traits correctos` | Usa `Dispatchable`, `SerializesModels` |

#### Listeners/ActualizarEstadoPedidosListenerTest.php — 4 tests
| Test | Lo que valida |
|---|---|
| `on ruta iniciada aprueba pedidos pendientes` | `RutaIniciada` → pedidos pendientes pasan a `'aprobado'` |
| `on entrega completada marca entregado` | `EntregaCompletada` → pedido pasa a `'entregado'` |
| `on entrega fallida revierte a pendiente` | `EntregaFallida` → pedido vuelve a `'pendiente'` |
| `on devolucion producto marca entregado` | `DevolucionProducto` → pedido pasa a `'entregado'` |

#### Listeners/ActualizarEstadoRepartidorListenerTest.php — 2 tests
| Test | Lo que valida |
|---|---|
| `on ruta iniciada cambia a en ruta` | `RutaIniciada` → repartidor.`estado` = `'en_ruta'` |
| `on repartidor regresa cambia a activo` | `RepartidorRegresa` → repartidor.`estado` = `'activo'` |

#### Listeners/ActualizarEstadoVehiculoListenerTest.php — 2 tests
| Test | Lo que valida |
|---|---|
| `on ruta iniciada cambia a en ruta` | `RutaIniciada` → vehiculo.`estado` = `'en_ruta'` |
| `on repartidor regresa cambia a disponible` | `RepartidorRegresa` → vehiculo.`estado` = `'disponible'` |

#### Listeners/RegistrarLlegadaParadaListenerTest.php — 4 tests
| Test | Lo que valida |
|---|---|
| `handle entrega registra hora llegada si no existe` | `EntregaCompletada` → `hora_llegada` se setea si era null |
| `handle fallida registra hora llegada si no existe` | `EntregaFallida` → `hora_llegada` se setea si era null |
| `handle devolucion registra hora llegada si no existe` | `DevolucionProducto` → `hora_llegada` se setea si era null |
| `no sobrescribe hora llegada existente` | Si ya tiene valor, no se modifica |

#### Services/AsignacionVehiculoServiceTest.php — 4 tests
| Test | Lo que valida |
|---|---|
| `asignar crea asignacion` | `asignar()` crea registro en BD sin `fecha_devolucion` |
| `asignar cierra asignacion anterior` | Asignar otro vehículo → anterior obtiene `fecha_devolucion` |
| `liberar cierra asignacion activa` | `liberar()` cierra asignación activa |
| `liberar no falla sin asignacion activa` | `liberar()` sin asignación → no lanza excepción |

---

### Despacho — `tests/Unit/Despacho/`
#### Models/DespachoTest.php — 7 tests
| Test | Lo que valida |
|---|---|
| `table name` | `'despachos'` |
| `primary key` | `'id_despacho'` |
| `fillable` | `id_pedido`, `estado_despacho`, `monto_recibido` |
| `belongs to pedido` | BelongsTo `Pedido` |
| `belongs to hoja ruta detalle` | BelongsTo `HojaRutaDetalle` |
| `has many evidencias` | HasMany `EvidenciaEntrega` |
| `has many incidencias` | HasMany `Incidencia` |

#### Models/EvidenciaEntregaTest.php — 4 tests
| Test | Lo que valida |
|---|---|
| `table name` | `'evidencias_entrega'` |
| `primary key` | `'id_evidencia'` |
| `fillable` | `id_despacho`, `tipo_evidencia`, `archivo` |
| `belongs to despacho` | BelongsTo `Despacho` |

#### Models/IncidenciaTest.php — 4 tests
| Test | Lo que valida |
|---|---|
| `table name` | `'incidencias'` |
| `primary key` | `'id_incidencia'` |
| `fillable` | `id_despacho`, `tipo_incidencia`, `descripcion` |
| `belongs to despacho` | BelongsTo `Despacho` por `id_despacho` |

#### Events/EntregaCompletadaTest.php — 2 tests
| Test | Lo que valida |
|---|---|
| `constructor asigna propiedades` | `$event->detalle`, `->idDespacho`, `->idPedido` se asignan correctamente |
| `usa traits correctos` | Usa `Dispatchable`, `SerializesModels` |

#### Events/EntregaFallidaTest.php — 3 tests
| Test | Lo que valida |
|---|---|
| `constructor asigna propiedades` | `$event->detalle`, `->idDespacho`, `->idPedido`, `->motivo` se asignan |
| `motivo puede ser vacio` | `new EntregaFallida(..., '')` → `$event->motivo === ''` |
| `usa traits correctos` | Usa `Dispatchable`, `SerializesModels` |

#### Events/DevolucionProductoTest.php — 2 tests
| Test | Lo que valida |
|---|---|
| `constructor asigna propiedades` | `$event->detalle`, `->idDespacho`, `->idPedido` se asignan |
| `usa traits correctos` | Usa `Dispatchable`, `SerializesModels` |

#### Listeners/ActualizarEstadoDespachoListenerTest.php — 3 tests
| Test | Lo que valida |
|---|---|
| `on entrega completada marca entregado` | `EntregaCompletada` → despacho.`estado_despacho` = `'entregado'` |
| `on entrega fallida marca incidencia` | `EntregaFallida` → despacho.`estado_despacho` = `'incidencia'` |
| `on devolucion producto marca incidencia` | `DevolucionProducto` → despacho.`estado_despacho` = `'incidencia'` |

---

### Caja — `tests/Unit/Caja/`
#### Models/CajaRepartidorTest.php — 6 tests
| Test | Lo que valida |
|---|---|
| `table name` | `'caja_repartidor'` |
| `primary key` | `'id_caja'` |
| `fillable` | `id_hoja_ruta`, `id_repartidor`, `monto_inicial`, `estado_caja` |
| `belongs to hoja ruta` | BelongsTo `HojaRuta` |
| `belongs to repartidor` | BelongsTo `Repartidor` |
| `has many gastos` | HasMany `GastoOperativo` |

#### Models/GastoOperativoTest.php — 4 tests
| Test | Lo que valida |
|---|---|
| `table name` | `'gastos_operativos'` |
| `primary key` | `'id_gasto'` |
| `fillable` | `id_caja`, `tipo_gasto`, `monto`, `fecha` |
| `belongs to caja` | BelongsTo `CajaRepartidor` |

---

### Global — `tests/Unit/Global/`
#### Config/ConfigTest.php — 6 tests
| Test | Lo que valida |
|---|---|
| `cors config tiene estructura correcta` | `config('cors.paths')` contiene `api/*` y `sanctum/csrf-cookie` |
| `auth config tiene estructura correcta` | Guard `web`, passwords `users`, driver `session`, model `Usuario` |
| `scribe config tiene estructura correcta` | Type `static`, base_url `http://127.0.0.1:8000` |
| `permission config tiene estructura correcta` | Teams=false, wildcard=false |
| `database config tiene conexion mysql` | Charset `utf8mb4`, collation `utf8mb4_unicode_ci` |
| `filesystems config tiene disks local y public` | Default `local`, disks `local` y `public` |

#### Providers/EventServiceProviderTest.php — 2 tests
| Test | Lo que valida |
|---|---|
| `tiene evento Registered en listen` | `$listen[Registered::class]` contiene `SendEmailVerificationNotification` |
| `registra subscribers en boot` | `boot()` registra los 5 eventos de dominio como subscribers |

---

### Raíz — `tests/Unit/`
#### `ArchTest.php` — 1 test
| Test | Lo que valida |
|---|---|
| `arch no usa debuggers en codigo fuente` | Ningún archivo en `app/` contiene `dd()`, `dump()`, `var_dump()`, `print_r()`, `exit()` |

#### `ExampleTest.php` — 1 test
| Test | Lo que valida |
|---|---|
| `true is true` | `expect(true)->toBeTrue()` |

---

## 5. Cómo ejecutar

```bash
# Todos los tests
./vendor/bin/pest

# Solo Feature tests (HTTP)
./vendor/bin/pest --testsuite=Feature

# Solo Unit tests
./vendor/bin/pest --testsuite=Unit

# Tests de un módulo específico (por grupo)
./vendor/bin/pest --group=pedido
./vendor/bin/pest --group=auth
./vendor/bin/pest --group=despachos
./vendor/bin/pest --group=catalogos
./vendor/bin/pest --group=logistica

# Un archivo específico (rutas actualizadas)
./vendor/bin/pest tests/Unit/Auth/Models/UsuarioTest.php
./vendor/bin/pest tests/Unit/Logistica/Services/AsignacionVehiculoServiceTest.php
./vendor/bin/pest tests/Unit/Global/Config/ConfigTest.php
./vendor/bin/pest tests/Feature/Despacho/ValidarEvidenciaEntregaTest.php
```
