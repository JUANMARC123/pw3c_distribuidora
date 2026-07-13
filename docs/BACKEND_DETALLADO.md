# BACKEND — Explicación Detallada del Sistema

## Estructura General

```
Pw3c-distribuidora/
├── app/                    # Lógica de negocio (99 archivos)
├── bootstrap/              # Arranque de Laravel
├── config/                 # Configuraciones (15 archivos)
├── database/               # Migraciones (65) + Seeders (3)
├── public/                 # Front controller (index.php)
├── resources/              # Vistas Blade, assets
├── routes/                 # Definición de rutas (4 archivos)
├── storage/                # Archivos subidos, logs, sesiones, cache
├── tests/                  # Tests PHPUnit
├── vendor/                 # Dependencias Composer
├── .env                    # Variables de entorno
├── composer.json
├── package.json
├── webpack.mix.js
└── phpunit.xml
```

---

# 1. DIRECTORIO `app/` (99 archivos)

## 1.1 `app/Http/Kernel.php` — Kernel HTTP

Registra 3 capas de middleware:

**Middleware global** (se ejecuta en TODAS las peticiones):
| Middleware | Propósito |
|-----------|-----------|
| `TrustProxies` | Confía en proxies inversos (Nginx, etc.) |
| `HandleCors` (Fruitcake) | Maneja CORS en todas las rutas |
| `PreventRequestsDuringMaintenance` | Bloquea peticiones en modo mantenimiento |
| `ValidatePostSize` | Valida tamaño máximo de POST |
| `TrimStrings` | Recorta espacios en blanco de inputs |
| `ConvertEmptyStringsToNull` | Convierte strings vacíos a null |

**Grupo `api`** (se ejecuta en rutas de API):
| Middleware | Propósito |
|-----------|-----------|
| `throttle:api` | Límite de 60 peticiones/minuto por IP/usuario |
| `SubstituteBindings` | Inyección automática de modelos Eloquent |

**Middleware de ruta** (asignados manualmente):
| Alias | Clase | Propósito |
|-------|-------|-----------|
| `auth` | `Authenticate` | Redirige no autenticados al login |
| `permission` | **`CheckPermission`** | Verifica permisos módulo/acción |
| `guest` | `RedirectIfAuthenticated` | Redirige autenticados al HOME |
| `auth.basic` | HTTP Basic Auth | Autenticación básica HTTP |
| `throttle` | Rate Limiting | Límite de peticiones configurable |
| `can` | Autorización | Policy-based authorization |

---

## 1.2 `app/Http/Middleware/` — Middlewares (9 archivos)

### `CheckPermission.php` — El middleware más importante

```php
class CheckPermission
{
    private array $actionMap = [
        'index'  => 'listar',
        'show'   => 'listar',
        'store'  => 'crear',
        'update' => 'editar',
        'destroy'=> 'eliminar',
    ];

    public function handle(Request $request, Closure $next, string $module, string $action = null)
    {
        // 1. Obtiene usuario autenticado
        // 2. Si no hay action explícita, mapea método HTTP a acción
        // 3. Llama a $user->hasPermission($module, $action)
        // 4. Retorna 403 si no tiene permiso
    }
}
```

**¿Cómo funciona el RBAC?**
1. En `routes/api.php` se usa: `->middleware('permission:Pedidos,crear')` o `->middleware('permission:Pedidos')` (auto-detecta acción del método HTTP)
2. Si no se especifica acción, mapea: `index/show → listar`, `store → crear`, `update → editar`, `destroy → eliminar`
3. Delega en el modelo `Usuario::hasPermission()` que recorre todos los roles del usuario y sus permisos

### Otros middlewares:
- `Authenticate.php` — Extiende el middleware auth de Laravel, redirige si no está autenticado
- `EncryptCookies.php` — Encripta cookies (sin exclusiones)
- `PreventRequestsDuringMaintenance.php` — Modo mantenimiento
- `RedirectIfAuthenticated.php` — Si ya está autenticado, redirige al HOME
- `TrimStrings.php` — Limpia espacios en inputs (excepto passwords)
- `TrustHosts.php` — Confía en todos los subdominios
- `TrustProxies.php` — Confía en todos los proxies
- `VerifyCsrfToken.php` — Verificación CSRF (exclusiones vacías)

---

## 1.3 `app/Http/Controllers/` (26 controladores)

### `Controller.php` — Base de Laravel
Extiende `BaseController` con traits: `AuthorizesRequests`, `DispatchesJobs`, `ValidatesRequests`.

### `ApiController.php` — Base para toda la API
Todos los controlers de la API extienden este. Provee 4 métodos helpers:

```php
class ApiController extends Controller
{
    jsonResponse($data, $message, $code)         // → { success: true,  message, data }
    errorResponse($message, $code, $errors)      // → { success: false, message, errors? }
    paginatedResponse($paginator)                // → items + meta (current_page, last_page, per_page, total)
    safePaginate($query, $request, $default)     // Si per_page=-1 retorna TODOS sin paginación
}
```

### `AuthController.php` — Autenticación (113 líneas)

| Método | Descripción |
|--------|------------|
| `login(LoginRequest)` | Busca usuario por email, verifica Hash::check, valida estado activo, actualiza ultimo_acceso, crea token Sanctum, retorna user + token + permisos planos |
| `register(RegisterRequest)` | Crea usuario con Hash::make, asigna rol por defecto (4=Repartidor) o roles especificados, crea token |
| `logout(Request)` | Revoca el token actual `currentAccessToken()->delete()` |
| `user(Request)` | Retorna usuario autenticado con roles, permisos, estado |
| `getPermisos()` (privado) | Recorre roles → permisos, planifica los permisos únicos en array `[{modulo, accion}]` |

**Flujo de login:**
```
POST /api/auth/login { email, password }
  → Usuario::where('email', $email)->first()
  → Hash::check($password, $user->password_hash)
  → $user->id_estado_usuario === 1 (Activo)
  → $user->createToken('api-token')->plainTextToken
  → $user->load('roles.permisos.modulo', 'roles.permisos.accion', 'estado')
  → getPermisos() aplana permisos únicos
  → Response: { user, token, permisos }
```

### `CatalogoController.php` — Catálogos dinámicos

Mantiene un mapa `$catalogoMap` que asocia slugs de URL con modelos y columnas. Los métodos GET son públicos (requieren auth), los POST/PUT/DELETE requieren permiso `Usuarios`.

**18 endpoints GET** que retornan listas de catálogos: estados, roles, módulos, acciones, marcas, modelos, capacidades, licencias, etc.

### Controladores de Dominio

#### `Pedido/PedidoController.php` — Gestión de Pedidos

| Método | Lógica clave |
|--------|-------------|
| `index` | Lista pedidos con filtros por estado, farmacia, rango de fechas. Eager load: farmacia, usuario, estado. |
| `store` | Crea pedido, registra estado inicial en `historial_estado_pedido` |
| `show` | Retorna pedido con TODAS las relaciones: farmacia, usuario, estado, historiales, despacho, detalles con producto/estado |
| `update` | Si cambia estado, cierra historial anterior y crea uno nuevo |
| `destroy` | Si tiene despacho asociado, retorna 409 con flag `has_dispatch` (captura FK constraint) |
| `extraerPdf` | Acepta PDF, usa `PedidoPdfExtractor` para extraer líneas de producto |
| `subirArchivo` | Guarda PDF en `storage/app/public/pedidos/{id}/` |
| `descargarArchivo` | Descarga el PDF adjunto |
| `cambiarEstado` | Cambia estado + registra historial |

#### `Pedido/DetallePedidoController.php` — Detalle de Pedidos

| Método | Lógica clave |
|--------|-------------|
| `store` | Agrega producto al pedido, auto-calcula subtotal, **recalcula totales del pedido padre** |
| `update` | Actualiza detalle, recalcula totales |
| `destroy` | Elimina detalle, recalcula totales |
| `actualizarEntrega` | Actualiza `cantidad_entregada`, auto-asigna estado: 4 si completo, 5 si parcial |

**Método privado `recalcularPedido()`**:
```php
$subtotal = DetallePedido::where('id_pedido', $id)->sum('subtotal');
$pedido = Pedido::find($id);
$pedido->subtotal = $subtotal;
$pedido->total = $subtotal - ($pedido->descuento ?? 0);
$pedido->save();
```

#### `Producto/ProductoController.php` — Catálogo de Productos

CRUD estándar con:
- Filtro de búsqueda por nombre
- Filtro por estado (activo/inactivo)
- Valor por defecto: `unidad_medida = 'UNIDAD'`
- `destroy` previene eliminación si tiene detalles de pedido asociados

#### `Farmacia/FarmaciaController.php` — Farmacias

CRUD con coordenadas geográficas (latitud, longitud). `show` incluye contactos con sus cargos.

#### `Farmacia/ContactoFarmaciaController.php` — Contactos de Farmacias

CRUD anidado bajo farmacias (`/api/farmacias/{id}/contactos`).

#### `Despacho/DespachoController.php` — Despachos

| Método | Lógica clave |
|--------|-------------|
| `index` | Lista con filtros: estado, pedido, control_ruta, rango fechas |
| `store` | Crea despacho + historial inicial |
| `show` | Carga: pedido, parada, controlRuta, repartidor, vehiculo, incidencias, evidencias |
| `cambiarEstado` | Cambia estado + historial |

#### `Despacho/EvidenciaController.php` — Evidencias

CRUD con subida de archivos (jpg, jpeg, png, gif, pdf) almacenados en `storage/app/public/evidencias/`.

#### `Despacho/IncidenciaController.php` — Incidencias

CRUD estándar de incidencias asociadas a un despacho.

#### `Logistica/RutaController.php` — Definición de Rutas

CRUD de rutas + gestión anidada de paradas (storeParada, updateParada, destroyParada). `show` incluye paradas y controles.

#### `Logistica/ControlRutaController.php` — Ejecución de Rutas

