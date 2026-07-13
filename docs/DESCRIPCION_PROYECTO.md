# Pw3c Distribuidora — Sistema de Gestión de Distribución

## Descripción General

Sistema integral para la gestión de una distribuidora farmacéutica. Cubre todo el ciclo logístico: gestión de pedidos de farmacias, planificación de despachos, asignación de rutas, seguimiento GPS en tiempo real de repartidores, administración de flota vehicular, control de caja diaria, gestión de incidencias/evidencias y reportes.

---

## Arquitectura del Sistema

```
[Frontend Web SPA] ──HTTP/JSON──> [API Laravel] ──Eloquent ORM──> [MySQL]
   (Vanilla JS)                     (PHP 8+, Laravel 9)
       │                                  │
  [Mapbox GL JS]                    [Sanctum Auth]
  [Firebase SDK]                   [Sistema de Eventos]
       │                                  │
       └──── [Firebase Realtime DB] ──────┘
                       │
              [Android App (Kotlin)]
           (GPS Tracking en tiempo real)
```

---

## BACKEND (Laravel 9 + PHP 8+)

### Stack Tecnológico

| Componente | Tecnología | Versión |
|------------|-----------|---------|
| Framework | Laravel | 9.x |
| Lenguaje | PHP | ^8.0 |
| Base de datos | MySQL | 8+ |
| Autenticación API | Laravel Sanctum | v2.14 |
| ORM | Eloquent | Laravel 9 |
| Eventos | Sistema de eventos de Laravel | - |
| Parseo PDF | smalot/pdfparser | v2.12 |
| HTTP Client | Guzzle | v7.2 |
| CORS | fruitcake/laravel-cors | v2.0.5 |
| Tests | PHPUnit | v9.5.10 |
| Contenedor dev | Laravel Sail (Docker) | v1.0.1 |
| Build frontend | Laravel Mix (Webpack) | v6.0.6 |

### Estructura del Backend

```
app/
├── Console/Commands
├── Events/                    # Eventos del dominio (5 eventos)
├── Exceptions/
├── Http/
│   ├── Controllers/
│   │   ├── ApiController.php              # Controller base con respuestas JSON
│   │   ├── AuthController.php             # Login, Register, Logout
│   │   ├── CatalogoController.php         # CRUD dinámico para tablas catálogo
│   │   ├── Controller.php                 # Controller base de Laravel
│   │   ├── Caja/
│   │   │   └── CajaController.php         # Control de caja diaria
│   │   ├── Despacho/
│   │   │   ├── DespachoController.php     # CRUD despachos
│   │   │   ├── IncidenciaController.php   # Incidencias en entregas
│   │   │   └── EvidenciaController.php    # Evidencias de entrega
│   │   ├── Farmacia/
│   │   │   ├── FarmaciaController.php     # CRUD farmacias
│   │   │   └── ContactoFarmaciaController.php
│   │   ├── Logistica/
│   │   │   ├── RutaController.php         # CRUD rutas
│   │   │   ├── ControlRutaController.php  # Ejecución de rutas
│   │   │   └── CentroLogisticoController.php # Planificación y tracking
│   │   ├── Pedido/
│   │   │   ├── PedidoController.php       # CRUD pedidos + Kanban
│   │   │   └── DetallePedidoController.php
│   │   ├── Producto/
│   │   │   └── ProductoController.php     # CRUD productos
│   │   ├── Repartidor/
│   │   │   ├── RepartidorController.php
│   │   │   └── RepartidorVehiculoController.php
│   │   ├── Reportes/
│   │   │   └── ReporteController.php      # Dashboard y estadísticas
│   │   ├── Seguridad/
│   │   │   ├── UsuarioController.php      # Gestión de usuarios
│   │   │   └── RolController.php          # Gestión de roles y permisos
│   │   └── Vehiculo/
│   │       └── VehiculoController.php     # CRUD vehículos
│   ├── Middleware/
│   │   ├── CheckPermission.php            # Verificación de permisos por módulo/acción
│   │   ├── Cors.php
│   │   └── ... (9 middleware en total)
│   └── Requests/
│       ├── LoginRequest.php
│       ├── RegisterRequest.php
│       └── Seguridad/                     # Validaciones por módulo
├── Listeners/
│   ├── ActualizarEstadoDespacho.php
│   ├── ActualizarEstadoPedido.php
│   ├── ActualizarEstadoRepartidor.php
│   ├── ActualizarEstadoVehiculo.php
│   └── RegistrarHistorial.php
├── Models/
│   ├── Caja/
│   │   ├── CajaRepartidor.php
│   │   ├── EstadoCaja.php
│   │   └── GastoOperativo.php
│   ├── Despacho/
│   │   ├── Despacho.php
│   │   ├── EstadoDespacho.php
│   │   └── HistorialEstadoDespacho.php
│   ├── Evidencia/
│   │   ├── EvidenciaEntrega.php
│   │   ├── Incidencia.php
│   │   ├── TipoEvidencia.php
│   │   └── TipoIncidencia.php
│   ├── Farmacia/
│   │   ├── Farmacia.php
│   │   ├── ContactoFarmacia.php
│   │   └── Cargo.php
│   ├── Logistica/
│   │   ├── Ruta.php
│   │   ├── RutaParada.php
│   │   ├── ControlRuta.php
│   │   ├── ControlRutaParada.php
│   │   ├── EstadoRuta.php
│   │   ├── HistorialEstadoRuta.php
│   │   ├── PlanificacionRuta.php
│   │   └── AsignacionVehiculoRepartidor.php
│   ├── Pedido/
│   │   ├── Pedido.php
│   │   ├── DetallePedido.php
│   │   ├── EstadoPedido.php
│   │   └── HistorialEstadoPedido.php
│   ├── Producto/
│   │   ├── Producto.php
│   │   └── EstadoDetalle.php
│   ├── Repartidor/
│   │   ├── Repartidor.php
│   │   ├── EstadoRepartidor.php
│   │   ├── HistorialEstadoRepartidor.php
│   │   ├── Licencia.php
│   │   └── ExtensionCI.php
│   ├── Seguridad/
│   │   ├── Usuario.php
│   │   ├── Rol.php
│   │   ├── Permiso.php
│   │   ├── Modulo.php
│   │   ├── Accion.php
│   │   ├── EstadoUsuario.php
│   │   ├── Auditoria.php
│   │   ├── SesionUsuario.php
│   │   └── TablaSistema.php
│   └── Vehiculo/
│       ├── Vehiculo.php
│       ├── EstadoVehiculo.php
│       ├── HistorialEstadoVehiculo.php
│       ├── Marca.php
│       ├── Modelo.php
│       └── Capacidad.php
├── Services/
│   ├── Logistica/AsignacionVehiculoService.php
│   └── Pedido/PedidoPdfExtractor.php
└── Providers/
    ├── AppServiceProvider.php
    ├── AuthServiceProvider.php
    ├── BroadcastServiceProvider.php
    ├── EventServiceProvider.php
    └── RouteServiceProvider.php
```

