# Pw3c Distribuidora — Sistema de Gestión de Distribución

## Descripción General

Sistema integral para la gestión de una distribuidora farmacéutica. Cubre todo el ciclo logístico: gestión de pedidos de farmacias, planificación de hojas de ruta, seguimiento GPS en tiempo real de repartidores, administración de flota vehicular, control de caja diaria (arqueo completo), gestión de evidencias de entrega y reportes. Incluye una app Android para repartidores que se comunica directamente con la API REST.

---

## Arquitectura del Sistema

```
[Frontend Web SPA] ──HTTP/JSON──> [API Laravel] ──Eloquent ORM──> [MySQL]
   (public/, Vanilla JS)            (PHP 8+, Laravel 9)
        │                                │
   [Leaflet 1.9.4]                [Sanctum Auth]
   [Firebase SDK]              [spatie/laravel-permission]
        │                        [Eventos de dominio]
        │                                │
        │                         [FirebaseSyncService]
        │                                │
        └──────────┬─────────────────────┘
                   │
          [Firebase Realtime Database]
            /viajes_activos/{uid}   ← backend (estado de ruta)
            /usuarios/{uid}/ubicacion ← app Android (GPS)
                   │
          [App Android (Kotlin)]
     Login + API Retrofit (/api/mobile)
     (GPS, evidencias, caja, geocercas)
```

---

## BACKEND (Laravel 9 + PHP 8+)

### Stack Tecnológico

| Componente | Tecnología | Versión |
|------------|-----------|---------|
| Framework | Laravel | 9.52.x (`^9.0`) |
| Lenguaje | PHP | ^8.0 |
| Base de datos | MySQL | 8+ |
| Autenticación API | Laravel Sanctum | ^2.14 |
| Autorización (RBAC) | spatie/laravel-permission | * |
| Generación PDF | barryvdh/laravel-dompdf | 3.1 |
| HTTP Client | Guzzle / Laravel Http | ^7.2 |
| CORS | fruitcake/laravel-cors | ^2.0.5 |
| Parseo PDF | smalot/pdfparser | ^2.12 *(instalado, sin uso actual)* |
| Tests | Pest / PHPUnit | ^1.0 / ^9.5.10 |
| Análisis estático | Larastan (PHPStan) | ^2.0 |
| Documentación API | Scribe | ^5.0 |
| Contenedor dev | Laravel Sail (Docker) | ^1.0.1 |
| Utilidades | Laravel Tinker | ^2.7 |

Scripts de composer: `composer test` (Pest), `composer analyse` (PHPStan), `composer docs` (genera Scribe en `public/docs`), `composer check` (ambos).

> **Nota:** no se usa Laravel Mix ni bundler para el frontend — `public/` se sirve directamente.

### Estructura del Backend

```
app/
├── Events/                          # 4 eventos de dominio
│   ├── RutaIniciada.php
│   ├── EntregaCompletada.php
│   ├── EntregaFallida.php
│   └── RepartidorRegresa.php
├── Http/
│   ├── Controllers/
│   │   ├── ApiController.php                    # Controller base con respuestas JSON
│   │   ├── AuthController.php                   # Login, Register, Logout, User
│   │   ├── Controller.php                       # Controller base de Laravel
│   │   ├── Caja/
│   │   │   └── CajaController.php               # Apertura, ingresos, gastos, vincular hoja, cierre, resumen
│   │   ├── Despacho/
│   │   │   ├── DespachoController.php           # CRUD despachos + cambiar-estado
│   │   │   └── EvidenciaController.php          # CRUD evidencias de entrega
│   │   ├── Farmacia/
│   │   │   ├── FarmaciaController.php           # CRUD farmacias (con lat/lng/geocerca)
│   │   │   └── ContactoFarmaciaController.php   # CRUD contactos por farmacia
│   │   ├── Logistica/
│   │   │   └── HojaRutaController.php           # CRUD hojas de ruta + ejecución + PDF (16 métodos)
│   │   ├── Mobile/
│   │   │   ├── RutaController.php               # API de la app: ruta activa, llegada, entrega, evidencias
│   │   │   └── CajaController.php               # API de la app: caja actual, gastos, cierre
│   │   ├── Pedido/
│   │   │   └── PedidoController.php             # CRUD pedidos + Kanban + subir/descargar archivo
│   │   ├── Repartidor/
│   │   │   ├── RepartidorController.php         # CRUD repartidores + cambiar-estado
│   │   │   └── RepartidorVehiculoController.php # Asignación repartidor ↔ vehículo
│   │   ├── Reportes/
│   │   │   └── ReporteController.php            # Dashboard y estadísticas (6 reportes)
│   │   ├── Seguridad/
│   │   │   ├── UsuarioController.php            # CRUD usuarios + bloquear
│   │   │   └── RolesController.php              # CRUD roles + asignar permisos
│   │   └── Vehiculo/
│   │       └── VehiculoController.php           # CRUD vehículos + cambiar-estado
│   ├── Middleware/
│   │   ├── ForceJsonResponse.php     # Fuerza respuesta JSON (alias: json.forcing, grupo mobile)
│   │   └── ... (estándar de Laravel: Authenticate, Cors, etc.)
│   └── Requests/
│       ├── LoginRequest.php
│       ├── RegisterRequest.php
│       └── Seguridad/UsuarioStoreRequest.php
├── Listeners/
│   ├── ActualizarEstadoRepartidorListener.php   # Suscrito a eventos de ruta
│   ├── ActualizarEstadoVehiculoListener.php     # Suscrito a eventos de ruta
│   └── SyncRutaToFirebaseListener.php           # Sincroniza estado de viaje a Firebase RTDB
├── Models/                           # 16 modelos (planos, sin subcarpetas)
│   ├── AsignacionVehiculoRepartidor.php
│   ├── CajaIngreso.php              # (nuevo) ingresos separados de caja
│   ├── CajaRepartidor.php
│   ├── ContactoFarmacia.php
│   ├── DetallePedido.php
│   ├── Despacho.php
│   ├── EvidenciaEntrega.php
│   ├── Farmacia.php
│   ├── GastoOperativo.php
│   ├── HojaRuta.php                 # (nuevo) reemplaza rutas/control_rutas/planificación
│   ├── HojaRutaDetalle.php          # (nuevo) paradas/pedidos de la hoja de ruta
│   ├── Pedido.php
│   ├── Producto.php
│   ├── Repartidor.php
│   ├── Usuario.php
│   └── Vehiculo.php
├── Services/
│   ├── FirebaseSyncService.php      # (nuevo) escritura/lectura en Firebase RTDB
│   └── Logistica/AsignacionVehiculoService.php
└── Providers/
    ├── AppServiceProvider.php
    ├── AuthServiceProvider.php
    ├── BroadcastServiceProvider.php  # (existe pero deshabilitado en config/app.php)
    ├── EventServiceProvider.php      # Suscribe los 3 listeners
    └── RouteServiceProvider.php
```