| Método | Lógica clave |
|--------|-------------|
| `store` | Previene duplicado para misma ruta + fecha |
| `show` | Carga: ruta.paradas, repartidor, vehiculo, despachos |
| `registrarLlegada` | Registra hora_llegada_real, setea estado a 4 (Completada) |

#### `Logistica/CentroLogisticoController.php` — Centro Logístico (497 líneas)

El controlador más complejo del sistema. Maneja **12 operaciones**:

| Método | Descripción |
|--------|------------|
| `pedidosPendientes` | Retorna pedidos SIN despacho, en estado 2 (Aprobado/En preparacion), con farmacia y detalles |
| `repartidoresDisponibles` | Retorna repartidores con estado "Disponible", incluye vehículo asignado, verifica si tienen ruta activa previa |
| `guardarBorrador` | Guarda/actualiza planificación en estado BORRADOR con datos JSON |
| `confirmarRuta` | **La operación más compleja.** Validación transactional: |

**`confirmarRuta` paso a paso:**
```
1. Valida: repartidor_id, vehiculo_id opcional, id_planificacion opcional, array pedidos con orden
2. INICIA TRANSACCIÓN (DB::transaction)
3. Bloquea repartidor (lockForUpdate)
4. Verifica: repartidor disponible? tiene vehículo asignado?
5. Bloquea y verifica pedidos: existen? sin despacho? farmacia tiene coordenadas?
6. Crea Ruta con nombre generado "Ruta operativa YYYYmmdd-HHiiss Rep X"
7. Crea ControlRuta con estado 3 (En curso)
8. Crea HistorialEstadoRuta
9. Por cada pedido: crea RutaParada, ControlRutaParada y Despacho
10. Dispara evento RutaIniciada
11. Si hay planificación, la actualiza a CONFIRMADA
12. CIERRA TRANSACCIÓN
```

| Método | Descripción |
|--------|------------|
| `rutasActivas` | Retorna rutas en estado "En curso" o "Planificada" con progreso (completados/total), hora_llegada por parada, datos de despachos con coordenadas |
| `marcarEntregaCompletada` | Valida parada/despacho/pedido, dispara evento `EntregaCompletada` |
| `marcarEntregaFallida` | Similar, incluye motivo, dispara evento `EntregaFallida` |
| `marcarDevolucion` | Dispara evento `DevolucionProducto` |
| `registrarLlegadaParada` | Actualiza `hora_llegada` en ControlRutaParada |
| `autoCompletarParada` | Registra llegada + dispara EntregaCompletada. Si TODAS las paradas están completadas, dispara RepartidorRegresa, actualiza ControlRuta a estado 4 (Finalizada), crea historial |
| `marcarRegresoAlmacen` | Dispara RepartidorRegresa, marca ruta como Finalizada |
| `detalleRutaActiva` | Retorna detalle completo: repartidor, vehículo, despachos con productos, incidencias |

#### `Seguridad/UsuarioController.php` — Gestión de Usuarios

| Método | Lógica clave |
|--------|-------------|
| `store` | Crea usuario con Hash::make, asigna roles |
| `destroy` | Si tiene pedidos asociados (FK), retorna flag `can_block` sugiriendo bloqueo en vez de eliminación |
| `bloquear` | Setea id_estado_usuario = 2 (Bloqueado) |
| `roles` | Retorna roles asignados |
| `assignRoles` | Sincroniza roles con `sync()` |

#### `Seguridad/RolController.php` — Gestión de Roles

| Método | Lógica clave |
|--------|-------------|
| `permisos` | Retorna TODOS los permisos con indicador de cuáles están asignados al rol |
| `assignPermisos` | Sincroniza permisos con `sync()` |
| `destroy` | Captura FK constraint si tiene usuarios asignados |

#### `Repartidor/RepartidorController.php` — Repartidores

CRUD que incluye:
- Asignación de vehículo (usa `AsignacionVehiculoService`)
- Cambio de estado con historial
- `show` carga: usuario, estado, licencia, extensión CI, asignación vehicular activa, historiales, rutas de control

#### `Repartidor/RepartidorVehiculoController.php` — Asignación Vehículo

Operaciones: mostrar asignación activa, asignar vehículo, liberar vehículo. Usa `AsignacionVehiculoService`.

#### `Vehiculo/VehiculoController.php` — Vehículos

CRUD con modelo.marca, capacidad, estado, historiales, controlRutas.

#### `Caja/CajaController.php` — Caja Diaria

| Método | Lógica clave |
|--------|-------------|
| `abrirCaja` | Abre caja para repartidor hoy, previene duplicados |
| `registrarIngreso` | Registra ingreso en caja abierta, actualiza monto |
| `registrarGasto` | Crea GastoOperativo, actualiza monto de caja |
| `cerrarCaja` | Setea estado = 2 (Cerrada) |
| `cajaDelDia` | Retorna todas las cajas de una fecha con totales |
| `resumenDiario` | Conteos, totales, abiertas/cerradas |

#### `Reportes/ReporteController.php` — Reportes

| Método | Descripción |
|--------|------------|
| `resumen` | Conteos globales: usuarios, farmacias, pedidos, repartidores, vehículos, rutas, despachos, incidencias |
| `pedidosPorEstado` | Pedidos agrupados por estado con conteo |
| `despachosPorEstado` | Despachos agrupados por estado |
| `pedidosPorDia` | Pedidos por día (últimos N días, default 30) |
| `repartidoresPorEstado` | Repartidores por estado |
| `vehiculosPorEstado` | Vehículos por estado |
| `incidenciasPorTipo` | Incidencias por tipo |

---

## 1.4 `app/Http/Requests/` — Validaciones (3 archivos)

| Archivo | Reglas |
|---------|--------|
| `LoginRequest` | email: required\|email, password: required\|string\|min:6 |
| `RegisterRequest` | nombre/apellido: required, email: required\|unique:usuarios, password: min:6, roles: sometimes\|array |
| `Seguridad/UsuarioStoreRequest` | Similar a RegisterRequest + id_estado_usuario: required\|exists |

---

## 1.5 `app/Models/` — Todos los Modelos (39 en 8 subdirectorios)

Cada modelo tiene configuraciones explícitas de `$table`, `$primaryKey` y `$timestamps`. A continuación, la lista completa con todos sus atributos y relaciones.

---

### Seguridad/ (9 modelos)

#### `Usuario.php` — `App\Models\Seguridad\Usuario`
| Configuración | Valor |
|--------------|-------|
| Tabla | `usuarios` |
| PK | `id_usuario` |
| Timestamps | false |
| Extiende | `Authenticatable` (Laravel auth) |
| Traits | `HasApiTokens` (Sanctum), `HasFactory`, `Notifiable` |

**Fillable:**
```php
'nombre', 'apellido', 'email', 'password_hash', 'telefono',
'id_estado_usuario', 'fecha_creacion', 'fecha_bloqueo', 'ultimo_acceso'
```

**Hidden:** `password_hash`, `remember_token`

**Casts:**
```php
'fecha_creacion' => 'datetime',
'fecha_bloqueo'  => 'datetime',
'ultimo_acceso'  => 'datetime',
```

**Método adicional:** `getAuthPassword()` retorna `$this->password_hash` (Laravel lo usa para auth)

**Método `hasPermission($modulo, $accion): bool`** — Recorre todos los roles del usuario, y por cada rol recorre sus permisos. Si encuentra un permiso cuyo `modulo->nombre` coincide con `$modulo` y `accion->nombre` con `$accion`, retorna `true`.

**Relaciones:**
```php
estado()        → belongsTo(EstadoUsuario::class, 'id_estado_usuario')
roles()         → belongsToMany(Rol::class, 'usuario_roles', 'id_usuario', 'id_rol')
pedidos()       → hasMany(Pedido::class, 'id_usuario')
repartidor()    → hasOne(Repartidor::class, 'id_usuario')
sesiones()      → hasMany(SesionUsuario::class, 'id_usuario')
auditorias()    → hasMany(Auditoria::class, 'id_usuario')
```

---

#### `Rol.php` — `App\Models\Seguridad\Rol`
| Configuración | Valor |
|--------------|-------|
| Tabla | `roles` |
| PK | `id_rol` |
| Timestamps | false |

**Fillable:** `'nombre'`

**Relaciones:**
```php
usuarios()  → belongsToMany(Usuario::class, 'usuario_roles', 'id_rol', 'id_usuario')
permisos()  → belongsToMany(Permiso::class, 'rol_permiso', 'id_rol', 'id_permiso')
```

---

#### `Permiso.php` — `App\Models\Seguridad\Permiso`
| Configuración | Valor |
|--------------|-------|
| Tabla | `permisos` |
| PK | `id_permiso` |
| Timestamps | false |

**Fillable:** `'id_modulo', 'id_accion'`

**Relaciones:**
```php
modulo()  → belongsTo(Modulo::class, 'id_modulo')
accion()  → belongsTo(Accion::class, 'id_accion')
roles()   → belongsToMany(Rol::class, 'rol_permiso', 'id_permiso', 'id_rol')
```

---

#### `Modulo.php` — `App\Models\Seguridad\Modulo`
| Configuración | Valor |
|--------------|-------|
| Tabla | `modulos` |
| PK | `id_modulo` |
| Timestamps | false |

**Fillable:** `'nombre'` — (Dashboard, Reportes, Usuarios, Roles, Farmacias, Productos, Pedidos, Logística, Caja Diaria, Repartidores, Vehículos, Rutas, Control Rutas, Despachos)

**Relaciones:**
```php
permisos() → hasMany(Permiso::class, 'id_modulo')
```

---

#### `Accion.php` — `App\Models\Seguridad\Accion`
| Configuración | Valor |
|--------------|-------|
| Tabla | `acciones` |
| PK | `id_accion` |
| Timestamps | false |

**Fillable:** `'nombre'` — (acceder, listar, crear, editar, eliminar, asignar-roles, etc.)

**Relaciones:**
```php
permisos() → hasMany(Permiso::class, 'id_accion')
```