### Base de Datos

**Motor:** MySQL 8+ con cotejamiento `utf8mb4_unicode_ci`

La base de datos consta de aproximadamente **54 tablas** organizadas en dominios:

#### Seguridad y Catálogos Base
- `usuarios` — Usuarios del sistema (id_usuario PK, nombre, apellido, email unique, password_hash, telefono, id_estado_usuario FK, fecha_creacion, ultimo_acceso)
- `roles` — Roles (id_rol PK, nombre unique)
- `permisos` — Permisos (id_permiso PK, id_modulo FK, id_accion FK) con unique(modulo, accion)
- `modulos` — Módulos del sistema (Dashboard, Usuarios, Pedidos, Logistica, etc.)
- `acciones` — Acciones CRUD (acceder, listar, crear, editar, eliminar, etc.)
- `usuario_roles` — Relación muchos-a-muchos usuarios ↔ roles
- `rol_permiso` — Relación muchos-a-muchos roles ↔ permisos
- `estados_usuario` — Catálogo de estados de usuario
- `auditorias` — Registro de auditoría del sistema
- `sesiones_usuario` — Sesiones activas
- `tablas_sistema` — Metadatos de tablas del sistema

#### Catálogos de Negocio
- `estados_pedido`, `estados_repartidor`, `estados_vehiculo`, `estados_despacho`, `estados_ruta`, `estados_caja`, `estados_detalle` — Catálogos de estados
- `licencias`, `extensiones_ci` — Catálogos para repartidores
- `marcas`, `modelos`, `capacidades` — Catálogos para vehículos (modelos FK a marcas)
- `tipos_incidencia`, `tipos_evidencia` — Tipos de incidencias y evidencias
- `cargos` — Cargos de contactos de farmacia

#### Núcleo del Negocio
- `farmacias` — Clientes (id_farmacia PK, nombre, direccion, telefono, email, latitud decimal(10,7), longitud decimal(10,7), timestamps)
- `contactos_farmacia` — Contactos por farmacia (id_contacto PK, id_farmacia FK, nombre, cargo, telefono, email)
- `productos` — Catálogo de productos (id_producto PK, nombre, precio_unitario, unidad_medida, estado boolean)
- `pedidos` — Pedidos de farmacias (id_pedido PK, id_farmacia FK, id_usuario FK, id_estado_pedido FK, fecha_pedido, observaciones, subtotal, descuento, total, archivo_pedido)
- `detalle_pedido` — Líneas de pedido (id_detalle PK, id_pedido FK, id_producto FK, cantidad_solicitada, cantidad_entregada, precio_unitario, subtotal, id_estado_detalle FK)

#### Logística
- `repartidores` — Personal de delivery (id_repartidor PK, id_usuario FK, ci, id_extension_ci FK, id_licencia FK, id_estado_repartidor FK, firebase_uid)
- `vehiculos` — Flota (id_vehiculo PK, placa unique, id_modelo FK, id_capacidad FK, id_estado_vehiculo FK)
- `rutas` — Plantillas de rutas (id_ruta PK, nombre_ruta)
- `ruta_paradas` — Paradas ordenadas de ruta (id_parada PK, id_ruta FK, id_farmacia FK, orden_parada)
- `control_rutas` — Ejecución de ruta (id_control_ruta PK, id_ruta FK, fecha_ruta, hora_salida, hora_llegada_real, id_repartidor FK, id_vehiculo FK, id_estado_ruta FK)
- `control_ruta_paradas` — Paradas en ejecución (id_control_ruta_parada PK, id_control_ruta FK, id_pedido FK, id_farmacia FK, orden_parada, hora_estimada, hora_llegada_real, timestamps)
- `planificacion_rutas` — Planificaciones (id_planificacion PK, id_usuario FK, id_control_ruta FK nullable, estado BORRADOR/CONFIRMADA, datos_json JSON, fechas)
- `asignaciones_vehiculo_repartidor` — Asignaciones vehículo-repartidor

#### Despachos y Entregas
- `despachos` — Despachos (id_despacho PK, id_pedido FK, id_parada FK, id_control_ruta FK, fecha_hora_despacho, id_estado_despacho FK)
- `incidencias` — Incidencias en entregas (id_incidencia PK, id_despacho FK, id_tipo_incidencia FK, descripcion, fecha_hora)
- `evidencias_entrega` — Evidencias (id_evidencia PK, id_despacho FK, id_tipo_evidencia FK, archivo_url, fecha_hora)

#### Caja Diaria
- `caja_repartidor` — Caja por repartidor (id_caja PK, id_control_ruta FK, id_repartidor FK, fecha, monto_inicial, ingresos, gastos, monto_final, id_estado_caja FK)
- `gastos_operativos` — Gastos (id_gasto PK, id_caja FK, tipo_gasto, descripcion, monto, fecha)

#### Historial
- `historial_estado_pedido`, `historial_estado_repartidor`, `historial_estado_vehiculo`, `historial_estado_despacho`, `historial_estado_ruta`

#### Sistema
- `password_resets`, `failed_jobs`, `personal_access_tokens` (Sanctum)

### API REST — 80+ Endpoints

Todas las rutas bajo `/api/`, protegidas con middleware `auth:sanctum` y `CheckPermission`.