**No existen (eliminados en el refactor):** `CatalogoController`, `IncidenciaController`, `ProductoController`, `DetallePedidoController`, `RutaController`, `ControlRutaController`, `CentroLogisticoController`, `PedidoPdfExtractor`, modelos `Estado*`/`Historial*`/`Incidencia`, eventos `DevolucionProducto`.

### Base de Datos

**Motor:** MySQL 8+ con cotejamiento `utf8mb4_unicode_ci`

**25 migraciones** organizadas en dominios:

#### Seguridad y Sistema (Sanctum + spatie/laravel-permission)
- `personal_access_tokens` — Tokens de API (Sanctum)
- `permission_tables` — Tablas spatie: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`
- `drop_old_tables` — Elimina el ER anterior (8 tablas `estados_*`, 5 `historial_*`, `auditorias`, `sesiones_usuario`, `tablas_sistema`, `password_resets`, etc.)

#### Catálogos Base
- `usuarios` — Usuarios (id_usuario PK, nombre, apellido, email unique, password_hash, telefono, estado, timestamps)
- `farmacias` — Clientes (id_farmacia PK, nombre, direccion, telefono, email, **latitud**, **longitud**, **geocerca** (radio en metros), timestamps)
- `contactos_farmacia` — Contactos por farmacia (id_contacto PK, id_farmacia FK, nombre, cargo, telefono, email)

#### Logística
- `repartidores` — Personal (id_repartidor PK, id_usuario FK, ci, licencia, estado, **firebase_uid** unique — vincula con la app móvil y Firebase)
- `vehiculos` — Flota (id_vehiculo PK, placa unique, modelo, capacidad, estado)
- `asignaciones_vehiculo_repartidor` — Asignaciones vehículo ↔ repartidor
- `hojas_ruta` — Hoja de ruta (id_hoja_ruta PK, id_repartidor FK, id_vehiculo FK, fecha, hora_salida, hora_llegada, estado, totales)
- `hoja_ruta_detalles` — Paradas/pedidos de la hoja (id_detalle PK, id_hoja_ruta FK, id_pedido FK, orden_visita, estado parada, montos)

#### Pedidos
- `productos` — Catálogo (id_producto PK, nombre, precio_unitario, unidad_medida, estado)
- `pedidos` — Pedidos (id_pedido PK, id_farmacia FK, id_usuario FK, estado, fecha_pedido, observaciones, subtotal, descuento, total, archivo_pedido, timestamps)
- `detalle_pedido` — Líneas de pedido (id_detalle PK, id_pedido FK, id_producto FK, cantidad_solicitada, cantidad_entregada, precio_unitario, subtotal, estado)

#### Despachos y Entregas
- `despachos` — Despachos (id_despacho PK, id_pedido FK, id_hoja_ruta FK, fecha_hora, estado, metodo_pago)
- `evidencias_entrega` — Evidencias (id_evidencia PK, id_despacho FK, tipo, **archivo** (ruta local en Storage), timestamps)
- `incidencias` — *(tabla creada pero huérfana: sin modelo ni controller)*

#### Caja Diaria
- `caja_repartidor` — Caja por jornada (id_caja PK, id_hoja_ruta FK nullable, id_repartidor FK, fecha, monto_inicial, ingresos, gastos, monto_final, estado [abierta/cerrada/auditada])
- `caja_ingresos` — (nuevo) Ingresos registrados por separado
- `gastos_operativos` — Gastos (id_gasto PK, id_caja FK, tipo_gasto, descripcion, monto, comprobante, fecha)

### API REST — 99 Endpoints

Todas las rutas bajo `/api/` en `routes/api.php`. Autenticación con `auth:sanctum`; permisos vía middleware spatie `permission:Modulo-accion` y `role_or_permission:`. El grupo `/api/mobile` usa middleware `json.forcing` y se identifica por `firebaseUid`.

#### Autenticación
```
POST /api/auth/login      → Login (público): token + usuario + permisos
POST /api/auth/register   → Registro de usuario (público)
POST /api/auth/logout     → Revocar token actual
GET  /api/auth/user       → Usuario autenticado con permisos
```

#### Usuarios
```
GET    /api/usuarios                     → Listar (Usuarios-listar)
POST   /api/usuarios                     → Crear (Usuarios-crear)
GET    /api/usuarios/{id}                → Ver
PUT    /api/usuarios/{id}                → Actualizar (Usuarios-editar)
DELETE /api/usuarios/{id}                → Eliminar (Usuarios-eliminar)
PUT    /api/usuarios/{id}/bloquear       → Bloquear usuario
```

#### Roles y Permisos
```
GET|POST   /api/roles                    → Listar/Crear roles
GET|PUT|DELETE /api/roles/{id}           → Ver/Actualizar/Eliminar rol
GET|POST   /api/roles/{id}/permisos      → Obtener/Asignar permisos del rol
```

#### Farmacias
```
GET|POST|PUT|DELETE /api/farmacias                   → CRUD farmacias
GET|POST|PUT|DELETE /api/farmacias/{id}/contactos    → CRUD contactos (y anidados)
```

#### Pedidos
```
GET|POST|PUT|DELETE /api/pedidos                     → CRUD pedidos
POST /api/pedidos/{id}/cambiar-estado                → Cambiar estado
POST /api/pedidos/{id}/subir-archivo                 → Subir PDF adjunto
GET  /api/pedidos/{id}/descargar-archivo             → Descargar PDF adjunto
```

#### Repartidores
```
GET|POST|PUT|DELETE /api/repartidores                → CRUD repartidores
POST /api/repartidores/{id}/cambiar-estado           → Cambiar estado
GET|POST|DELETE /api/repartidores/{id}/asignacion-vehiculo → Asignación vehículo
```

#### Vehículos
```
GET|POST|PUT|DELETE /api/vehiculos                   → CRUD vehículos
POST /api/vehiculos/{id}/cambiar-estado              → Cambiar estado
```

#### Logística (informativas)
```
GET /api/logistica/pedidos-pendientes                → Pedidos pendientes de asignar
GET /api/logistica/repartidores-disponibles          → Repartidores disponibles
GET /api/logistica/rutas-activas                     → Hojas de ruta activas (tracking)
GET /api/logistica/hojas-ruta-disponibles            → Hojas de ruta disponibles
```

#### Hojas de Ruta
```
GET|POST|PUT|DELETE /api/hojas-ruta                  → CRUD hojas de ruta
GET    /api/hojas-ruta/{id}/pdf                      → Generar PDF de la hoja de ruta (dompdf)
POST   /api/hojas-ruta/{id}/iniciar                  → Iniciar ruta (dispara evento RutaIniciada + Firebase)
POST   /api/hojas-ruta/{id}/registrar-llegada        → Registrar llegada a parada
POST   /api/hojas-ruta/{id}/entrega-completada       → Marcar entrega exitosa
POST   /api/hojas-ruta/{id}/entrega-fallida          → Marcar entrega fallida
POST   /api/hojas-ruta/{id}/regreso-almacen          → Finalizar ruta (limpia viaje en Firebase)
```

#### Despachos y Evidencias
```
GET|POST|PUT|DELETE /api/despachos                   → CRUD despachos
POST /api/despachos/{id}/cambiar-estado              → Cambiar estado
GET|POST|PUT|DELETE /api/despachos/{id}/evidencias   → CRUD evidencias (archivos en Storage)
```

#### Reportes
```
GET /api/reportes/resumen                    → Conteos globales
GET /api/reportes/pedidos-por-estado         → Pedidos por estado
GET /api/reportes/despachos-por-estado       → Despachos por estado
GET /api/reportes/pedidos-por-dia            → Pedidos por día
GET /api/reportes/repartidores-por-estado    → Repartidores por estado
GET /api/reportes/vehiculos-por-estado       → Vehículos por estado
```

#### Caja Diaria
```
POST /api/caja/abrir                         → Abrir caja
POST /api/caja/ingreso                       → Registrar ingreso
POST /api/caja/gasto                         → Registrar gasto operativo
POST /api/caja/vincular-hoja/{id}            → Vincular caja a hoja de ruta
PUT  /api/caja/cerrar/{id}                   → Cerrar caja
GET  /api/caja/dia                           → Caja del día (con filtros de periodo)
GET  /api/caja/resumen                       → Resumen de caja
```

#### Móvil (app repartidor — `json.forcing`)
```
GET  /api/mobile/ruta-activa/{firebaseUid}   → Hoja de ruta activa con paradas y farmacias
GET  /api/mobile/pedido/{pedidoId}           → Detalle de pedido (productos a entregar)
POST /api/mobile/registrar-llegada           → Registrar llegada a parada (crea despacho)
POST /api/mobile/entrega                     → Registrar entrega (cobro, método de pago)
POST /api/mobile/entrega-fallida             → Registrar entrega fallida con motivo
POST /api/mobile/evidencias/{despachoId}     → Subir evidencia (multipart: foto/firma/sello)
POST /api/mobile/regreso-almacen             → Finalizar ruta desde la app
GET  /api/mobile/caja/{firebaseUid}          → Caja actual del repartidor
POST /api/mobile/caja/{firebaseUid}/gasto    → Registrar gasto con comprobante
POST /api/mobile/caja/{firebaseUid}/cerrar   → Cerrar caja con monto final
```

**Endpoints eliminados respecto a versiones anteriores:** `/catalogos`, `/productos`, `/pedidos/{id}/detalles`, `/pedidos/extraer-pdf`, `/despachos/{id}/incidencias`, `/controles-ruta`, planificación borrador/confirmar, devoluciones.

### Sistema de Autenticación y Permisos (RBAC — spatie/laravel-permission)

**Autenticación:**
- Tokens Laravel Sanctum (expiración configurable: 480 min)
- Modelo `Usuario` con `HasApiTokens`
- Login: email + password_hash (bcrypt) → token + usuario + permisos planos

**Autorización (spatie/laravel-permission):**
- Permisos con formato `Modulo-accion` (guard `web`), verificados por middleware `permission:` en cada ruta
- **13 módulos** (definidos en `database/seeders/CatalogoSeeder.php`):

| Módulo | Acciones |
|--------|----------|
| Dashboard | acceder |
| Usuarios | acceder, listar, crear, editar, eliminar, asignar-roles |
| Roles | acceder, listar, crear, editar, eliminar, asignar-permisos |
| Farmacias | acceder, listar, crear, editar, eliminar, gestionar-contactos |
| Pedidos | acceder, listar, crear, editar, eliminar, cambiar-estado |
| Repartidores | acceder, listar, crear, editar, eliminar, cambiar-estado |
| Vehículos | acceder, listar, crear, editar, eliminar, cambiar-estado |
| Rutas | acceder, listar, crear, editar, eliminar, gestionar-paradas |
| Control Rutas | acceder, listar, crear, editar, eliminar, registrar-llegada |
| Despachos | acceder, listar, crear, editar, eliminar, cambiar-estado, gestionar-evidencias |
| Reportes | acceder, listar |
| Caja Diaria | acceder, listar, crear, editar, cerrar |
| Documentos | acceder |

- **4 roles** (seeders `CatalogoSeeder` / `DemoDataSeeder`): **Administrador** (todos los permisos), **Supervisor** (lectura + gestión operativa), **Operador** (creación de pedidos/despachos), **Repartidor** (despachos, evidencias, control de rutas)

### Sistema de Eventos

4 eventos de dominio con 3 listeners suscritos en `EventServiceProvider`:

| Evento | Disparador | Efecto |
|--------|-----------|--------|
| `RutaIniciada` | Iniciar hoja de ruta | Repartidor → "En ruta", estado vehículo; `SyncRutaToFirebaseListener` escribe `viajes_activos/{uid}` en Firebase |
| `EntregaCompletada` | Marcar entrega exitosa | Despacho → "Entregado", actualiza pedido, sincroniza estado de parada en Firebase |
| `EntregaFallida` | Marcar entrega fallida | Despacho → "Fallido", sincroniza estado de parada |
| `RepartidorRegresa` | Finalizar ruta | Repartidor → "Disponible", vehículo liberado, limpia `viajes_activos/{uid}` |

### Servicios Especializados

**FirebaseSyncService** (`app/Services/FirebaseSyncService.php`) — *nuevo*:
- `iniciarViaje()` — PUT a `viajes_activos/{firebaseUid}.json` con: `id_hoja_ruta`, `estado`, `fecha_inicio` y array `farmacias[]` (id_pedido, orden_visita, nombre, dirección, lat/lng, `radio_geocerca` de la farmacia, estado pendiente)
- `actualizarEstadoParada()` — PATCH del estado de una parada específica
- `limpiarViaje()` / `limpiarViajePorRepartidor()` — DELETE del nodo al finalizar
- Usa `Http::` con timeout 10s; config en `config/firebase.php` (`FIREBASE_DATABASE_URL`, `FIREBASE_API_KEY`)

**AsignacionVehiculoService** (`app/Services/Logistica/AsignacionVehiculoService.php`):
- Asignación de vehículos a repartidores con validación de disponibilidad y estados

**HojaRutaController::pdf()**:
- Genera el PDF de la hoja de ruta con `barryvdh/laravel-dompdf` y la vista `resources/views/pdf/hoja-ruta.blade.php`

### Tests (Pest)

**37 archivos de test** (13 Feature + 24 Unit) sobre SQLite `:memory:`:
- Feature: Auth (Login, Logout, Register, User, UsuarioCRUD), CajaDiaria, FarmaciaCRUD, CambiarEstadoDespacho, ValidarEvidenciaEntrega, HojaRuta, RepartidorCRUD, VehiculoCRUD
- Unit: ArchTest (arquitectura), modelos, eventos, listeners, `AsignacionVehiculoServiceTest`, requests, config

### Seeders y Factories

- **9 seeders:** `DatabaseSeeder` → `CatalogoSeeder` (permisos y roles), `DemoDataSeeder`, `RepartidorSeeder`, `VehiculoSeeder`, `FarmaciaSeeder`, `PedidoSeeder`, `CajaSeeder`, `CajaTestSeeder`
- **13 factories:** Usuario, Farmacia, ContactoFarmacia, Repartidor, Vehiculo, AsignacionVehiculoRepartidor, Pedido, HojaRuta, HojaRutaDetalle, Despacho, EvidenciaEntrega, CajaRepartidor, GastoOperativo

---

## FRONTEND WEB (Vanilla JavaScript SPA — `public/`)

### Stack Tecnológico

| Componente | Tecnología |
|------------|-----------|
| Arquitectura | SPA servida desde `public/` (front controller Laravel `index.php`) |
| Lenguaje | JavaScript vanilla (sin frameworks) |
| Routing | Hash-based (`#pedidos`, `#logistica`, etc. + alias de pestañas) |
| Estilos | CSS personalizado (**3.250 líneas** totales) |
| Íconos | Font Awesome 6.5.1 |
| Tablas | jQuery 3.7.1 + DataTables 1.13.6 |
| **Mapas** | **Leaflet 1.9.4** (OpenStreetMap) — *ya no Mapbox GL JS* |
| Tiempo real | Firebase compat SDK 10.12.2 (Realtime Database) |
| Drag & Drop | SortableJS 1.15.0 |
| Modales/Alertas | SweetAlert2 11 |
| Build | Sin bundler — HTML/CSS/JS plano |
| Doc API | Scribe (generada en `public/docs/`) |