---

#### `EstadoUsuario.php` — `App\Models\Seguridad\EstadoUsuario`
| Configuración | Valor |
|--------------|-------|
| Tabla | `estados_usuario` |
| PK | `id_estado_usuario` |
| Timestamps | false |

**Fillable:** `'nombre_estado'` — Activo (1), Bloqueado (2), Suspendido (3)

**Relaciones:**
```php
usuarios() → hasMany(Usuario::class, 'id_estado_usuario')
```

---

#### `SesionUsuario.php` — `App\Models\Seguridad\SesionUsuario`
| Configuración | Valor |
|--------------|-------|
| Tabla | `sesiones_usuario` |
| PK | `id_sesion` |
| Timestamps | false |

**Fillable:** `'id_usuario', 'fecha_inicio', 'fecha_fin'`

**Casts:** `fecha_inicio => datetime`, `fecha_fin => datetime`

**Relaciones:**
```php
usuario() → belongsTo(Usuario::class, 'id_usuario')
```

---

#### `Auditoria.php` — `App\Models\Seguridad\Auditoria`
| Configuración | Valor |
|--------------|-------|
| Tabla | `auditorias` |
| PK | `id_auditoria` |
| Timestamps | false |

**Fillable:** `'id_usuario', 'id_accion', 'id_tabla', 'registro_id', 'fecha_hora'`

**Casts:** `fecha_hora => datetime`

**Relaciones:**
```php
usuario() → belongsTo(Usuario::class, 'id_usuario')
accion()  → belongsTo(Accion::class, 'id_accion')
tabla()   → belongsTo(TablaSistema::class, 'id_tabla')
```

---

#### `TablaSistema.php` — `App\Models\Seguridad\TablaSistema`
| Configuración | Valor |
|--------------|-------|
| Tabla | `tablas_sistema` |
| PK | `id_tabla` |
| Timestamps | false |

**Fillable:** `'nombre'` — (dashboard, reportes, usuarios, roles, farmacias, pedidos, productos, logistica, caja_repartidor, gastos_operativos, repartidores, vehiculos, rutas, control_rutas, despachos)

**Relaciones:**
```php
auditorias() → hasMany(Auditoria::class, 'id_tabla')
```

---

### Pedido/ (4 modelos)

#### `Pedido.php` — `App\Models\Pedido\Pedido`
| Configuración | Valor |
|--------------|-------|
| Tabla | `pedidos` |
| PK | `id_pedido` |
| Timestamps | false |

**Fillable:**
```php
'id_farmacia', 'id_usuario', 'id_estado_pedido', 'fecha_pedido',
'observaciones', 'subtotal', 'descuento', 'total', 'archivo_pedido'
```

**Casts:** `fecha_pedido => datetime`

**Relaciones:**
```php
farmacia()    → belongsTo(Farmacia::class, 'id_farmacia')
usuario()     → belongsTo(Usuario::class, 'id_usuario')
estado()      → belongsTo(EstadoPedido::class, 'id_estado_pedido')
historiales() → hasMany(HistorialEstadoPedido::class, 'id_pedido')
despacho()    → hasOne(Despacho::class, 'id_pedido')
detalles()    → hasMany(DetallePedido::class, 'id_pedido')
```

---

#### `DetallePedido.php` — `App\Models\Pedido\DetallePedido`
| Configuración | Valor |
|--------------|-------|
| Tabla | `detalle_pedido` |
| PK | `id_detalle` |
| Timestamps | false |

**Fillable:**
```php
'id_pedido', 'id_producto', 'cantidad_solicitada', 'cantidad_entregada',
'precio_unitario', 'subtotal', 'id_estado_detalle'
```

**Casts:** `cantidad_solicitada => decimal:2`, `cantidad_entregada => decimal:2`, `precio_unitario => decimal:2`, `subtotal => decimal:2`

**Relaciones:**
```php
pedido()   → belongsTo(Pedido::class, 'id_pedido')
producto() → belongsTo(Producto::class, 'id_producto')
estado()   → belongsTo(EstadoDetalle::class, 'id_estado_detalle')
```

---

#### `EstadoPedido.php` — `App\Models\Pedido\EstadoPedido`
| Configuración | Valor |
|--------------|-------|
| Tabla | `estados_pedido` |
| PK | `id_estado_pedido` |
| Timestamps | false |

**Fillable:** `'nombre_estado'`
**Valores:** Pendiente (1), En preparacion (2), En ruta (3), Entregado (4), Cancelado (5)

**Relaciones:**
```php
pedidos()     → hasMany(Pedido::class, 'id_estado_pedido')
historiales() → hasMany(HistorialEstadoPedido::class, 'id_estado_pedido')
```

---

#### `HistorialEstadoPedido.php` — `App\Models\Pedido\HistorialEstadoPedido`
| Configuración | Valor |
|--------------|-------|
| Tabla | `historial_estado_pedido` |
| PK | `id_historial` |
| Timestamps | false |

**Fillable:** `'id_pedido', 'id_estado_pedido', 'fecha_inicio', 'fecha_fin'`

**Casts:** `fecha_inicio => datetime`, `fecha_fin => datetime`

**Relaciones:**
```php
pedido() → belongsTo(Pedido::class, 'id_pedido')
estado() → belongsTo(EstadoPedido::class, 'id_estado_pedido')
```

---

### Producto/ (2 modelos)

#### `Producto.php` — `App\Models\Producto\Producto`
| Configuración | Valor |
|--------------|-------|
| Tabla | `productos` |
| PK | `id_producto` |
| Timestamps | **SI** (por defecto) |

**Fillable:** `'nombre', 'precio_unitario', 'unidad_medida', 'estado'`

**Casts:** `precio_unitario => decimal:2`, `estado => boolean`

**Relaciones:**
```php
detalles() → hasMany(DetallePedido::class, 'id_producto')
```

---

#### `EstadoDetalle.php` — `App\Models\Producto\EstadoDetalle`
| Configuración | Valor |
|--------------|-------|
| Tabla | `estados_detalle` |
| PK | `id_estado_detalle` |
| Timestamps | false |

**Fillable:** `'nombre_estado'`
**Valores:** Pendiente (1), Preparado (2), Enviado (3), Entregado (4), Faltante (5), Cancelado (6)

**Relaciones:**
```php
detalles() → hasMany(DetallePedido::class, 'id_estado_detalle')
```

---

### Farmacia/ (3 modelos)

#### `Farmacia.php` — `App\Models\Farmacia\Farmacia`
| Configuración | Valor |
|--------------|-------|
| Tabla | `farmacias` |
| PK | `id_farmacia` |
| Timestamps | **SI** (por defecto) |

**Fillable:** `'nombre', 'direccion', 'telefono', 'email', 'latitud', 'longitud'`

**Relaciones:**
```php
contactos() → hasMany(ContactoFarmacia::class, 'id_farmacia')
pedidos()   → hasMany(Pedido::class, 'id_farmacia')
paradas()   → hasMany(RutaParada::class, 'id_farmacia')
```

---

#### `ContactoFarmacia.php` — `App\Models\Farmacia\ContactoFarmacia`
| Configuración | Valor |
|--------------|-------|
| Tabla | `contactos_farmacia` |
| PK | `id_contacto` |
| Timestamps | false |

**Fillable:** `'id_farmacia', 'nombre_contacto', 'id_cargo', 'telefono', 'email'`

**Relaciones:**
```php
farmacia() → belongsTo(Farmacia::class, 'id_farmacia')
cargo()    → belongsTo(Cargo::class, 'id_cargo')
```

---

#### `Cargo.php` — `App\Models\Farmacia\Cargo`
| Configuración | Valor |
|--------------|-------|
| Tabla | `cargos` |
| PK | `id_cargo` |
| Timestamps | false |

**Fillable:** `'nombre_cargo'`
**Valores:** Gerente, Administrador, Farmaceutico, Recepcionista, Almacenero

**Relaciones:**
```php
contactos() → hasMany(ContactoFarmacia::class, 'id_cargo')
```

---

### Repartidor/ (5 modelos)

#### `Repartidor.php` — `App\Models\Repartidor\Repartidor`
| Configuración | Valor |
|--------------|-------|
| Tabla | `repartidores` |
| PK | `id_repartidor` |
| Timestamps | false |

**Fillable:**
```php
'id_usuario', 'ci', 'id_extension_ci', 'id_licencia',
'id_estado_repartidor', 'firebase_uid'
```

**Relaciones:**
```php
usuario()     → belongsTo(Usuario::class, 'id_usuario')
extensionCi() → belongsTo(ExtensionCI::class, 'id_extension_ci')
licencia()    → belongsTo(Licencia::class, 'id_licencia')
estado()      → belongsTo(EstadoRepartidor::class, 'id_estado_repartidor')
historiales() → hasMany(HistorialEstadoRepartidor::class, 'id_repartidor')
controlRutas()-> hasMany(ControlRuta::class, 'id_repartidor')

asignacionesVehiculo() → hasMany(AsignacionVehiculoRepartidor::class, 'id_repartidor')

// Relación especial: obtiene la asignación activa (activo=true, fecha_fin=null)
asignacionVehiculoActiva() → hasOne(AsignacionVehiculoRepartidor::class, 'id_repartidor')
    ->where('activo', true)
    ->whereNull('fecha_fin')
    ->latestOfMany('fecha_inicio')

// Vehículo actual a través de la asignación activa (hasOneThrough)
vehiculoActual() → hasOneThrough(Vehiculo::class, AsignacionVehiculoRepartidor::class,
    'id_repartidor', 'id_vehiculo', 'id_repartidor', 'id_vehiculo')
    ->where('asignaciones_vehiculo_repartidor.activo', true)
    ->whereNull('asignaciones_vehiculo_repartidor.fecha_fin')

// Último vehículo usado (a través de ControlRuta)
ultimoVehiculoUsado() → hasOneThrough(Vehiculo::class, ControlRuta::class,
    'id_repartidor', 'id_vehiculo', 'id_repartidor', 'id_vehiculo')
    ->latest('fecha_ruta')
```