#### Autenticación (públicas)
```
POST /api/auth/login     → Login (email + password), devuelve token + usuario + permisos
POST /api/auth/register  → Registro de nuevo usuario
POST /api/auth/logout    → Revocar token actual
GET  /api/auth/user      → Obtener usuario autenticado con permisos
```

#### Seguridad
```
GET|POST   /api/usuarios                      → Listar/Crear usuarios
GET|PUT|DELETE /api/usuarios/{id}             → Ver/Actualizar/Eliminar usuario
PUT        /api/usuarios/{id}/bloquear        → Bloquear usuario
GET|POST   /api/usuarios/{usuario}/roles      → Obtener/Asignar roles

GET|POST   /api/roles                         → Listar/Crear roles
GET|PUT|DELETE /api/roles/{id}                → Ver/Actualizar/Eliminar rol
GET|POST   /api/roles/{rol}/permisos          → Obtener/Asignar permisos
```

#### Farmacias
```
GET|POST|PUT|DELETE /api/farmacias                   → CRUD farmacias
GET|POST|PUT|DELETE /api/farmacias/{id}/contactos    → CRUD contactos
```

#### Productos
```
GET|POST|PUT|DELETE /api/productos → CRUD productos
```

#### Pedidos
```
GET|POST|PUT|DELETE /api/pedidos                          → CRUD pedidos
POST /api/pedidos/extraer-pdf                             → Extraer productos desde PDF
POST /api/pedidos/{id}/cambiar-estado                     → Cambiar estado del pedido
POST /api/pedidos/{id}/subir-archivo                      → Subir archivo PDF adjunto
GET  /api/pedidos/{id}/descargar-archivo                  → Descargar PDF adjunto
GET|POST|PUT|DELETE /api/pedidos/{id}/detalles            → CRUD detalle del pedido
PUT  /api/pedidos/{id}/detalles/{detalle}/entrega         → Actualizar cantidad entregada
```

#### Repartidores
```
GET|POST|PUT|DELETE /api/repartidores                     → CRUD repartidores
POST /api/repartidores/{id}/cambiar-estado                → Cambiar estado
GET|POST|DELETE /api/repartidores/{id}/asignacion-vehiculo → Asignación vehículo
```

#### Vehículos
```
GET|POST|PUT|DELETE /api/vehiculos                → CRUD vehículos
POST /api/vehiculos/{id}/cambiar-estado           → Cambiar estado
```

#### Rutas
```
GET|POST|PUT|DELETE /api/rutas                    → CRUD rutas
GET|POST|PUT|DELETE /api/rutas/{id}/paradas       → CRUD paradas de ruta
```

#### Control Rutas
```
GET|POST|PUT|DELETE /api/controles-ruta           → CRUD control rutas
POST /api/controles-ruta/{id}/registrar-llegada   → Registrar llegada a parada
```

#### Despachos
```
GET|POST|PUT|DELETE /api/despachos                       → CRUD despachos
POST /api/despachos/{id}/cambiar-estado                  → Cambiar estado
GET|POST|PUT|DELETE /api/despachos/{id}/incidencias      → CRUD incidencias
GET|POST|PUT|DELETE /api/despachos/{id}/evidencias       → CRUD evidencias
```

#### Centro Logístico
```
GET  /api/logistica/pedidos-pendientes                    → Pedidos pendientes de despachar
GET  /api/logistica/repartidores-disponibles              → Repartidores disponibles
POST /api/logistica/planificacion/borrador                → Guardar borrador de planificación
POST /api/logistica/planificacion/confirmar               → Confirmar y ejecutar planificación
GET  /api/logistica/rutas-activas                         → Rutas activas con datos de tracking
GET  /api/logistica/rutas-activas/{id}                    → Detalle de ruta activa
POST /api/logistica/rutas-activas/{id}/entrega-completada → Marcar entrega completada
POST /api/logistica/rutas-activas/{id}/entrega-fallida    → Marcar entrega fallida
POST /api/logistica/rutas-activas/{id}/devolucion         → Registrar devolución
POST /api/logistica/rutas-activas/{id}/regreso-almacen    → Marcar regreso al almacén
POST /api/logistica/rutas-activas/{id}/registrar-llegada-parada
POST /api/logistica/rutas-activas/{id}/auto-completar-parada
```

#### Caja Diaria
```
POST /api/caja/abrir       → Abrir caja
POST /api/caja/ingreso     → Registrar ingreso
POST /api/caja/gasto       → Registrar gasto
PUT  /api/caja/cerrar/{id} → Cerrar caja
GET  /api/caja/dia         → Caja del día
GET  /api/caja/resumen     → Resumen de caja
```

#### Reportes
```
GET /api/reportes/resumen                    → Conteos globales
GET /api/reportes/pedidos-por-estado         → Pedidos agrupados por estado
GET /api/reportes/despachos-por-estado       → Despachos agrupados por estado
GET /api/reportes/pedidos-por-dia            → Pedidos por día
GET /api/reportes/repartidores-por-estado    → Repartidores por estado
GET /api/reportes/vehiculos-por-estado       → Vehículos por estado
GET /api/reportes/incidencias-por-tipo       → Incidencias por tipo
```

#### Catálogos
```
GET /api/catalogos/modelos-por-marca/{id}
GET /api/catalogos/estados-usuario, roles, modulos, acciones, tablas-sistema
GET /api/catalogos/estados-pedido, estados-repartidor, estados-vehiculo
GET /api/catalogos/estados-despacho, extensiones-ci, licencias
GET /api/catalogos/marcas, modelos, capacidades
GET /api/catalogos/tipos-incidencia, tipos-evidencia, cargos
POST|PUT|DELETE /api/catalogos/{nombre}  → CRUD dinámico (requiere permiso Usuarios)
```

### Sistema de Autenticación y Permisos (RBAC)

**Autenticación:**
- Basada en tokens Laravel Sanctum (expiración configurable: 480 min)
- Modelo `Usuario` extiende `Authenticatable` e implementa `HasApiTokens`
- Login: verifica email + password_hash (bcrypt), genera token de acceso
- Respuesta de login incluye: token, datos del usuario, lista plana de permisos

**Autorización:**
- Middleware `CheckPermission` mapea método HTTP a acción:
  - `index` → `listar`
  - `show` → `listar`
  - `store` → `crear`
  - `update` → `editar`
  - `destroy` → `eliminar`