### Estructura del Frontend

```
public/
├── index.php                   # Front controller Laravel
├── index.html                  # Login / Register (308 líneas)
├── dashboard.html              # Shell principal: sidebar + #page-content
├── css/
│   ├── login.css               # 218 líneas — página de login
│   ├── style.css               # 2.099 líneas — sistema de diseño completo
│   └── logistica.css           # 933 líneas — Torre Logística
├── js/
│   ├── config.js               # 289 líneas — API_BASE_URL, apiFetch(), permisos, helpers
│   ├── auth.js                 # 151 líneas — login, register, logout, tokens
│   ├── error-handler.js        # 64 líneas — window.onerror + unhandledrejection
│   ├── layout.js               # 108 líneas — sidebar dinámico por permisos
│   ├── config.local(.example).js  # Config local (gitignored)
│   └── modules/                # 14 módulos — 6.067 líneas
│       ├── dashboard.js        # 155 — Centro de decisiones (métricas, alertas, rutas, caja)
│       ├── pedidos.js          # 786 — CRUD + Kanban + lista + archivos PDF
│       ├── logistica.js        # 223 — Router de la "Torre Logística" (4 pestañas)
│       ├── tracking.js         # 1.079 — Tracking GPS en tiempo real (el mayor)
│       ├── planificador.js     # 673 — Asignación con drag-and-drop + mapa + Firebase
│       ├── farmacias.js        # 563 — CRUD + contactos + mapa global con geocoding
│       ├── repartidores.js     # 373 — CRUD + estados + columna GPS Firebase + vehículo
│       ├── vehiculos.js        # 119 — CRUD vehículos
│       ├── despachos.js        # 196 — CRUD + módulo de evidencias
│       ├── usuarios.js         # 210 — CRUD usuarios
│       ├── roles.js            # 178 — CRUD roles + matriz de permisos
│       ├── caja.js             # 844 — Arqueo diario completo con periodo y paginación
│       ├── historial.js        # 612 — Rutas históricas + modal con evidencias + PDF
│       └── documentos.js       # 56 — Visor de manuales PDF
├── pages/                      # 11 páginas HTML (cargan sus módulos JS)
│   ├── logistica.html          # Torre Logística (4 pestañas, carga 5 módulos)
│   ├── pedidos.html            # Redirección a logistica.html#pedidos
│   ├── caja.html, despachos.html, farmacias.html, repartidores.html,
│   ├── vehiculos.html, usuarios.html, roles.html, historial.html,
│   └── documentos.html
├── storage/                    # Archivos subidos (evidencias, pedidos, gastos)
├── docs/                       # Documentación API generada por Scribe
└── vendor/scribe/              # Theme de Scribe
```