---

#### `EstadoRepartidor.php` — `App\Models\Repartidor\EstadoRepartidor`
| Configuración | Valor |
|--------------|-------|
| Tabla | `estados_repartidor` |
| PK | `id_estado_repartidor` |
| Timestamps | false |

**Fillable:** `'nombre_estado'`
**Valores:** Disponible (1), En ruta (2), Inactivo (3)

**Relaciones:**
```php
repartidores() → hasMany(Repartidor::class, 'id_estado_repartidor')
historiales()  → hasMany(HistorialEstadoRepartidor::class, 'id_estado_repartidor')
```

---

#### `HistorialEstadoRepartidor.php` — `App\Models\Repartidor\HistorialEstadoRepartidor`
| Configuración | Valor |
|--------------|-------|
| Tabla | `historial_estado_repartidor` |
| PK | `id_historial` |
| Timestamps | false |

**Fillable:** `'id_repartidor', 'id_estado_repartidor', 'fecha_inicio', 'fecha_fin'`

**Casts:** `fecha_inicio => datetime`, `fecha_fin => datetime`

**Relaciones:**
```php
repartidor() → belongsTo(Repartidor::class, 'id_repartidor')
estado()     → belongsTo(EstadoRepartidor::class, 'id_estado_repartidor')
```

---

#### `ExtensionCI.php` — `App\Models\Repartidor\ExtensionCI`
| Configuración | Valor |
|--------------|-------|
| Tabla | `extensiones_ci` |
| PK | `id_extension_ci` |
| Timestamps | false |

**Fillable:** `'nombre_extension'`
**Valores:** LP, CBBA, SC, OR, PT, TJ, CH, BE, PD

**Relaciones:**
```php
repartidores() → hasMany(Repartidor::class, 'id_extension_ci')
```

---

#### `Licencia.php` — `App\Models\Repartidor\Licencia`
| Configuración | Valor |
|--------------|-------|
| Tabla | `licencias` |
| PK | `id_licencia` |
| Timestamps | false |

**Fillable:** `'categoria'`
**Valores:** A, B, C, P, Profesional

**Relaciones:**
```php
repartidores() → hasMany(Repartidor::class, 'id_licencia')
```

---

### Vehiculo/ (6 modelos)

#### `Vehiculo.php` — `App\Models\Vehiculo\Vehiculo`
| Configuración | Valor |
|--------------|-------|
| Tabla | `vehiculos` |
| PK | `id_vehiculo` |
| Timestamps | false |

**Fillable:** `'placa', 'id_modelo', 'id_capacidad', 'id_estado_vehiculo'`

**Relaciones:**
```php
modelo()               → belongsTo(Modelo::class, 'id_modelo')
capacidad()            → belongsTo(Capacidad::class, 'id_capacidad')
estado()               → belongsTo(EstadoVehiculo::class, 'id_estado_vehiculo')
historiales()          → hasMany(HistorialEstadoVehiculo::class, 'id_vehiculo')
controlRutas()         → hasMany(ControlRuta::class, 'id_vehiculo')
asignacionesRepartidor() → hasMany(AsignacionVehiculoRepartidor::class, 'id_vehiculo')

// Asignación activa (similar a la de Repartidor)
asignacionActiva() → hasOne(AsignacionVehiculoRepartidor::class, 'id_vehiculo')
    ->where('activo', true)
    ->whereNull('fecha_fin')
    ->latestOfMany('fecha_inicio')
```

---

#### `Modelo.php` — `App\Models\Vehiculo\Modelo`
| Configuración | Valor |
|--------------|-------|
| Tabla | `modelos` |
| PK | `id_modelo` |
| Timestamps | false |

**Fillable:** `'id_marca', 'nombre_modelo'`

**Relaciones:**
```php
marca()     → belongsTo(Marca::class, 'id_marca')
vehiculos() → hasMany(Vehiculo::class, 'id_modelo')
```

---

#### `Marca.php` — `App\Models\Vehiculo\Marca`
| Configuración | Valor |
|--------------|-------|
| Tabla | `marcas` |
| PK | `id_marca` |
| Timestamps | false |

**Fillable:** `'nombre_marca'` — Toyota, Suzuki, Nissan, Mitsubishi, Kia

**Relaciones:**
```php
modelos() → hasMany(Modelo::class, 'id_marca')
```

---

#### `Capacidad.php` — `App\Models\Vehiculo\Capacidad`
| Configuración | Valor |
|--------------|-------|
| Tabla | `capacidades` |
| PK | `id_capacidad` |
| Timestamps | false |

**Fillable:** `'capacidad_kg'` — 500, 1000, 2000, 3000, 5000 kg

**Casts:** `capacidad_kg => decimal:2`

**Relaciones:**
```php
vehiculos() → hasMany(Vehiculo::class, 'id_capacidad')
```

---

#### `EstadoVehiculo.php` — `App\Models\Vehiculo\EstadoVehiculo`
| Configuración | Valor |
|--------------|-------|
| Tabla | `estados_vehiculo` |
| PK | `id_estado_vehiculo` |
| Timestamps | false |

**Fillable:** `'nombre_estado'`
**Valores:** Operativo (1), En mantenimiento (2), Fuera de servicio (3), En movimiento (4)

**Relaciones:**
```php
vehiculos()   → hasMany(Vehiculo::class, 'id_estado_vehiculo')
historiales() → hasMany(HistorialEstadoVehiculo::class, 'id_estado_vehiculo')
```

---

#### `HistorialEstadoVehiculo.php` — `App\Models\Vehiculo\HistorialEstadoVehiculo`
| Configuración | Valor |
|--------------|-------|
| Tabla | `historial_estado_vehiculo` |
| PK | `id_historial` |
| Timestamps | false |

**Fillable:** `'id_vehiculo', 'id_estado_vehiculo', 'fecha_inicio', 'fecha_fin'`

**Casts:** `fecha_inicio => datetime`, `fecha_fin => datetime`

**Relaciones:**
```php
vehiculo() → belongsTo(Vehiculo::class, 'id_vehiculo')
estado()   → belongsTo(EstadoVehiculo::class, 'id_estado_vehiculo')
```

---

### Despacho/ (3 modelos)

#### `Despacho.php` — `App\Models\Despacho\Despacho`
| Configuración | Valor |
|--------------|-------|
| Tabla | `despachos` |
| PK | `id_despacho` |
| Timestamps | false |

**Fillable:**
```php
'id_pedido', 'id_parada', 'id_control_ruta', 'fecha_hora_despacho', 'id_estado_despacho'
```

**Casts:** `fecha_hora_despacho => datetime`

**Relaciones:**
```php
pedido()      → belongsTo(Pedido::class, 'id_pedido')
parada()      → belongsTo(RutaParada::class, 'id_parada')
controlRuta() → belongsTo(ControlRuta::class, 'id_control_ruta')
estado()      → belongsTo(EstadoDespacho::class, 'id_estado_despacho')
historiales() → hasMany(HistorialEstadoDespacho::class, 'id_despacho')
incidencias() → hasMany(Incidencia::class, 'id_despacho')
evidencias()  → hasMany(EvidenciaEntrega::class, 'id_despacho')
```

---

#### `EstadoDespacho.php` — `App\Models\Despacho\EstadoDespacho`
| Configuración | Valor |
|--------------|-------|
| Tabla | `estados_despacho` |
| PK | `id_estado_despacho` |
| Timestamps | false |

**Fillable:** `'nombre_estado'`
**Valores:** Pendiente (1), En camino (2), Entregado completo (3), Fallido (4), Devolucion (5)

**Relaciones:**
```php
despachos()   → hasMany(Despacho::class, 'id_estado_despacho')
historiales() → hasMany(HistorialEstadoDespacho::class, 'id_estado_despacho')
```

---

#### `HistorialEstadoDespacho.php` — `App\Models\Despacho\HistorialEstadoDespacho`
| Configuración | Valor |
|--------------|-------|
| Tabla | `historial_estado_despacho` |
| PK | `id_historial` |
| Timestamps | false |

**Fillable:** `'id_despacho', 'id_estado_despacho', 'fecha_inicio', 'fecha_fin'`

**Casts:** `fecha_inicio => datetime`, `fecha_fin => datetime`

**Relaciones:**
```php
despacho() → belongsTo(Despacho::class, 'id_despacho')
estado()   → belongsTo(EstadoDespacho::class, 'id_estado_despacho')
```

---

### Evidencia/ (4 modelos)

#### `EvidenciaEntrega.php` — `App\Models\Evidencia\EvidenciaEntrega`
| Configuración | Valor |
|--------------|-------|
| Tabla | `evidencias_entrega` |
| PK | `id_evidencia` |
| Timestamps | false |

**Fillable:** `'id_despacho', 'id_tipo_evidencia', 'archivo', 'fecha_registro'`

**Casts:** `fecha_registro => datetime`

**Relaciones:**
```php
despacho()     → belongsTo(Despacho::class, 'id_despacho')
tipoEvidencia()→ belongsTo(TipoEvidencia::class, 'id_tipo_evidencia')
```

---

#### `Incidencia.php` — `App\Models\Evidencia\Incidencia`
| Configuración | Valor |
|--------------|-------|
| Tabla | `incidencias` |
| PK | `id_incidencia` |
| Timestamps | false |

**Fillable:** `'id_despacho', 'id_tipo_incidencia', 'descripcion', 'fecha_incidencia'`

**Casts:** `fecha_incidencia => datetime`

**Relaciones:**
```php
despacho()       → belongsTo(Despacho::class, 'id_despacho')
tipoIncidencia() → belongsTo(TipoIncidencia::class, 'id_tipo_incidencia')
```

---

#### `TipoEvidencia.php` — `App\Models\Evidencia\TipoEvidencia`
| Configuración | Valor |
|--------------|-------|
| Tabla | `tipos_evidencia` |
| PK | `id_tipo_evidencia` |
| Timestamps | false |