- Método `$user->hasPermission($module, $action)` recorre roles y permisos del usuario
- 4 roles predefinidos: Administrator (1), Supervisor (2), Operador (3), Repartidor (4)
- 14 módulos: Dashboard, Reportes, Usuarios, Roles, Farmacias, Productos, Pedidos, Logistica, Caja Diaria, Repartidores, Vehiculos, Rutas, Control Rutas, Despachos
- 16 acciones: acceder, listar, crear, editar, eliminar, asignar-roles, asignar-permisos, gestionar-contactos, cambiar-estado, planificar, tracking, cerrar, gestionar-incidencias, gestionar-evidencias, gestionar-paradas, registrar-llegada

### Sistema de Eventos

Desacopla la lógica logística mediante 5 eventos de dominio:

| Evento | Disparador | Listeners |
|--------|-----------|-----------|
| `RutaIniciada` | Confirmar planificación | Actualizar despachos a "En camino", repartidor a "En ruta", estado vehículo |
| `EntregaCompletada` | Marcar entrega exitosa | Actualizar despacho a "Entregado completo", actualizar pedido, registrar hora llegada |
| `EntregaFallida` | Marcar entrega fallida | Actualizar despacho a "Fallido", registrar hora llegada |
| `DevolucionProducto` | Registrar devolución | Actualizar despacho a "Devolucion" |
| `RepartidorRegresa` | Finalizar ruta | Actualizar repartidor a "Disponible", actualizar vehículo, registrar cierre de ruta |

### Servicios Especializados

**PedidoPdfExtractor** (`app/Services/Pedido/PedidoPdfExtractor.php`):
- Parsea facturas y órdenes de compra en PDF usando `smalot/pdfparser`
- Detecta nombres de productos por coincidencia con el catálogo existente
- Extrae cantidades, precios unitarios y totales
- Deduplica productos repetidos
- Proporciona puntuación de confianza por extracción
- Acumula cantidades de productos duplicados

**AsignacionVehiculoService** (`app/Services/Logistica/AsignacionVehiculoService.php`):
- Lógica de asignación de vehículos a repartidores
- Validación de disponibilidad y estados

---

## FRONTEND WEB (Vanilla JavaScript SPA)

### Stack Tecnológico

| Componente | Tecnología |
|------------|-----------|
| Arquitectura | SPA (Single Page Application) |
| Lenguaje | JavaScript vanilla (sin frameworks) |
| Routing | Hash-based (`#pedidos`, `#logistica`, etc.) |
| Estilos | CSS personalizado (~1832 líneas) |
| Íconos | Font Awesome 6.5.1 |
| Tablas | jQuery DataTables (vía CDN) |
| Mapas | Mapbox GL JS |
| Tiempo real | Firebase Realtime Database SDK |
| Build | Sin bundler — HTML/CSS/JS plano servido estáticamente |

### Estructura del Frontend

```
frontenda_distribuidora/
├── index.html                    # Página de Login/Register
├── dashboard.html                # Shell principal con sidebar y contenedor dinámico
├── css/
│   ├── login.css                 # Estilos de la página de login
│   ├── style.css                 # ~1832 líneas — sistema de diseño completo
│   └── logistica.css             # Estilos específicos del centro logístico
├── js/
│   ├── config.js                 # Configuración central del frontend
│   │   ├── API_BASE_URL          # URL base de la API
│   │   ├── apiFetch()            # Wrapper fetch con token automático
│   │   ├── mostrarLoading()      # Indicador de carga
│   │   ├── mostrarAlerta()       # Alertas tipo toast
│   │   ├── formatearMoneda()     # Formateo de montos
│   │   └── hasPermission()       # Verificador de permisos
│   ├── auth.js                   # Login, register, logout, token management
│   ├── error-handler.js          # Captura global window.onerror + unhandledrejection
│   ├── layout.js                 # Renderizado de sidebar, navegación, toggle responsive
│   ├── modules/
│   │   ├── dashboard.js          # Dashboard con métricas y cards
│   │   ├── pedidos.js            # ~1840 líneas — CRUD + Kanban + PDF
│   │   ├── logistica.js          # Hub logístico con 4 pestañas
│   │   ├── tracking.js           # ~1031 líneas — Tracking GPS en tiempo real
│   │   ├── planificador.js       # Planificador de rutas (drag-and-drop)
│   │   ├── productos.js          # CRUD productos
│   │   ├── farmacias.js          # CRUD farmacias con coordenadas
│   │   ├── repartidores.js       # CRUD repartidores + asignación vehículo
│   │   ├── vehiculos.js          # CRUD vehículos
│   │   ├── rutas.js              # CRUD rutas + paradas ordenadas
│   │   ├── controles-ruta.js     # Ejecución de control de rutas
│   │   ├── despachos.js          # CRUD despachos + incidencias + evidencias
│   │   ├── usuarios.js           # Gestión de usuarios del sistema
│   │   ├── roles.js              # Gestión de roles y permisos
│   │   ├── caja.js               # Control de caja diaria
│   │   ├── historial.js          # Visor de historial de cambios
│   │   └── documentos.js         # Visor de documentación PDF
│   └── pages/                    # Páginas HTML (14 páginas, una por módulo)
└── docs/
    ├── Manual_de_Usuario.pdf
    └── Manual_Tecnico.pdf
```

### Características del Frontend por Módulo

#### Dashboard
- Cards con métricas en tiempo real: total pedidos, entregas completadas, farmacias activas, repartidores disponibles, vehículos operativos, incidencias
- Rutas activas con barras de progreso
- Resumen de caja del día
- Indicadores visuales con íconos y colores

#### Pedidos (módulo más extenso: ~1840 líneas)
- CRUD completo con formularios en modal
- Vista Kanban con columnas: Pendiente → Preparación → En Ruta → Entregado → Cancelado
- Arrastre y movimiento entre columnas
- Carga de archivo PDF adjunto al pedido
- Extracción automática de productos desde PDF
- Gestión de detalle del pedido con actualización de cantidades entregadas
- Cambio de estado del pedido
- Búsqueda, filtro y paginación con DataTables