**Totales:** HTML 709 líneas (13 archivos) · CSS 3.250 · JS 6.681 · **≈ 10.640 líneas**

**Módulos eliminados:** `productos`, `rutas`, `controles-ruta`, `catalogos`, `reportes`, `gps`, y el OCR/extracción de PDF en `pedidos`.

### Características del Frontend por Módulo

#### Dashboard (`dashboard.js`)
- "Centro de decisiones": métricas (pedidos, entregas, farmacias, repartidores, vehículos)
- Alertas, rutas en curso con barra de progreso, resumen de caja
- Consume `/reportes/resumen`, `/logistica/rutas-activas`, `/caja/resumen`

#### Torre Logística (`logistica.js` + `pages/logistica.html`, 4 pestañas)
1. **Pedidos** — Pedidos pendientes con selección para despachar
2. **Planificación** — `planificador.js`: asignación de pedidos a repartidores con **drag-and-drop (SortableJS)**, selección de vehículo y ruta, mapa Leaflet con trazado de ruta, estado de conexión GPS del repartidor vía Firebase, botón "Iniciar ruta"
3. **Seguimiento** — `tracking.js`: mapa con posiciones GPS en vivo desde Firebase, **detección automática de llegada por proximidad** (`checkProximityToStops` → `autoRegistrarLlegada`), evidencias por parada, acciones (entregado / fallido / regreso al almacén), progreso por paradas
4. **Historial** — Rutas completadas con filtros, modal con detalle + evidencias + **descarga del PDF de la hoja de ruta**