**Fillable:** `'nombre_tipo'`
**Valores:** Foto de entrega, Firma digital, Documento adjunto

**Relaciones:**
```php
evidencias() → hasMany(EvidenciaEntrega::class, 'id_tipo_evidencia')
```

---

#### `TipoIncidencia.php` — `App\Models\Evidencia\TipoIncidencia`
| Configuración | Valor |
|--------------|-------|
| Tabla | `tipos_incidencia` |
| PK | `id_tipo_incidencia` |
| Timestamps | false |

**Fillable:** `'nombre_tipo'`
**Valores:** Retraso en la entrega, Producto dañado, Direccion incorrecta, Cliente ausente, Fallo mecanico, Otro

**Relaciones:**
```php
incidencias() → hasMany(Incidencia::class, 'id_tipo_incidencia')
```

---

### Logistica/ (8 modelos)

#### `Ruta.php` — `App\Models\Logistica\Ruta`
| Configuración | Valor |
|--------------|-------|
| Tabla | `rutas` |
| PK | `id_ruta` |
| Timestamps | false |

**Fillable:** `'nombre_ruta'`

**Relaciones:**
```php
paradas()   → hasMany(RutaParada::class, 'id_ruta')->orderBy('orden_parada')
controles() → hasMany(ControlRuta::class, 'id_ruta')
```

---

#### `RutaParada.php` — `App\Models\Logistica\RutaParada`
| Configuración | Valor |
|--------------|-------|
| Tabla | `ruta_paradas` |
| PK | `id_parada` |
| Timestamps | false |

**Fillable:** `'id_ruta', 'id_farmacia', 'orden_parada'`

**Relaciones:**
```php
ruta()      → belongsTo(Ruta::class, 'id_ruta')
farmacia()  → belongsTo(Farmacia::class, 'id_farmacia')
despachos() → hasMany(Despacho::class, 'id_parada')
```

---

#### `ControlRuta.php` — `App\Models\Logistica\ControlRuta`
| Configuración | Valor |
|--------------|-------|
| Tabla | `control_rutas` |
| PK | `id_control_ruta` |
| Timestamps | false |

**Fillable:**
```php
'id_ruta', 'fecha_ruta', 'hora_salida', 'hora_llegada_real',
'id_repartidor', 'id_vehiculo', 'id_estado_ruta'
```

**Casts:** `fecha_ruta => date`, `hora_salida => string`, `hora_llegada_real => string`

**Relaciones:**
```php
ruta()            → belongsTo(Ruta::class, 'id_ruta')
repartidor()      → belongsTo(Repartidor::class, 'id_repartidor')
vehiculo()        → belongsTo(Vehiculo::class, 'id_vehiculo')
estado()          → belongsTo(EstadoRuta::class, 'id_estado_ruta')
despachos()       → hasMany(Despacho::class, 'id_control_ruta')
paradasEjecucion()→ hasMany(ControlRutaParada::class, 'id_control_ruta')->orderBy('orden_parada')
planificaciones() → hasMany(PlanificacionRuta::class, 'id_control_ruta')
```

---

#### `ControlRutaParada.php` — `App\Models\Logistica\ControlRutaParada`
| Configuración | Valor |
|--------------|-------|
| Tabla | `control_ruta_paradas` |
| PK | `id_control_ruta_parada` |
| Timestamps | **SI** (por defecto) |

**Fillable:** `'id_control_ruta', 'id_pedido', 'id_farmacia', 'orden_parada', 'hora_llegada_real', 'hora_llegada'`

**Casts:** `hora_llegada_real => string`

**Relaciones:**
```php
controlRuta() → belongsTo(ControlRuta::class, 'id_control_ruta')
pedido()      → belongsTo(Pedido::class, 'id_pedido')
farmacia()    → belongsTo(Farmacia::class, 'id_farmacia')
```

---

#### `EstadoRuta.php` — `App\Models\Logistica\EstadoRuta`
| Configuración | Valor |
|--------------|-------|
| Tabla | `estados_ruta` |
| PK | `id_estado_ruta` |
| Timestamps | false |

**Fillable:** `'nombre_estado'`
**Valores:** Borrador (1), Planificada (2), En curso (3), Finalizada (4), Cancelada (5)

**Relaciones:**
```php
controles() → hasMany(ControlRuta::class, 'id_estado_ruta')
```

---

#### `HistorialEstadoRuta.php` — `App\Models\Logistica\HistorialEstadoRuta`
| Configuración | Valor |
|--------------|-------|
| Tabla | `historial_estado_ruta` |
| PK | `id_historial` |
| Timestamps | false |

**Fillable:** `'id_control_ruta', 'id_estado_ruta', 'fecha_inicio', 'fecha_fin'`

**Casts:** `fecha_inicio => datetime`, `fecha_fin => datetime`

**Relaciones:**
```php
controlRuta() → belongsTo(ControlRuta::class, 'id_control_ruta')
estado()      → belongsTo(EstadoRuta::class, 'id_estado_ruta')
```

---

#### `PlanificacionRuta.php` — `App\Models\Logistica\PlanificacionRuta`
| Configuración | Valor |
|--------------|-------|
| Tabla | `planificacion_rutas` |
| PK | `id_planificacion` |
| Timestamps | false |

**Fillable:** `'id_usuario', 'id_control_ruta', 'estado', 'datos_json', 'fecha_creacion', 'fecha_confirmacion'`

**Casts:** `datos_json => array`, `fecha_creacion => datetime`, `fecha_confirmacion => datetime`

**Relaciones:**
```php
usuario()     → belongsTo(Usuario::class, 'id_usuario')
controlRuta() → belongsTo(ControlRuta::class, 'id_control_ruta')
```

---

#### `AsignacionVehiculoRepartidor.php` — `App\Models\Logistica\AsignacionVehiculoRepartidor`
| Configuración | Valor |
|--------------|-------|
| Tabla | `asignaciones_vehiculo_repartidor` |
| PK | `id_asignacion` |
| Timestamps | **SI** (por defecto) |

**Fillable:** `'id_repartidor', 'id_vehiculo', 'fecha_inicio', 'fecha_fin', 'activo', 'observaciones'`

**Casts:** `fecha_inicio => datetime`, `fecha_fin => datetime`, `activo => boolean`

**Relaciones:**
```php
repartidor() → belongsTo(Repartidor::class, 'id_repartidor')
vehiculo()   → belongsTo(Vehiculo::class, 'id_vehiculo')
```

---

### Caja/ (3 modelos)

#### `CajaRepartidor.php` — `App\Models\Caja\CajaRepartidor`
| Configuración | Valor |
|--------------|-------|
| Tabla | `caja_repartidor` |
| PK | `id_caja` |
| Timestamps | false |

**Fillable:** `'id_control_ruta', 'id_repartidor', 'fecha', 'monto_inicial', 'ingresos', 'gastos', 'monto_final', 'id_estado_caja'`

**Casts:** `fecha => date`, `monto_inicial => decimal:2`, `ingresos => decimal:2`, `gastos => decimal:2`, `monto_final => decimal:2`

**Relaciones:**
```php
repartidor()  → belongsTo(Repartidor::class, 'id_repartidor')
controlRuta() → belongsTo(ControlRuta::class, 'id_control_ruta')
estado()      → belongsTo(EstadoCaja::class, 'id_estado_caja')
gastos()      → hasMany(GastoOperativo::class, 'id_caja')
```

---

#### `EstadoCaja.php` — `App\Models\Caja\EstadoCaja`
| Configuración | Valor |
|--------------|-------|
| Tabla | `estados_caja` |
| PK | `id_estado_caja` |
| Timestamps | false |

**Fillable:** `'nombre_estado'`
**Valores:** Abierta (1), Cerrada (2), Revisada (3)

**Relaciones:**
```php
cajas() → hasMany(CajaRepartidor::class, 'id_estado_caja')
```

---

#### `GastoOperativo.php` — `App\Models\Caja\GastoOperativo`
| Configuración | Valor |
|--------------|-------|
| Tabla | `gastos_operativos` |
| PK | `id_gasto` |
| Timestamps | false |

**Fillable:** `'id_caja', 'tipo_gasto', 'descripcion', 'monto', 'comprobante', 'fecha'`

**Casts:** `monto => decimal:2`, `fecha => datetime`

**Relaciones:**
```php
caja() → belongsTo(CajaRepartidor::class, 'id_caja')
```

---

## 1.6 `app/Events/` — Eventos del Dominio (5 archivos)

**`RutaIniciada.php`** — Dispara al confirmar una ruta:
```php
class RutaIniciada
{
    public ControlRuta $controlRuta;
    public array $pedidoIds;
    public int $vehiculoId;
}
```

**`EntregaCompletada.php`** — Entrega exitosa:
```php
class EntregaCompletada
{
    public ControlRutaParada $parada;
    public int $idDespacho;
    public int $idPedido;
}
```

**`EntregaFallida.php`** — Entrega fallida (incluye motivo):
```php
class EntregaFallida
{
    public ControlRutaParada $parada;
    public int $idDespacho;
    public int $idPedido;
    public string $motivo;
}
```

**`DevolucionProducto.php`** — Devolución registrada (mismos datos que EntregaCompletada)

**`RepartidorRegresa.php`** — Repartidor vuelve al almacén:
```php
class RepartidorRegresa
{
    public ControlRuta $controlRuta;
    public int $idRepartidor;
    public int $idVehiculo;
}
```

---

## 1.7 `app/Listeners/` — Suscriptores de Eventos (5 archivos)

### `ActualizarEstadoDespachoListener.php` — Escucha 4 eventos:
| Evento | Acción | Nuevo estado |
|--------|--------|-------------|
| `RutaIniciada` | Despacho::whereIn(id_pedido) update | 2 (En camino) |
| `EntregaCompletada` | Despacho::where(id_despacho) update | 3 (Entregado completo) |
| `EntregaFallida` | Despacho::where(id_despacho) update | 4 (Fallido) |
| `DevolucionProducto` | Despacho::where(id_despacho) update | 5 (Devolucion) |