#### Centro Logístico (interfaz de 4 pestañas)
1. **Pedidos** — Listado de pedidos pendientes con selección para despachar
2. **Despachar (Planificación)** — Interfaz de planificación:
   - Selección de repartidor, vehículo y ruta
   - Asignación de pedidos a repartidores
   - Guardado de borrador y confirmación de planificación
3. **Seguimiento** — Mapa Mapbox con tracking en tiempo real:
   - Posiciones de repartidores desde Firebase Realtime Database
   - Paradas con estado (pendiente/completada/fallida)
   - Botones de acción: completar entrega, fallido, devolución, regreso almacén
   - Panel de información de ruta activa
4. **Historial** — Rutas completadas con filtros por fecha

#### Tracking GPS (~1031 líneas)
- Integración con Mapbox GL JS para visualización de mapas
- Conexión en tiempo real con Firebase Realtime Database
- Marcadores de posición de repartidores
- Colores indicativos por estado de parada
- Panel lateral con detalle de la ruta activa
- Acciones de gestión en tiempo real

#### Farmacias
- CRUD completo con modal
- Campos de geolocalización (latitud, longitud)
- Gestión de contactos por farmacia (nombre, cargo, teléfono, email)
- DataTables con búsqueda y paginación

#### Repartidores
- CRUD completo con datos personales (CI, extensión CI, licencia)
- Vinculación con usuario del sistema
- Asignación de vehículo
- Cambio de estado con historial
- DataTables con filtros

#### Vehículos
- CRUD completo con selección en cascada (marca → modelo → capacidad)
- Placa única
- Cambio de estado con historial

#### Rutas
- CRUD con nombre de ruta
- Gestión de paradas ordenadas (farmacias en secuencia con número de orden)
- DataTables

#### Despachos
- CRUD completo
- Cambio de estado
- Gestión de incidencias (tipo, descripción, fecha)
- Gestión de evidencias (archivos, tipo)

#### Usuarios y Roles
- CRUD de usuarios del sistema
- Asignación de roles a usuarios
- CRUD de roles
- Asignación detallada de permisos (módulo × acción) con checkboxes
- DataTables

#### Caja Diaria
- Apertura de caja con monto inicial
- Registro de ingresos
- Registro de gastos operativos (tipo, descripción, monto)
- Cierre de caja con cálculo automático de monto final
- Resumen del día
- DataTables

#### Reportes
- Tablas resumen: pedidos por estado, despachos por estado
- Conteo de repartidores por estado y vehículos por estado
- Incidencias agrupadas por tipo
- Diseño responsive con cards

#### Documentación
- Visor de PDF embebido
- Manual de Usuario y Manual Técnico accesibles desde la interfaz

### Sistema de Diseño

**CSS personalizado** sin frameworks (Bootstrap, Tailwind, etc.):

- Variables CSS para paleta de colores, tipografía y espaciados
- Layout con sidebar fijo y contenido scrollable
- Sidebar responsive con toggle en dispositivos móviles
- Cards con sombras suaves para métricas
- Modales centrados con overlay
- Tablas con estilo DataTables (personalizado)
- Badges y labels con colores por estado
- Botones con variantes de color
- Formularios con estilos consistentes
- Kanban board con drag-and-drop visual
- Barras de progreso animadas
- Alertas tipo toast
- Diseño mobile-first con media queries
- Estilos específicos para el módulo logístico (`logistica.css`)

### Flujo de Autenticación en el Frontend

1. Usuario ingresa email + contraseña en `index.html`
2. `auth.js` envía POST a `/api/auth/login`
3. API devuelve: token, datos del usuario, permisos planos
4. Frontend almacena en `localStorage`: `auth_token`, `auth_user`, `auth_permissions`
5. Redirige a `dashboard.html` con el token en URL o localStorage
6. `layout.js` renderiza sidebar mostrando solo módulos permitidos
7. `apiFetch()` (config.js) inyecta automáticamente `Authorization: Bearer {token}` en cada petición
8. En 401 Unauthorized, redirige automáticamente al login
9. Logout elimina datos de localStorage y redirige al login

---

## APP MÓVIL (Android — Kotlin)

### Stack Tecnológico

| Componente | Tecnología | Versión |
|------------|-----------|---------|
| Lenguaje | Kotlin | 2.0.21 |
| SDK de Mapas | Mapbox Maps SDK for Android | 10.15.0 |
| Tiempo real | Firebase Realtime Database (KTX) | 20.3.0 |
| Firebase BOM | Firebase Bill of Materials | 34.10.0 |
| Localización | Mapbox Android Core Location Engine | incluido en SDK |
| UI | ConstraintLayout + Material 3 | 2.2.1 / 1.13.0 |
| AndroidX Core | Core KTX | 1.17.0 |
| Compilación | Android Gradle Plugin | 8.12.0 |
| Gradle | Gradle Wrapper | 8.13 |
| Min SDK | Android API 24 (Android 7.0) | - |
| Target SDK | Android API 36 | - |
| Google Services | google-services plugin | 4.4.4 |
| Pruebas | JUnit + Espresso | 4.13.2 / 3.7.0 |

### Estructura de la App