#### Pedidos (`pedidos.js`, 786 líneas)
- CRUD con modal, vista **Kanban** (Pendiente → Preparación → En Ruta → Entregado → Cancelado) + vista lista
- Subida/descarga de archivo PDF adjunto
- Filtros por fecha, paginación, historial contextual por farmacia
- *(Eliminada la extracción automática de productos desde PDF/OCR)*

#### Tracking GPS (`tracking.js`, 1.079 líneas)
- Leaflet + Firebase Realtime Database en tiempo real
- Marcadores GPS con estela (`createGPSMarker`, `updateGPSTrail`)
- Detección de llegada por proximidad y registro automático
- Registro manual/automático de evidencias por parada
- Panel lateral con detalle de la ruta activa y acciones

#### Farmacias (`farmacias.js`, 563 líneas)
- CRUD + contactos, validación de email
- **Mapa global y modal de geolocalización** con reverse geocoding y marcadores resaltados
- Campos latitud, longitud y radio de geocerca

#### Repartidores (`repartidores.js`, 373 líneas)
- CRUD con CI, extensión, licencia; vinculación con usuario
- Asignar/liberar vehículo; cambio de estado
- **Columna de conexión GPS/Firebase** en vivo

#### Caja Diaria (`caja.js`, 844 líneas)
- Apertura con monto inicial; ingresos; gastos con comprobante
- **Vinculación de caja a hoja de ruta**
- Filtros por repartidor y periodo (día/mes/año) + paginación propia
- Cierre con cálculo automático de monto final y resumen