### `ActualizarEstadoPedidosListener.php` — Escucha 4 eventos:
| Evento | Nuevo estado del pedido |
|--------|------------------------|
| `RutaIniciada` | 3 (En ruta) |
| `EntregaCompletada` | 4 (Entregado) |
| `EntregaFallida` | 5 (Fallido) |
| `DevolucionProducto` | 4 (Entregado) |

### `ActualizarEstadoRepartidorListener.php` — Escucha 2 eventos:
| Evento | Nuevo estado del repartidor |
|--------|---------------------------|
| `RutaIniciada` | 2 (En ruta) |
| `RepartidorRegresa` | 1 (Disponible) |

### `ActualizarEstadoVehiculoListener.php` — Escucha 2 eventos:
| Evento | Nuevo estado del vehículo |
|--------|-------------------------|
| `RutaIniciada` | 4 (En ruta) |
| `RepartidorRegresa` | 1 (Operativo) |

### `RegistrarLlegadaParadaListener.php` — Escucha 3 eventos:
| Evento | Acción |
|--------|--------|
| `EntregaCompletada` | Registra hora_llegada en la parada |
| `EntregaFallida` | Registra hora_llegada en la parada |
| `DevolucionProducto` | Registra hora_llegada en la parada |

### `EventServiceProvider.php` — Registro de suscriptores
```php
public function boot()
{
    Event::subscribe(ActualizarEstadoPedidosListener::class);
    Event::subscribe(ActualizarEstadoRepartidorListener::class);
    Event::subscribe(ActualizarEstadoVehiculoListener::class);
    Event::subscribe(ActualizarEstadoDespachoListener::class);
    Event::subscribe(RegistrarLlegadaParadaListener::class);
}
```

Todos los listeners usan el método `subscribe()` que retorna un array `[Evento => 'método']`.

---

## 1.8 `app/Services/` — Servicios Especializados (2 archivos)

### `PedidoPdfExtractor.php` — Extractor de PDF (196 líneas)

**Propósito**: Parsea facturas/órdenes de compra en PDF y extrae productos automáticamente.

**Algoritmo completo:**

1. **`extraer(path)`**:
   - Parsea el PDF con `smalot/pdfparser`
   - Obtiene todo el texto plano
   - Divide en líneas
   - Carga todos los productos activos del catálogo
   - Procesa línea por línea

2. **`parsearLineaProducto(line, productos)`**:
   - Busca coincidencia en catálogo
   - Extrae cantidad (regex: `\d+ - ud|und|unidad|...`)
   - Extrae montos (encuentra todos los valores decimales)
   - Determina precio unitario (últimos 2 montos: penúltimo = precio unitario, último = total)
   - Si cantidad > 0: calcula precio unitario = total / cantidad
   - Valida consistencia: `|cantidad * precio - total| < 0.15`
   - Genera advertencias: sin cantidad, sin precio, sin coincidencia en catálogo

3. **`buscarProducto(line, productos)`**:
   - Normaliza línea y nombre del producto (lowercase + transliteración ASCII)
   - Primero busca coincidencia exacta vía `str_contains`
   - Si no, usa `similar_text()` con threshold >= 55%

4. **`deduplicar(rows)`**:
   - Agrupa por `id_producto` o nombre normalizado
   - Acumula cantidades
   - Reduce confianza a 75 si hubo duplicados

**Sistema de confianza**:
| Situación | Confianza |
|-----------|-----------|
| Coincidencia exacta en catálogo | 92 |
| Coincidencia exacta + advertencias | 75 |
| Sin coincidencia (texto extraído) | 68 |
| Producto duplicado | min(actual, 75) |

### `AsignacionVehiculoService.php` — Asignación Vehículo (54 líneas)

```php
class AsignacionVehiculoService
{
    asignar($idRepartidor, $idVehiculo, $observaciones): AsignacionVehiculoRepartidor
    liberar($idRepartidor): void
}
```

**`asignar()`** es transactional:
1. Desactiva asignación activa actual del repartidor (activo=false, fecha_fin=now)
2. Desactiva asignación activa actual del vehículo
3. Crea nueva asignación con activo=true, fecha_inicio=now

**`liberar()`**: Desactiva la asignación activa del repartidor.

---

## 1.9 `app/Providers/` (5 archivos)

**`AppServiceProvider.php`**:
```php
Sanctum::authenticateAccessTokensUsing(function ($token, $isValid) {
    return $token->tokenable->id_estado_usuario === 1 && $isValid;
});
```
Valida que el usuario esté Activo (id_estado_usuario = 1) además de la validez del token Sanctum.

**`AuthServiceProvider.php`**: Proveedor estándar (sin policies definidas aún)

**`BroadcastServiceProvider.php`**: Carga `routes/channels.php`

**`EventServiceProvider.php`**: Registra los 5 listeners como suscriptores (ver sección 1.7)

**`RouteServiceProvider.php`**: Carga rutas API y Web, configura rate limiting (60/min)

---

## 1.10 `app/Exceptions/Handler.php`

Manejador de excepciones estándar. Campos que no se incluyen en logs: password fields.

---

# 2. DIRECTORIO `database/` — Migraciones y Seeders

## 2.1 Migraciones (65 archivos)

### Estructura de Nombres
Las migraciones usan prefijos de fecha que definen el orden de ejecución:

| Prefijo | Grupo | Tablas |
|---------|-------|--------|
| `2024_01_01_*` | Catálogos (1-15) | estados_usuario, roles, modulos, acciones, tablas_sistema, estados_pedido, estados_repartidor, estados_vehiculo, estados_despacho, licencias, extensiones_ci, marcas, capacidades, tipos_incidencia, tipos_evidencia, cargos |
| `2024_01_02_*` | Entidades core (5) | usuarios, farmacias, modelos, repartidores, vehiculos, rutas |
| `2024_01_03_*` | Entidades dependientes (8) | pedidos, contactos_farmacia, usuario_roles, permisos, rol_permiso, ruta_paradas, control_rutas, despachos |
| `2024_01_04_*` | Historial/Auditoría (8) | historial_estado_pedido, historial_estado_repartidor, historial_estado_vehiculo, historial_estado_despacho, auditorias, sesiones_usuario, incidencias, evidencias_entrega |
| `2024_06_11_*` | Extensiones (19) | Drops users, estados_detalle, productos, add subtotal a pedidos, detalle_pedido, + gestión rutas + caja + asignaciones |
| `2026_06_27_*` | Actualizaciones (4) | Renombrar estados, agregar Devolucion, En movimiento, columnas |
| `2026_07_06_*` | Última migración | Remove hora_estimada |

### Detalle de las Migraciones Más Importantes

**`usuarios`** — Tabla personalizada (no usa la tabla `users` de Laravel):
```php
Schema::create('usuarios', function (Blueprint $table) {
    $table->id('id_usuario');
    $table->string('nombre', 100);
    $table->string('apellido', 100);
    $table->string('email', 180)->unique();
    $table->string('password_hash', 255);
    $table->string('telefono', 20);
    $table->unsignedBigInteger('id_estado_usuario');
    $table->foreign('id_estado_usuario')->references('id_estado_usuario')->on('estados_usuario');
    $table->datetime('fecha_creacion')->nullable();
    $table->datetime('fecha_bloqueo')->nullable();
    $table->datetime('ultimo_acceso')->nullable();
    $table->rememberToken();
});
```

**`pedidos`**:
```php
Schema::create('pedidos', function (Blueprint $table) {
    $table->id('id_pedido');
    $table->foreignId('id_farmacia')->constrained('farmacias', 'id_farmacia');
    $table->foreignId('id_usuario')->constrained('usuarios', 'id_usuario');
    $table->foreignId('id_estado_pedido')->constrained('estados_pedido', 'id_estado_pedido');
    $table->datetime('fecha_pedido')->nullable();
    $table->text('observaciones')->nullable();
    $table->decimal('subtotal', 10, 2)->nullable();
    $table->decimal('descuento', 10, 2)->nullable();
    $table->decimal('total', 10, 2)->nullable();
    $table->string('archivo_pedido')->nullable();
});
```

**`farmacias`** — Con coordenadas geográficas:
```php
$table->decimal('latitud', 10, 7)->nullable();
$table->decimal('longitud', 10, 7)->nullable();
```

**`permisos`** — Unique compuesto:
```php
$table->unique(['id_modulo', 'id_accion']);
```

**Migraciones recientes (Julio 2026):**
- `2026_06_27_142725`: Renombra estados pedido (Aprobado → En preparacion, Despachado → En ruta), elimina Cancelado
- `2026_06_27_142941`: Agrega estado "Devolucion" a despachos, renombra "Entregado" → "Entregado completo"
- `2026_06_27_142941`: Agrega estado "En movimiento" a vehículos
- `2026_06_27_143021`: Agrega columna `hora_llegada` a `control_ruta_paradas`
- `2026_07_06_000001`: Elimina columna `hora_estimada` de rutas (ya no se usa)

### Tablas creadas (52 en total)

| # | Tabla | Tipo | Descripción |
|---|-------|------|-------------|
| 1-3 | password_resets, failed_jobs, personal_access_tokens | Sistema | Laravel defaults |
| 4-19 | estados_usuario, roles, modulos, acciones, tablas_sistema, estados_pedido, estados_repartidor, estados_vehiculo, estados_despacho, estados_ruta, estados_caja, estados_detalle, licencias, extensiones_ci, marcas, capacidades, tipos_incidencia, tipos_evidencia, cargos | Catálogo | 19 catálogos |
| 20-29 | usuarios, farmacias, modelos, repartidores, vehiculos, rutas, productos, pedidos, detalle_pedido, planificacion_rutas | Entidad Core | 10 tablas de negocio |
| 30-37 | contactos_farmacia, usuario_roles, permisos, rol_permiso, ruta_paradas, control_rutas, despachos, control_ruta_paradas, asignaciones_vehiculo_repartidor | Junction | 9 tablas pivote |
| 38-45 | historial_estado_pedido, historial_estado_repartidor, historial_estado_vehiculo, historial_estado_despacho, historial_estado_ruta, auditorias, sesiones_usuario, incidencias, evidencias_entrega | Historial | 9 tablas de auditoría |
| 46-52 | caja_repartidor, gastos_operativos + 5 más | Finanzas | 7 tablas de caja |