```
mapboxfirebase/
├── build.gradle.kts                         # Gradle project-level
├── settings.gradle.kts                      # Configuración con repositorio Mapbox
├── gradle.properties                        # MAPBOX_DOWNLOADS_TOKEN
├── local.properties                         # SDK path local
├── gradle/wrapper/
│   └── gradle-wrapper.properties            # Gradle 8.13
├── app/
│   ├── build.gradle.kts                     # Dependencias y configuración de build
│   ├── google-services.json                 # Configuración Firebase
│   ├── proguard-rules.pro                   # Sin reglas personalizadas
│   └── src/
│       ├── main/
│       │   ├── AndroidManifest.xml
│       │   ├── java/com/example/mapboxfirebase/
│       │   │   ├── ui/
│       │   │   │   ├── MainActivity.kt      # Actividad principal (única)
│       │   │   │   └── primercodigo.kt      # Código legacy comentado
│       │   │   ├── map/
│       │   │   │   └── MapController.kt     # Controlador del mapa Mapbox
│       │   │   ├── location/
│       │   │   │   └── LocationService.kt   # Servicio de geolocalización
│       │   │   ├── fireabase/               # (nombre con typo intencional)
│       │   │   │   ├── FirebaseRepository.kt # Escritura en Firebase RTDB
│       │   │   │   └── GeofenceRepository.kt # Lectura de geocercas desde Firebase
│       │   │   ├── geofence/
│       │   │   │   └── GeofenceManager.kt   # Algoritmo punto-en-polígono
│       │   │   ├── model/
│       │   │   │   ├── Usuario.kt           # Modelo de usuario
│       │   │   │   ├── Ubicacion.kt         # Modelo de ubicación GPS
│       │   │   │   └── Geocerca.kt          # Modelo de geocerca + Punto
│       │   │   └── utils/
│       │   │       ├── TimeUtils.kt         # Utilidad de formato de hora
│       │   │       └── DeviceUtils.kt       # Utilidad de información del dispositivo
│       │   └── res/
│       │       ├── layout/
│       │       │   └── activity_main.xml    # Layout: MapView + TextView
│       │       ├── values/
│       │       │   ├── colors.xml           # Blanco y negro
│       │       │   ├── strings.xml          # app_name
│       │       │   ├── themes.xml           # Tema Material3 NoActionBar
│       │       │   └── mapbox_access_token.xml
│       │       ├── values-night/
│       │       │   └── themes.xml           # Tema oscuro
│       │       └── xml/
│       │           ├── backup_rules.xml
│       │           └── data_extraction_rules.xml
│       ├── test/
│       │   └── ExampleUnitTest.kt
│       └── androidTest/
│           └── ExampleInstrumentedTest.kt
```

### AndroidManifest.xml — Permisos y Configuración

```xml
<uses-permission android:name="android.permission.INTERNET"/>
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION"/>
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION"/>

<meta-data android:name="com.mapbox.accessToken" android:value="pk.eyJ1Ijoi..."/>
```

- **INTERNET**: comunicación con Mapbox (tiles, geocoding) y Firebase
- **ACCESS_FINE_LOCATION**: GPS preciso para tracking en tiempo real
- **ACCESS_COARSE_LOCATION**: fallback de ubicación por red
- **Mapbox Access Token**: autenticación con servicios Mapbox (token público `pk.*`)

La app tiene una **única Activity** (`MainActivity`) como launcher.

### MainActivity.kt — Flujo Completo de la App

```kotlin
class MainActivity : AppCompatActivity() {

    // Componentes
    private lateinit var mapView: MapView              // Mapa Mapbox
    private lateinit var textView: TextView            // Info de ubicación
    private lateinit var locationService: LocationService  // GPS
    private lateinit var firebaseRepo: FirebaseRepository  // Firebase escritura
    private lateinit var geofenceRepo: GeofenceRepository  // Firebase lectura geocercas
    private lateinit var geofenceManager: GeofenceManager  // Algoritmo PIP
    private lateinit var mapController: MapController      // Control mapa
    private lateinit var usuario: Usuario                  // Usuario hardcodeado
    private var primeraActualizacion = true                // Control cámara
```

**Flujo de ejecución:**

1. **`onCreate()`**:
   - Infla layout `activity_main.xml` con un `MapView` y un `TextView`
   - Inicializa todos los servicios: `LocationService`, `FirebaseRepository`, `GeofenceRepository`, `GeofenceManager`, `MapController`
   - Crea un `Usuario` hardcodeado con id `"user_123"`, nombre `"Juan"` y nombre del dispositivo (`Build.MANUFACTURER + Build.MODEL`)
   - Solicita permiso `ACCESS_FINE_LOCATION` usando `ActivityResultContracts.RequestPermission`

2. **`iniciarApp()`** (ejecutado al conceder permiso):
   ```
   1. Configurar limpieza en desconexión de Firebase
   2. Inicializar el mapa Mapbox con estilo streets
   3. Escuchar geocercas desde Firebase (tiempo real)
   4. Dibujar geocercas en el mapa como polígonos
   5. Iniciar actualizaciones GPS cada 1 segundo
   6. Por cada actualización:
      a. Mostrar latitud, longitud, hora en pantalla
      b. Enviar ubicación a Firebase RTDB
      c. Mover cámara del mapa en la primera actualización
      d. Validar si está dentro de alguna geocerca
   ```

### MapController.kt — Gestión del Mapa

```kotlin
class MapController(private val mapView: MapView) {

    fun inicializarMapa() {
        // Carga estilo Mapbox Streets
        // Habilita localización del usuario con efecto pulsante
        // Inicializa PolygonAnnotationManager
    }

    fun dibujarGeocercas(geocercas: List<Geocerca>) {
        // Elimina polígonos anteriores
        // Dibuja cada geocerca como polígono:
        //   - Fill color: #3388FF (azul semitransparente)
        //   - Fill opacity: 0.3 (30%)
        //   - Outline color: #0066FF
    }

    fun moverCamara(location: Location) {
        // Centra cámara en la ubicación con zoom 18
    }
}
```

**Características del mapa:**
- **Estilo**: Mapbox Streets (calles, edificios, toponimia)
- **Localización**: indicador de ubicación del usuario con efecto pulsante habilitado
- **Geocercas**: renderizadas como polígonos azules semitransparentes con borde visible
- **Cámara**: zoom 18 (nivel de calle) centrado en la ubicación actual

### LocationService.kt — Servicio de GPS en Tiempo Real

```kotlin
class LocationService(private val context: Context) {

    private val locationEngine = LocationEngineProvider.getBestLocationEngine(context)

    fun iniciarActualizacion(callback: (Location) -> Unit) {
        val request = LocationEngineRequest.Builder(1000)
            .setPriority(LocationEngineRequest.PRIORITY_HIGH_ACCURACY)
            .build()

        locationEngine.requestLocationUpdates(request, callback, Looper.getMainLooper())
    }
}
```

**Configuración:**
- **Intervalo**: 1000 ms (1 segundo) — tiempo real
- **Precisión**: `PRIORITY_HIGH_ACCURACY` (usa GPS + red)
- **Engine**: `LocationEngineProvider.getBestLocationEngine()` — usa el mejor proveedor disponible
- **Callback**: entrega ubicación en el hilo principal (`Looper.getMainLooper()`)
- **Manejo de errores**: captura `SecurityException` si el permiso es revocado