#### Despachos (`despachos.js`, 196 líneas)
- CRUD + cambiar estado
- **Módulo de evidencias** (subida de archivos, estados pendiente/entregado/incidencia)
- *(Eliminado el módulo de incidencias)*

#### Historial (`historial.js`, 612 líneas)
- Listado/filtrado/paginación de rutas históricas
- Modal con timeline, evidencias y descarga de PDF de hoja de ruta / pedido

#### Usuarios y Roles
- CRUD de usuarios, asignación de roles
- Matriz de permisos módulo × acción con checkboxes

#### Documentación
- Visor de PDF con manuales de usuario y técnico

### Sistema de Diseño

CSS personalizado sin frameworks (variables CSS, sidebar responsive, cards, modales, DataTables personalizado, badges por estado, Kanban, barras de progreso, toasts, mobile-first). Secciones nuevas: rediseño operativo, drawer lateral, timeline de historial, layout split de farmacias, tablas de caja.

### Flujo de Autenticación en el Frontend

1. `index.html` → `auth.js` → POST `/api/auth/login`
2. API devuelve token + usuario + permisos planos
3. `localStorage`: `auth_token`, `auth_user`, `auth_permissions`
4. `layout.js` renderiza sidebar filtrado por permisos
5. `apiFetch()` inyecta `Authorization: Bearer {token}`; en 401 redirige al login
6. Logout limpia localStorage

---

## APP MÓVIL (Android — Kotlin)

> **Ubicación:** repo Git separado en `..\Proyectos de visual studio\mapboxfirebase\` (ignorado por el `.gitignore` del backend).

### Stack Tecnológico

| Componente | Tecnología | Versión |
|------------|-----------|---------|
| Lenguaje | Kotlin | 2.0.21 |
| SDK de Mapas | Mapbox Maps SDK for Android | 10.15.0 |
| Tiempo real | Firebase Realtime Database (KTX) | 20.3.0 (BOM 34.10.0) |
| **API REST** | **Retrofit + Gson converter** | **2.9.0** *(nuevo)* |
| **HTTP** | **OkHttp + logging-interceptor** | **4.12.0** *(nuevo)* |
| UI | Material 3 + ConstraintLayout + **ViewBinding** | 1.13.0 / 2.2.1 |
| **Listas** | **RecyclerView + CardView** | 1.3.2 / 1.0.0 |
| Compilación | Android Gradle Plugin | 8.12.0 |
| Gradle Wrapper | Gradle | 8.13 |
| Min / Target SDK | Android API 24 / API 36 | - |
| Google Services | plugin | 4.4.4 |
| Pruebas | JUnit + Espresso | 4.13.2 / 3.7.0 |

**Kotlin total:** ≈ **2.672 líneas** (2.631 en producción).

### Estructura de la App

```
mapboxfirebase/
├── build.gradle.kts / settings.gradle.kts / gradle.properties
├── gradle/wrapper/                          # Gradle 8.13
└── app/
    ├── build.gradle.kts                     # Dependencias + viewBinding
    ├── google-services.json
    └── src/main/
        ├── AndroidManifest.xml
        ├── java/com/example/mapboxfirebase/
        │   ├── ui/
        │   │   ├── MainActivity.kt              # 280 — Mapa + drawer de paradas + geocercas
        │   │   ├── primercodigo.kt              # 303 — legacy, 100% comentado
        │   │   ├── login/LoginActivity.kt       # 90 — LAUNCHER: login + token (nuevo)
        │   │   ├── ruta/RutaActivity.kt         # 203 — Lista de paradas + finalizar ruta (nuevo)
        │   │   ├── parada/ParadaDetailActivity.kt # 288 — Productos, cobro, entrega/fallido (nuevo)
        │   │   ├── evidencia/EvidenciaActivity.kt # 257 — Foto/firma/sello + SignatureView (nuevo)
        │   │   └── caja/CajaActivity.kt         # 334 — Caja, egresos con comprobante, cierre (nuevo)
        │   ├── api/                            # (nuevo)
        │   │   ├── ApiClient.kt                # 46 — Retrofit singleton + interceptor Bearer
        │   │   ├── RutaApi.kt                  # 58 — 11 endpoints (auth + mobile)
        │   │   └── models/ApiModels.kt         # 208 — ~30 data classes DTO
        │   ├── auth/TokenManager.kt            # 41 — SharedPreferences de sesión (nuevo)
        │   ├── map/MapController.kt            # 118 — Mapa + círculos de geocerca + icono farmacia
        │   ├── location/LocationService.kt     # 78 — GPS cada 5s vía LocationEngine
        │   ├── fireabase/                      # (typo en carpeta)
        │   │   ├── FirebaseRepository.kt       # 56 — Escribe ubicación + onDisconnect
        │   │   ├── ViajeActivoRepository.kt    # 90 — Lee viajes_activos/{uid} (nuevo)
        │   │   └── GeofenceRepository.kt       # 42 — Huérfano (sin uso)
        │   ├── geofence/GeofenceManager.kt     # 62 — Haversine (circular) + Ray-Casting (polígono)
        │   ├── model/                          # Usuario, Ubicacion, Geocerca (+centro/radio)
        │   └── utils/                          # TimeUtils, DeviceUtils, SessionUtil (nuevo)
        └── res/
            ├── layout/                         # 9 layouts (login, ruta, parada, evidencia, caja, items)
            ├── drawable/ic_farmacia.xml        # (nuevo)
            └── xml/file_paths.xml              # FileProvider para cámara (nuevo)