## 2.2 Seeders (3 archivos)

### `CatalogoSeeder.php` (316 líneas)

Siembra **22 catálogos** + **matriz de permisos RBAC completa**.

**Roles y sus permisos**:

| Rol | ID | Permisos | Cantidad |
|-----|----|----------|----------|
| **Administrador** | 1 | TODOS los permisos | 63 |
| **Supervisor** | 2 | Acceso completo a operaciones, SIN usuarios/roles | 48 |
| **Operador** | 3 | Operaciones básicas SIN seguridad/reportes avanzados | 31 |
| **Repartidor** | 4 | Solo Dashboard, Rutas (acceder/listar), Control Rutas (acceder/listar/registrar-llegada), Despachos (acceder/listar) | 7 |

**Matriz completa de módulos y acciones**:

| Módulo | Acciones |
|--------|----------|
| Dashboard | acceder |
| Reportes | acceder, listar |
| Usuarios | acceder, listar, crear, editar, eliminar, asignar-roles |
| Roles | acceder, listar, crear, editar, eliminar, asignar-permisos |
| Farmacias | acceder, listar, crear, editar, eliminar, gestionar-contactos |
| Productos | acceder, listar, crear, editar, eliminar |
| Pedidos | acceder, listar, crear, editar, eliminar, cambiar-estado |
| Logística | acceder, planificar, tracking |
| Caja Diaria | acceder, listar, crear, cerrar |
| Repartidores | acceder, listar, crear, editar, eliminar, cambiar-estado |
| Vehículos | acceder, listar, crear, editar, eliminar, cambiar-estado |
| Rutas | acceder, listar, crear, editar, eliminar, gestionar-paradas |
| Control Rutas | acceder, listar, crear, editar, eliminar, registrar-llegada |
| Despachos | acceder, listar, crear, editar, eliminar, cambiar-estado, gestionar-incidencias, gestionar-evidencias |

**Catálogos adicionales**:
- 5 marcas: Toyota, Suzuki, Nissan, Mitsubishi, Kia
- 10 modelos: Hilux, Land Cruiser, Vitara, Carry, Frontier, NP300, L200, Montero Sport, K2500, Bongo
- 5 capacidades: 500, 1000, 2000, 3000, 5000 kg
- 9 extensiones CI: LP, CBBA, SC, OR, PT, TJ, CH, BE, PD
- 5 licencias: A, B, C, P, Profesional
- 6 tipos incidencia: Retraso, Producto dañado, Dirección incorrecta, Cliente ausente, Fallo mecánico, Otro
- 3 tipos evidencia: Foto, Firma digital, Documento adjunto
- 5 cargos: Gerente, Administrador, Farmacéutico, Recepcionista, Almacenero

### `DemoDataSeeder.php` (47 líneas)

Crea 4 usuarios demo con password `123456`:

| Nombre | Email | Rol |
|--------|-------|-----|
| Admin Sistema | admin@distribuidora.com | Administrador |
| Carlos Mendoza | supervisor@distribuidora.com | Supervisor |
| Lucia Paredes | operador@distribuidora.com | Operador |
| Pedro Quispe | repartidor@distribuidora.com | Repartidor |

Además crea el registro en la tabla `repartidores` para Pedro Quispe.

### `DatabaseSeeder.php`

```php
$this->call([CatalogoSeeder::class, DemoDataSeeder::class]);
```

---

# 3. DIRECTORIO `routes/` — Definición de Rutas (4 archivos)

## `routes/api.php` (166 líneas) — Todas las rutas de la API

### Estructura General

```php
// Públicas (sin autenticación)
POST /api/auth/login
POST /api/auth/register

// Autenticadas (middleware auth:sanctum)
//  → Middleware permission:Modulo,accion en cada ruta
//  → Resource routes auto-mapean a CRUD
```

### Todas las Rutas Agrupadas por Módulo

#### Auth (2 rutas autenticadas):
```
POST /api/auth/logout
GET  /api/auth/user
```

#### Usuarios (6 rutas):
```
GET|POST   /api/usuarios                    → UsuarioController@index|store
GET|PUT|DELETE /api/usuarios/{usuario}      → show|update|destroy
PUT        /api/usuarios/{id}/bloquear      → bloquear
GET|POST   /api/usuarios/{usuario}/roles    → roles|assignRoles
```

#### Roles (4 rutas):
```
GET|POST   /api/roles                       → RolController@index|store
GET|PUT|DELETE /api/roles/{rol}             → show|update|destroy
GET|POST   /api/roles/{rol}/permisos        → permisos|assignPermisos
```

#### Farmacias (8 rutas):
```
GET|POST|PUT|DELETE /api/farmacias                    → CRUD
GET|POST|PUT|DELETE /api/farmacias/{id}/contactos     → CRUD contactos
```

#### Productos (5 rutas):
```
GET|POST|PUT|DELETE /api/productos → CRUD
```

#### Pedidos (12 rutas):
```
GET|POST|PUT|DELETE /api/pedidos                      → CRUD
POST /api/pedidos/extraer-pdf                         → extraerPdf
POST /api/pedidos/{id}/cambiar-estado                 → cambiarEstado
POST /api/pedidos/{id}/subir-archivo                  → subirArchivo
GET  /api/pedidos/{id}/descargar-archivo              → descargarArchivo
GET|POST|PUT|DELETE /api/pedidos/{id}/detalles         → CRUD detalles
PUT  /api/pedidos/{id}/detalles/{det}/entrega          → actualizarEntrega
```

#### Repartidores (8 rutas):
```
GET|POST|PUT|DELETE /api/repartidores                  → CRUD
POST /api/repartidores/{id}/cambiar-estado             → cambiarEstado
GET|POST|DELETE /api/repartidores/{id}/asignacion-vehiculo → asignación
```

#### Vehículos (4 rutas):
```
GET|POST|PUT|DELETE /api/vehiculos                     → CRUD
POST /api/vehiculos/{id}/cambiar-estado                → cambiarEstado
```

#### Rutas (8 rutas):
```
GET|POST|PUT|DELETE /api/rutas                         → CRUD
GET|POST|PUT|DELETE /api/rutas/{id}/paradas            → CRUD paradas
```

#### Control Rutas (5 rutas):
```
GET|POST|PUT|DELETE /api/controles-ruta                → CRUD
POST /api/controles-ruta/{id}/registrar-llegada        → registrarLlegada
```

#### Despachos (16 rutas):
```
GET|POST|PUT|DELETE /api/despachos                     → CRUD
POST /api/despachos/{id}/cambiar-estado                → cambiarEstado
GET|POST|PUT|DELETE /api/despachos/{id}/incidencias    → CRUD incidencias
GET|POST|PUT|DELETE /api/despachos/{id}/evidencias     → CRUD evidencias
```

#### Centro Logístico (11 rutas, todas bajo `/api/logistica`):
```
GET  /api/logistica/pedidos-pendientes
GET  /api/logistica/repartidores-disponibles
POST /api/logistica/planificacion/borrador
POST /api/logistica/planificacion/confirmar
GET  /api/logistica/rutas-activas
GET  /api/logistica/rutas-activas/{id}
POST /api/logistica/rutas-activas/{id}/entrega-completada
POST /api/logistica/rutas-activas/{id}/entrega-fallida
POST /api/logistica/rutas-activas/{id}/devolucion
POST /api/logistica/rutas-activas/{id}/regreso-almacen
POST /api/logistica/rutas-activas/{id}/registrar-llegada-parada
POST /api/logistica/rutas-activas/{id}/auto-completar-parada
```

#### Reportes (7 rutas, bajo `/api/reportes`):
```
GET /api/reportes/resumen
GET /api/reportes/pedidos-por-estado
GET /api/reportes/despachos-por-estado
GET /api/reportes/pedidos-por-dia
GET /api/reportes/repartidores-por-estado
GET /api/reportes/vehiculos-por-estado
GET /api/reportes/incidencias-por-tipo
```

#### Caja Diaria (6 rutas, bajo `/api/caja`):
```
POST /api/caja/abrir
POST /api/caja/ingreso
POST /api/caja/gasto
PUT  /api/caja/cerrar/{id}
GET  /api/caja/dia
GET  /api/caja/resumen
```

#### Catálogos (21 rutas GET + 3 CRUD dinámicos, bajo `/api/catalogos`):
```
GET /api/catalogos/modelos-por-marca/{id}
GET /api/catalogos/estados-usuario, roles, modulos, acciones, tablas-sistema
GET /api/catalogos/estados-pedido, estados-repartidor, estados-vehiculo, estados-despacho
GET /api/catalogos/extensiones-ci, licencias, marcas, modelos, capacidades
GET /api/catalogos/tipos-incidencia, tipos-evidencia, cargos
POST|PUT|DELETE /api/catalogos/{nombre}  → CRUD dinámico (permiso: Usuarios)
```

### Sistema de Middleware en Rutas

**`Route::apiResource()`** genera automáticamente 5 rutas RESTful. El middleware `permission:Modulo` auto-detecta la acción según el método HTTP.

**Rutas con acción explícita:**
```php
Route::post('pedidos/{pedido}/cambiar-estado', [PedidoController::class, 'cambiarEstado'])
    ->middleware('permission:Pedidos,cambiar-estado');
```

**Rutas del Centro Logístico** (NO tienen middleware `permission` explícito — se asume que la autenticación sanctum es suficiente para el módulo logístico).

---

## `routes/web.php` (18 líneas)