### FirebaseRepository.kt — Escritura en Firebase

```kotlin
class FirebaseRepository {
    private val baseDatos = FirebaseDatabase.getInstance().reference

    fun configurarDesconexion(usuarioId: String) {
        // Escucha .info/connected
        // Si conectado, configura onDisconnect para eliminar ubicación
    }

    fun enviarUbicacion(usuario: Usuario, ubicacion: Ubicacion) {
        // Escribe usuario en: /usuarios/{id}/info/
        // Escribe ubicación en: /usuarios/{id}/ubicacion/
    }
}
```

**Estructura en Firebase Realtime Database:**
```
/usuarios/{userId}/
├── info/
│   ├── id: "user_123"
│   ├── nombre: "Juan"
│   └── dispositivo: "Xiaomi Redmi Note 12"
└── ubicacion/
    ├── latitud: -17.783333
    ├── longitud: -63.182222
    └── hora: "14:30:25"
```

**Limpieza en desconexión:**
- Usa el listener `/.info/connected` para detectar estado de conexión
- Configura `.onDisconnect().removeValue()` en el nodo `ubicacion`
- Cuando el repartidor pierde conexión o cierra la app, su última ubicación se elimina automáticamente, evitando datos obsoletos en el mapa

### GeofenceRepository.kt — Lectura de Geocercas desde Firebase

```kotlin
class GeofenceRepository {
    private val db = FirebaseDatabase.getInstance()
    var geocercasActuales: List<Geocerca> = emptyList()

    fun obtenerGeocercas(userId: String, callback: (List<Geocerca>) -> Unit) {
        val ref = db.getReference("geocercas").child(userId)
        ref.addValueEventListener { snapshot ->
            // Convierte cada hijo a Geocerca
            // Actualiza geocercasActuales
            // Ejecuta callback
        }
    }
}
```

**Estructura en Firebase:**
```
/geocercas/{userId}/
├── 0/
│   ├── id: "geo_001"
│   ├── nombre: "Zona Norte"
│   └── puntos: [{lat: -17.78, lng: -63.18}, ...]
├── 1/
│   ├── id: "geo_002"
│   ├── nombre: "Zona Sur"
│   └── puntos: [{lat: -17.80, lng: -63.20}, ...]
```

- Usa `addValueEventListener` para obtener actualizaciones en tiempo real
- Almacena la lista actual en `geocercasActuales` para validaciones posteriores
- Cada geocerca contiene una lista de puntos que definen un polígono

### GeofenceManager.kt — Algoritmo de Punto-en-Polígono

```kotlin
class GeofenceManager {
    fun estaDentroGeocerca(ubicacion: Location, geocerca: Geocerca): Boolean {
        // Implementación del algoritmo Ray-Casting
        // Cuenta cuántas veces un rayo horizontal cruza los bordes del polígono
        // Si el número de cruces es impar → está dentro
        // Si es par → está fuera
    }
}
```

**Algoritmo:**
- Implementa el algoritmo estándar **Ray-Casting** (también conocido como even-odd rule)
- Lanza un rayo horizontal desde el punto de ubicación hacia la derecha
- Cuenta las intersecciones con los segmentos del polígono
- Resultado: impar = dentro, par = fuera
- Se ejecuta en cada actualización de ubicación (cada 1 segundo)

### Modelos de Datos

```kotlin
data class Usuario(
    val id: String = "",
    val nombre: String = "",
    val dispositivo: String = ""
)

data class Ubicacion(
    val latitud: Double = 0.0,
    val longitud: Double = 0.0,
    val hora: String = ""
)

data class Geocerca(
    val id: String = "",
    val nombre: String = "",
    val puntos: List<Punto> = emptyList()
)

data class Punto(
    val lat: Double = 0.0,
    val lng: Double = 0.0
)
```

### Utilidades

**TimeUtils.kt:**
```kotlin
object TimeUtils {
    fun obtenerHoraActual(): String {
        return SimpleDateFormat("HH:mm:ss", Locale.getDefault()).format(Date())
    }
}
```
- Formatea la hora actual en formato `HH:mm:ss` (24 horas)

**DeviceUtils.kt:**
```kotlin
object DeviceUtils {
    fun obtenerNombreDispositivo(): String {
        return "${Build.MANUFACTURER} ${Build.MODEL}"
    }
}
```
- Obtiene el fabricante y modelo del dispositivo (ej: "Xiaomi M2101K6G", "samsung SM-S908B")

### Layout Principal (activity_main.xml)

```xml
<ConstraintLayout>
    <MapView />           <!-- Ocupa todo el espacio superior -->
    <TextView />          <!-- Barra inferior con coordenadas -->
</ConstraintLayout>
```

- **MapView** de Mapbox ocupa el 100% del ancho y alto hasta la barra de información inferior
- **TextView** anclado al bottom, centrado, con padding de 16dp, texto tamaño 18sp
- Diseño limpio y funcional sin elementos decorativos

### Permisos de Firebase

- **google-services.json** configurado para proyecto `mapboxfirebase-a79b5`
- API Key de Firebase: `AIzaSyC6ocm9J7hQTTf_eRpfrZlZgbt50JHQlIE`
- El proyecto Firebase tiene habilitado **Realtime Database** y **App Invites**

### Configuración de Build

**Project-level build.gradle.kts:**
```kotlin
plugins {
    id("com.android.application") version "8.12.0" apply false
    id("org.jetbrains.kotlin.android") version "2.0.21" apply false
    id("com.google.gms.google-services") version "4.4.4" apply false
}
```

**App-level build.gradle.kts** (dependencias principales):
```kotlin
dependencies {
    implementation(platform("com.google.firebase:firebase-bom:34.10.0"))
    implementation("com.google.firebase:firebase-database-ktx:20.3.0")
    implementation("com.mapbox.maps:android:10.15.0")
    implementation("androidx.core:core-ktx:1.17.0")
    implementation("androidx.appcompat:appcompat:1.7.1")
    implementation("com.google.android.material:material:1.13.0")
    implementation("androidx.activity:activity:1.12.4")
    implementation("androidx.constraintlayout:constraintlayout:2.2.1")
}
```