```

### AndroidManifest.xml — Permisos y Componentes

```xml
<uses-permission android:name="android.permission.INTERNET"/>
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION"/>
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION"/>
<uses-permission android:name="android.permission.CAMERA"/>                        <!-- nuevo -->
<uses-permission android:name="android.permission.WRITE_EXTERNAL_STORAGE"
                 android:maxSdkVersion="28"/>                                     <!-- nuevo -->
<uses-feature android.hardware.camera" android:required="false"/>
<application android:usesCleartextTraffic="true"> ...                             <!-- API por http:// -->
```

**Activities (5):** `LoginActivity` (launcher) → `MainActivity` (mapa) → `RutaActivity` / `CajaActivity` → `ParadaDetailActivity` → `EvidenciaActivity`.
**Provider:** `FileProvider` para compartir fotos de cámara. **Sin services ni receivers.**

### Flujo Completo de la App

1. **`LoginActivity` (launcher)**
   - Email + contraseña → `POST /api/auth/login` → guarda `token`, `firebase_uid`, `id_repartidor`, `nombre` en SharedPreferences (`repartidor_session`) → `ApiClient.setToken()` → `MainActivity`
   - Auto-login si ya hay sesión; manejo de 401/403/422; logout vía `SessionUtil`

2. **`MainActivity`** (una vez con sesión)
   - Escucha **`viajes_activos/{firebaseUid}`** en Firebase (`ViajeActivoRepository`) → lista de farmacias con lat/lng/`radio_geocerca` (default 50 m o 20 m) y estado pendiente
   - GPS cada 5 segundos (`LocationService`) → escribe `usuarios/{id}/ubicacion` en Firebase + `onDisconnect().removeValue()`
   - **Geocercas circulares**: valida cada fix con `GeofenceManager.estaDentroGeocercaCircular()` (**Haversine**); al entrar a una parada **registra llegada automática** → `POST /api/mobile/registrar-llegada` (evita duplicados)
   - Mapa Mapbox con círculos de geocerca, iconos de farmacia y **DrawerLayout** con las paradas (Pendiente / Llegado / Entregado)
   - Botones: Ruta, Caja, Logout

3. **`RutaActivity`** — Lista de paradas con totales en Bs; botón "Finalizar ruta" (`POST mobile/regreso-almacen`) habilitado solo al completar todo

4. **`ParadaDetailActivity`** — Detalle del pedido (`GET mobile/pedido/{id}`), productos, spinner de método de pago (efectivo/QR/transferencia/crédito), monto recibido → `POST mobile/entrega`; fallido con motivo → `POST mobile/entrega-fallida`; acceso a evidencias

5. **`EvidenciaActivity`** — 3 tipos:
   - **foto/sello** → `ACTION_IMAGE_CAPTURE` + FileProvider → preview → JPEG
   - **firma** → `SignatureView` custom (canvas táctil → PNG)
   - Subida multipart → `POST /api/mobile/evidencias/{despachoId}`

6. **`CajaActivity`** — `GET mobile/caja/{uid}` (monto inicial, cobrado, gastos, efectivo esperado), registrar egreso con comprobante desde galería, cerrar caja (`POST mobile/caja/{uid}/cerrar`)

### Comunicación con la API (Retrofit)

`ApiClient.kt` — `BASE_URL = "http://192.168.0.102:8000/api/"` (IP local, ver Deuda Técnica), interceptor `Authorization: Bearer`, timeouts 30/30/60 s, logging BODY. **11 endpoints** en `RutaApi.kt` que coinciden 1:1 con el grupo `/api/mobile` + `/auth/login` del backend.

### Geocercas

- **Antes:** polígonos desde Firebase (`GeofenceRepository` + Ray-Casting) — hoy huérfanos
- **Ahora:** geocercas **circulares por farmacia** (`radio_geocerca` desde el backend → `viajes_activos`), validación **Haversine** con auto-registro de llegada al backend

### Configuración de Build

```kotlin
// raíz
id("com.android.application") version "8.12.0"
id("org.jetbrains.kotlin.android") version "2.0.21"
id("com.google.gms.google-services") version "4.4.4"