```php
Route::get('/', function () {
    return view('welcome');
});
```
Solo sirve la página de bienvenida de Laravel. Toda la aplicación real está en el frontend SPA separado.

## `routes/channels.php` (18 líneas)

Canal de broadcasting por defecto: `App.Models.User.{id}`.

## `routes/console.php` (19 líneas)

Comando Artisan `inspire` que muestra una frase inspiradora.

---

# 4. DIRECTORIO `storage/` — Archivos del Sistema

## Estructura

```
storage/
├── app/
│   ├── .gitignore
│   └── public/
│       ├── .gitignore
│       ├── evidencias/          → Vacío (destinado a fotos/documentos de entregas)
│       └── pedidos/
│           └── 12/              → Vacío (destinado a PDFs de pedidos, por ID)
├── framework/
│   ├── cache/data/              → Cache de Laravel (2 archivos de cache activos)
│   ├── sessions/                → 14 sesiones activas (Junio 2026)
│   ├── testing/                 → Vacío
│   └── views/                   → 3 vistas Blade compiladas
└── logs/
    └── laravel.log              → ~2.7 MB de logs
```

## `storage/app/public/` — Archivos Subidos por Usuarios

**Directorio `evidencias/`**: Aquí se almacenan los archivos de evidencia subidos desde el módulo de Despachos (fotos, documentos). **Actualmente vacío** — no hay entregas con evidencias aún.

**Directorio `pedidos/{id}/`**: Aquí se almacenan los PDFs de pedidos subidos. El directorio `12/` existe (posiblemente de una prueba) pero está **vacío**.

⚠️ **Problema conocido**: El symlink `public/storage` → `storage/app/public` **NO está creado**. Es un directorio vacío normal. Para que los archivos subidos sean accesibles web, se debe ejecutar:
```bash
php artisan storage:link
```

## `storage/framework/cache/data/` — Cache del Sistema

2 archivos de cache activos con expiración `1783439769` (≈ 7 Julio 2026):
- Contienen valores enteros simples (probablemente resultados de consultas cacheadas)

## `storage/framework/sessions/` — Sesiones de Usuario

14 archivos de sesión PHP (todos ~0.17 KB), con fechas entre **11 y 26 de Junio 2026**. Representan sesiones de usuarios que interactuaron con la aplicación web de Laravel (la vista welcome).

## `storage/framework/views/` — Vistas Blade Compiladas

3 archivos PHP compilados:
- `7e8f9b...26.php` — 18.24 KB (la más grande, probablemente la vista welcome)
- `97610c...91.php` — 0.47 KB (pequeña, quizás un partial)
- `c3b42c...45.php` — 6.74 KB (mediana)

## `storage/logs/laravel.log` — Registro de Errores (~2.7 MB)

Contiene errores desde Junio 2026. Los errores más comunes:

1. **`SQLSTATE[HY000] [1049] Unknown database 'pw3c_distribuidora'`** — La base de datos MySQL no existe o no está creada
2. **`SQLSTATE[HY000] [14] unable to open database file`** — Intento de usar SQLite con ruta inválida
3. **`SQLSTATE[HY000]: General error: 1 no such table: rols`** — Seeder ejecutado antes de las migraciones

---

# 5. FLUJO DE FUNCIONAMIENTO DEL SISTEMA

## 5.1 Ciclo de Vida de un Pedido

```
1. CREACIÓN
   ← Frontend: formulario nuevo pedido
   → POST /api/pedidos { datos }
   → PedidoController@store
   → Crea Pedido (estado: 1 = Pendiente)
   → Crea HistorialEstadoPedido

2. EXTRACCIÓN DE PDF (opcional)
   ← Frontend: sube PDF de factura
   → POST /api/pedidos/extraer-pdf (archivo)
   → PedidoController@extraerPdf
   → PedidoPdfExtractor@extraer
   → Retorna productos detectados con confianza

3. GESTIÓN DE DETALLE
   ← Frontend: agrega/quita productos
   → POST/PUT/DELETE /api/pedidos/{id}/detalles
   → DetallePedidoController
   → Recalcula subtotal y total del pedido

4. CAMBIO A "EN PREPARACION"
   ← Frontend: mueve a columna "En preparacion"
   → POST /api/pedidos/{id}/cambiar-estado { estado: 2 }
   → PedidoController@cambiarEstado
   → Cierra historial anterior, crea nuevo

5. PLANIFICACIÓN (Centro Logístico)
   ← Frontend: selecciona pedidos, repartidor, vehículo
   → POST /api/logistica/planificacion/borrador (opcional)
   → POST /api/logistica/planificacion/confirmar
   → CentroLogisticoController@confirmarRuta
   → TRANSACTION: crea Ruta, ControlRuta, Paradas, Despachos
   → Dispara evento RutaIniciada
     → Listeners: Despacho→En camino, Pedido→En ruta, Repartidor→En ruta, Vehículo→En ruta

6. SEGUIMIENTO EN TIEMPO REAL
   ← App Android envía GPS a Firebase RTDB cada 1s
   ← Frontend web lee ubicaciones desde Firebase
   ← Muestra repartidores en mapa Mapbox
   → Acciones por parada:
     → POST /logistica/rutas-activas/{id}/entrega-completada
       → Dispara EntregaCompletada
         → Listeners: Despacho→Entregado, Pedido→Entregado
     → POST /logistica/rutas-activas/{id}/entrega-fallida
       → Dispara EntregaFallida
         → Listeners: Despacho→Fallido, Pedido→Fallido
     → POST /logistica/rutas-activas/{id}/devolucion
       → Dispara DevolucionProducto
         → Listeners: Despacho→Devuelto, Pedido→Entregado

7. CIERRE DE RUTA
   ← Cuando todas las paradas están completadas
     → autoCompletarParada detecta "todas completadas"
     → Dispara RepartidorRegresa
     → Listeners: Repartidor→Disponible, Vehículo→Operativo
     → ControlRuta→Finalizada

8. CAJA DIARIA (por repartidor)
   ← Frontend: abre caja
   → POST /api/caja/abrir { repartidor_id }
   ← Frontend: registra ingresos/gastos durante el día
   → POST /api/caja/ingreso, POST /api/caja/gasto
   ← Frontend: cierra caja
   → PUT /api/caja/cerrar/{id}
```

## 5.2 Sistema de Seguridad (RBAC)

```
PETICIÓN HTTP
    │
    ▼
auth:sanctum middleware
    │
    ├── ¿Token válido? ─── No → 401 Unauthenticated
    │
    ▼
AppServiceProvider: ¿id_estado_usuario === 1? ─── No → Token rechazado
    │
    ▼
permission:Modulo,Accion middleware (CheckPermission)
    │
    ├── ¿Mapear método HTTP? → index→listar, store→crear, etc.
    │
    ▼
¿$user->hasPermission($modulo, $accion)?
    │
    ├── Recorre $user->roles
    │   └── Por cada rol, recorre $rol->permisos
    │       └── Compara $permiso->modulo->nombre === $modulo
    │           && $permiso->accion->nombre === $accion
    │
    ├── Sí → CONTINUA a la ruta
    └── No → 403 Forbidden
```

---

# 6. DIAGRAMA DE FLUJO DE DATOS

```
[MySQL DB]
    │
    ├── Lectura/Escritura vía Eloquent ORM
    │
[Laravel API]
    │
    ├── AuthController ─── login/register → Sanctum tokens
    ├── PedidoController ─── CRUD + PDF extraction
    ├── CentroLogisticoController ─── Planificación + Tracking
    │   └── Dispara eventos: RutaIniciada, EntregaCompletada, etc.
    ├── DespachoController ─── CRUD + Incidencias + Evidencias
    ├── ReporteController ─── Estadísticas agregadas
    ├── CajaController ─── Gestión financiera diaria
    ├── Seguridad ─── Usuarios, Roles, Permisos
    └── Catálogos ─── Referencias (marcas, modelos, estados, etc.)
        │
        ▼
[Eventos del Dominio]
    │
    ├── RutaIniciada → Actualiza: Despachos, Pedidos, Repartidores, Vehículos
    ├── EntregaCompletada → Actualiza: Despacho, Pedido, Registra llegada
    ├── EntregaFallida → Actualiza: Despacho, Pedido, Registra llegada
    ├── DevolucionProducto → Actualiza: Despacho, Pedido, Registra llegada
    └── RepartidorRegresa → Actualiza: Repartidor, Vehículo, Cierra ruta
        │
        ▼
[Firebase Realtime DB]
    │
    ├── /usuarios/{id}/info/ ─── Datos del repartidor
    ├── /usuarios/{id}/ubicacion/ ─── GPS en tiempo real
    └── /geocercas/{id}/ ─── Polígonos de geocercas
        │
        ▼
[App Android + Frontend Web]
    ├── App escribe ubicación GPS cada 1s
    ├── Frontend lee ubicación y muestra en mapa Mapbox
    └── App valúa geocercas con algoritmo Ray-Casting
```

---

# 7. RESUMEN TÉCNICO

| Aspecto | Valor |
|---------|-------|
| **Versión Laravel** | 9.x |
| **PHP requerido** | ^8.0 |
| **Base de datos** | MySQL 8+ |
| **Tablas totales** | 52 |
| **Controladores** | 26 |
| **Modelos** | 39 |
| **Eventos** | 5 |
| **Listeners** | 5 (suscriptores) |
| **Middleware** | 9 |
| **Servicios** | 2 |
| **Seeders** | 3 (22 catálogos + 4 usuarios demo) |
| **Migraciones** | 65 (crean 52 tablas) |
| **Rutas API** | ~80 endpoints |
| **Autenticación** | Sanctum (tokens) |
| **Autorización** | RBAC personalizado (CheckPermission) |
| **Filesystem** | Local (storage/app/public) |
| **Cache** | File-based |
| **Sesiones** | File-based |
| **Logs** | Single file (laravel.log) |
| **Colas** | Sync (síncrono, por defecto) |
| **Rate limiting** | 60 requests/min |