**settings.gradle.kts** — repositorio Mapbox con autenticación:
```kotlin
maven {
    url = uri("https://api.mapbox.com/downloads/v2/releases/maven")
    credentials {
        username = "mapbox"
        password = providers.gradleProperty("MAPBOX_DOWNLOADS_TOKEN").get()
    }
}
```

**Tokens de Mapbox** (2 tokens diferentes encontrados en el proyecto):
- `gradle.properties` → usado como `MAPBOX_DOWNLOADS_TOKEN` para autenticación Maven
- `AndroidManifest.xml` + `mapbox_access_token.xml` → token público `pk.*` para el SDK en tiempo de ejecución

### Resumen: Funcionalidades de la App Móvil

1. **Mapa interactivo Mapbox**: visualización de calles con estilo Streets, ubicación del usuario con indicador pulsante
2. **GPS en tiempo real**: actualización de ubicación cada 1 segundo con alta precisión
3. **Publicación en Firebase**: envía ubicación y datos del usuario a Firebase Realtime Database
4. **Geocercas desde Firebase**: carga dinámica de polígonos de geocercas desde la nube
5. **Visualización de geocercas**: dibuja polígonos semitransparentes en el mapa
6. **Validación de geocercas**: algoritmo Ray-Casting para detectar si el repartidor está dentro de un área geográfica
7. **Limpieza automática**: elimina ubicación al desconectarse (onDisconnect)
8. **Información en pantalla**: muestra coordenadas y hora actual en tiempo real
9. **Información del dispositivo**: envía fabricante y modelo para identificación
10. **Actualización en tiempo real de geocercas**: cambios en Firebase se reflejan automáticamente en el mapa

### Integración con el Sistema Web

La app móvil se integra con el frontend web a través de Firebase Realtime Database:

```
App Android                              Frontend Web
    │                                         │
    ├── Escribe en ──────────────────────┐   │
    │  /usuarios/{id}/ubicacion/         │   │
    │  /usuarios/{id}/info/              │   │
    │                                     │   │
    │                                     │   │
    │                              ┌──────┘   │
    │                              ▼          │
    │                      tracking.js         │
    │                      Lee desde:          │
    │                      /usuarios/{id}/     │
    │                      (ubicacion + info)  │
    │                                         │
    │    Geocercas ───────────────────────┐   │
    │    (definidas desde el backend)     │   │
    │                                     │   │
    │    ┌────────────────────────────────┘   │
    │    ▼                                    │
    │    App Android lee de:                  │
    │    /geocercas/{id}/                     │
```

El flujo completo:
1. Backend (o administrador) define geocercas en Firebase
2. App Android las lee y las dibuja en el mapa
3. App Android envía ubicación GPS cada 1 segundo
4. Frontend web (tracking.js) lee las ubicaciones en tiempo real
5. Sistema web muestra la posición del repartidor en el mapa web

---

## FUNCIONALIDADES CLAVE DEL SISTEMA COMPLETO

1. **RBAC completo**: Autenticación con tokens Sanctum + permisos granulares (14 módulos × 16 acciones, 4 roles predefinidos)
2. **Gestión de pedidos con Kanban**: Flujo visual de 5 estados con drag-and-drop, carga de PDF y extracción automática de datos
3. **Extracción automática de PDF**: Parsea facturas con detección de productos, cantidades, precios y puntuación de confianza
4. **Planificación logística**: Asignación de pedidos a repartidores con vehículo y ruta, modo borrador/confirmación
5. **Tracking GPS en tiempo real**: Mapa Mapbox interactivo con posiciones de repartidores vía Firebase Realtime Database, actualización cada 1 segundo
6. **Geocercas dinámicas**: Definición de zonas geográficas con validación automática de entrada/salida vía algoritmo Ray-Casting
7. **Gestión de incidencias y evidencias**: Registro de problemas con tipos catalogados y adjuntos de evidencia por despacho
8. **Caja diaria por repartidor**: Control completo de apertura, ingresos, gastos operativos y cierre con resumen
9. **Historial de cambios**: Trazabilidad completa de cambios de estado en pedidos, repartidores, vehículos, despachos y rutas
10. **Reportes dinámicos**: Dashboard con métricas agregadas, conteos por estado y distribuciones temporales
11. **Arquitectura basada en eventos**: Desacoplamiento mediante 5 eventos de dominio que actualizan automáticamente estados
12. **App Android para repartidores**: Seguimiento GPS en tiempo real con Mapbox Maps SDK, Firebase Realtime Database y detección de geocercas
13. **Documentación integrada**: Manuales de usuario y técnico en PDF accesibles desde la aplicación web
14. **Catálogos dinámicos**: CRUD genérico para todas las tablas catálogo del sistema (estados, tipos, marcas, etc.)
15. **Auditoría de sesiones**: Registro de sesiones de usuario y eventos de auditoría del sistema

---

## REQUISITOS DEL ENTORNO

### Backend
- PHP ^8.0
- Composer
- MySQL 8+
- Node.js + NPM (para Laravel Mix)
- Extensión PHP: bcmath, ctype, fileinfo, JSON, mbstring, openssl, PDO, tokenizer, xml, pdo_mysql

### Frontend Web
- Servidor web (Apache/Nginx) o servido estáticamente
- Navegador moderno con soporte ES6+
- Conexión a internet (Mapbox tiles, Firebase SDK, Font Awesome, jQuery CDN)

### App Android
- Android Studio Hedgehog o superior
- JDK 11+
- Android SDK API 24+
- Mapbox account (token público)
- Firebase project con Realtime Database habilitado

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
```

### App Android (gradle.properties)
```properties
MAPBOX_DOWNLOADS_TOKEN="REEMPLAZADO_MAPBOX_TOKEN_2"
```

### Firebase Realtime Database Structure
```
/usuarios/{userId}/
├── info/ { id, nombre, dispositivo }
└── ubicacion/ { latitud, longitud, hora }

/geocercas/{userId}/
└── {geocercaId}/
    ├── id
    ├── nombre
    └── puntos/ [{ lat, lng }]
```