// app
implementation("com.mapbox.maps:android:10.15.0")          // repo Maven Mapbox + token
implementation(platform("com.google.firebase:firebase-bom:34.10.0"))
implementation("com.google.firebase:firebase-database-ktx:20.3.0")
implementation("com.squareup.retrofit2:retrofit:2.9.0")
implementation("com.squareup.okhttp3:logging-interceptor:4.12.0")
android { viewBinding { isEnabled = true } }
```

- `gradle.properties` → `MAPBOX_DOWNLOADS_TOKEN` (repo Maven); token público `pk.*` en `AndroidManifest.xml` + `mapbox_access_token.xml`
- `local.properties` y `FIREBASE_*` no se suben al repo

---

## FUNCIONALIDADES CLAVE DEL SISTEMA COMPLETO

1. **RBAC con spatie/laravel-permission**: 13 módulos × acciones, 4 roles (Administrador, Supervisor, Operador, Repartidor), permisos por middleware en cada endpoint
2. **Gestión de pedidos con Kanban**: flujo visual de estados con drag-and-drop, adjunto PDF y filtros por fecha
3. **Hojas de ruta**: planificación de paradas con drag-and-drop, inicio de ruta, seguimiento y **generación de PDF** (dompdf)
4. **Tracking GPS en tiempo real**: mapa Leaflet con posiciones vía Firebase, detección de llegada por proximidad y acciones en vivo
5. **API mobile dedicada**: 10 endpoints `/api/mobile` para la app del repartidor identificada por `firebase_uid`
6. **App Android completa**: login, ruta activa, entrega con cobro, evidencias (foto/firma/sello), caja del día y geocercas con llegada automática
7. **Sincronización backend ↔ Firebase**: `FirebaseSyncService` publica `viajes_activos/{uid}` al iniciar ruta y lo limpia al finalizar
8. **Evidencias de entrega**: subida de archivos a Storage desde web y desde la app (multipart)
9. **Caja diaria / arqueo completo**: apertura, ingresos, gastos con comprobante, vinculación a hoja de ruta, filtros por periodo y cierre con resumen
10. **Reportes dinámicos**: dashboard con métricas, conteos por estado y distribuciones temporales
11. **Arquitectura basada en eventos**: 4 eventos de dominio con listeners (estados + sync Firebase)
12. **Documentación API generada**: Scribe con OpenAPI (`composer docs` → `public/docs`)
13. **Calidad de código**: Pest (37 tests), Larastan/PHPStan y test de arquitectura (`composer check`)
14. **Manuales integrados**: visor de PDF de manuales de usuario y técnico

---

## REQUISITOS DEL ENTORNO

### Backend
- PHP ^8.0 con extensiones: bcmath, ctype, fileinfo, json, mbstring, openssl, pdo, pdo_mysql, tokenizer, xml
- Composer
- MySQL 8+
- (Opcional) Docker + Laravel Sail

### Frontend Web
- Se sirve desde `public/` vía el servidor web (Apache/Nginx) o `php artisan serve`
- Navegador moderno con soporte ES6+
- Conexión a internet (tiles de Leaflet/OSM, Firebase SDK, CDNs: Font Awesome, jQuery, DataTables, SweetAlert2, SortableJS)

### App Android
- Android Studio Hedgehog o superior, JDK 11+
- Android SDK API 24+
- Cuenta Mapbox (token de descargas + token público `pk.*`)
- Proyecto Firebase con Realtime Database habilitado
- Backend Laravel accesible desde el dispositivo (misma red para desarrollo)

---

## CONFIGURACIÓN CLAVE

### Backend (.env)
```env
APP_NAME=Pw3cDistribuidora
APP_URL=http://localhost
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pw3c_distribuidora
DB_USERNAME=root
DB_PASSWORD=
SANCTUM_TOKEN_EXPIRATION=480

# Firebase RTDB (FirebaseSyncService)
FIREBASE_DATABASE_URL="https://<proyecto>-default-rtdb.firebaseio.com"
FIREBASE_API_KEY="<TU_API_KEY>"
```

### App Android (gradle.properties)
```properties
MAPBOX_DOWNLOADS_TOKEN="<TU_MAPBOX_TOKEN>"
```

### Estructura Firebase Realtime Database

```
/viajes_activos/{firebaseUid}/      ← escritura por backend (FirebaseSyncService)
├── id_hoja_ruta: "12"
├── estado: "en_camino"
├── fecha_inicio: "2026-08-08 08:30"
└── farmacias/
    └── 0/ { id_pedido, id_detalle_hoja_ruta, orden_visita,
             nombre_farmacia, direccion, latitud, longitud,
             radio_geocerca, estado: "pendiente"|"llegado"|"entregado" }

/usuarios/{userId}/                 ← escritura por app Android
├── info/ { id, nombre, dispositivo }
└── ubicacion/ { latitud, longitud, hora }   (se borra con onDisconnect)
```

---

## DEUDA TÉCNICA CONOCIDA

Problemas detectados durante la revisión del estado actual del desarrollo:

1. **Enlace roto en el menú**: `layout.js:38` enlaza a `pages/catalogos.html`, que **no existe** (módulo de catálogos eliminado).
2. **IP local hardcodeada**: `ApiClient.kt` (app Android) apunta a `http://192.168.0.102:8000/api/` con `usesCleartextTraffic="true"` — no portable a producción.
3. **Trabajo sin commitear**: el backend tiene ~76 archivos modificados en el working tree (módulo Mobile, caja-ingresos, Firebase, borrados de productos/OCR/incidencias) sin commit.
4. **Código muerto en Android**: `primercodigo.kt` (100% comentado), `GeofenceRepository.kt` y `MapController.dibujarGeocercas()` sin uso; dependencias `lifecycle-viewmodel/livedata` declaradas pero no utilizadas.
5. **Tabla huérfana**: `incidencias` se crea en migraciones pero ya no tiene modelo ni controller.
6. **Dependencia sin uso**: `smalot/pdfparser` sigue en `composer.json` aunque `PedidoPdfExtractor` fue eliminado.
7. **Versión cacheada obsoleta**: `pedidos.html` carga `pedidos.js?v=20260626-ocr-table` pese a que el OCR fue eliminado.
8. **Repo anidado**: `public/.git` es un repositorio Git independiente del raíz (y `mapboxfirebase/` otro separado), lo que dificulta el versionado conjunto.
9. **Tokens sensibles en el repo**: token de Mapbox en `gradle.properties`/manifest y `config.local.js` vacío trackeado.
10. **Sin broadcast real ni colas**: `BroadcastServiceProvider` comentado, queues en driver `sync`, caché en `file` sin uso — sin websockets para el tracking (usa Firebase).
