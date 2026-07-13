# SDD: Sistema de Gestión de Distribuidora Farmacéutica

---

## 1. CONTROL DEL DOCUMENTO Y METADATOS

### 1.1 Identificación del Componente

| Campo | Valor |
|---|---|
| **ID** | `PW3C-DIST-SDD-001` |
| **Nombre** | Módulo Backend — API REST Laravel |
| **Repositorio** | `pw3c-distribuidora` |

### 1.2 Historial de Versiones

| Versión | Fecha | Autor | Descripción |
|---|---|---|---|
| 1.0 | 2026-07-13 | — | Versión inicial basada en análisis de código, base de datos, endpoints y tests |

### 1.3 Estado del Spec

`Aprobado para Desarrollo`

### 1.4 Dependencias y Bloqueos

| Dependencia | Tipo | Versión |
|---|---|---|
| PHP | Lenguaje | ^8.2 |
| Laravel | Framework | ^9.0 |
| MySQL | Base de datos (prod/dev) | 8+ |
| SQLite | Base de datos (testing) | — |
| Pest PHP | Testing | ^1.0 |
| Spatie Permission | Roles/Permisos | ^5.0 |
| Laravel Sanctum | Autenticación API | ^3.0 |

> Sin bloqueos externos conocidos. El módulo es autocontenido.

---

## 2. DESCRIPCIÓN GENERAL Y CONTEXTO FUNCIONAL

### 2.1 Propósito del Módulo

Gestionar el ciclo completo de distribución farmacéutica: desde la creación de pedidos por farmacias, la planificación de rutas de reparto, la ejecución de entregas con registro de evidencias, hasta el cierre de caja diaria y generación de reportes operativos.

### 2.2 Actores y Permisos

#### Roles del sistema (Spatie Permission)

| Rol | Descripción |
|---|---|
| **Administrador** | Acceso total a todas las funcionalidades del sistema |
| **Supervisor** | Gestión de rutas, pedidos, reportes; sin acceso a usuarios ni vehículos |
| **Operador** | Creación y gestión de pedidos, farmacias, productos |
| **Repartidor** | Ejecución de rutas: iniciar, registrar llegada, completar entrega, reportar incidencias, subir evidencias |

#### Matriz de permisos granulares por módulo

| Módulo | Permisos disponibles |
|---|---|
| Dashboard | `acceder` |
| Usuarios | `acceder`, `listar`, `crear`, `editar`, `eliminar`, `asignar-roles` |
| Roles | `acceder`, `listar`, `crear`, `editar`, `eliminar`, `asignar-permisos` |
| Farmacias | `acceder`, `listar`, `crear`, `editar`, `eliminar`, `gestionar-contactos` |
| Productos | `acceder`, `listar`, `crear`, `editar`, `eliminar` |
| Pedidos | `acceder`, `listar`, `crear`, `editar`, `eliminar`, `cambiar-estado` |
| Repartidores | `acceder`, `listar`, `crear`, `editar`, `eliminar`, `cambiar-estado` |
| Vehículos | `acceder`, `listar`, `crear`, `editar`, `eliminar`, `cambiar-estado` |
| Rutas | `acceder`, `listar`, `crear`, `editar`, `eliminar`, `gestionar-paradas` |
| Control Rutas | `acceder`, `listar`, `crear`, `editar`, `eliminar`, `registrar-llegada` |
| Despachos | `acceder`, `listar`, `crear`, `editar`, `eliminar`, `cambiar-estado`, `gestionar-incidencias`, `gestionar-evidencias` |
| Reportes | `acceder`, `listar` |
| Caja Diaria | `acceder`, `listar`, `crear`, `editar`, `cerrar` |
| Documentos | `acceder` |

#### Permisos asignados por rol

| Rol | Permisos |
|---|---|
| **Administrador** | Todos los permisos |
| **Supervisor** | Dashboard, Farmacias (acceder/listar/crear/editar), Productos (acceder/listar/crear/editar), Pedidos (todos menos eliminar), Repartidores (acceder/listar), Vehículos (acceder/listar), Rutas (todos), Control Rutas (todos menos registrar-llegada), Despachos (acceder/listar/crear/editar/cambiar-estado), Reportes (todos), Caja Diaria (acceder/listar) |
| **Operador** | Dashboard, Farmacias (acceder/listar), Productos (acceder/listar), Pedidos (acceder/listar/crear/cambiar-estado), Repartidores (acceder/listar), Vehículos (acceder/listar), Rutas (acceder/listar), Control Rutas (acceder/listar), Despachos (acceder/listar/crear/cambiar-estado), Reportes (todos) |
| **Repartidor** | Dashboard, Despachos (acceder/listar/cambiar-estado/gestionar-incidencias/gestionar-evidencias), Control Rutas (acceder/listar/registrar-llegada) |

> **⚠️ Riesgo conocido:** Los permisos de Spatie existen en BD y están asignados a los roles, pero **ningún endpoint los verifica mediante middleware** (`role:`, `permission:`, `role_or_permission:`). Todos los endpoints autenticados solo exigen `auth:sanctum`. Ver sección 10.

### 2.3 Alcance

| In Scope | Out of Scope |
|---|---|
| CRUD completo de 10 entidades de negocio | App móvil nativa (solo API) |
| Hoja de ruta + workflow de despacho | Geolocalización en tiempo real |
| Subida de evidencias (foto/firma/sello) | Dashboard analítico avanzado |
| Gestión de incidencias | Pasarela de pagos en línea |
| Caja diaria + gastos operativos | Notificaciones push |
| Extracción de datos desde PDF | Chat / mensajería interna |
| 7 endpoints de reportes | Módulo de inventario / stock |
| Autenticación con Sanctum + roles | Integración con sistemas contables |

---

## 3. ARQUITECTURA Y CONTRATO TECNOLÓGICO

### 3.1 Stack de Implementación

| Capa | Tecnología |
|---|---|
| Backend | Laravel 9 + PHP 8.2 |
| API | RESTful con Sanctum (token-based) |
| Base de datos (prod/dev) | MySQL 8+ |
| Base de datos (testing) | SQLite en memoria |
| Testing | Pest PHP + PHPUnit 9 |
| Roles y permisos | Spatie Permission v5 |
| Documentación API | Scribe (endpoint `/docs`) |
| Almacenamiento de archivos | Disco `public` (local) configurable a S3 |

### 3.2 Diagrama de Arquitectura / Flujo

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENTE (SPA / App)                      │
│                  Authorization: Bearer <token>                   │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                    ┌──────▼───────┐
                    │  Laravel API  │
                    │  auth:sanctum │
                    └──────┬───────┘
                           │
              ┌────────────┼────────────┐
              ▼            ▼            ▼
       ┌──────────┐ ┌──────────┐ ┌──────────┐
       │Controllers│ │   API    │ │  Events  │
       │ (18)     │ │Response  │ │  (5)     │
       │          │ │Helpers   │ │          │
       └────┬─────┘ └──────────┘ └────┬─────┘
            │                         │
            ▼                         ▼
       ┌──────────┐           ┌───────────────┐
       │ Services │           │  Listeners    │
       │ (2)     │            │  (5 subscribers)│
       └────┬─────┘           └───────┬───────┘
            │                         │
            └──────────┬──────────────┘
                       ▼
              ┌────────────────┐
              │    Eloquent     │
              │    ORM / Models │
              └───────┬────────┘
                      ▼
              ┌────────────────┐
              │    MySQL /      │
              │  SQLite (:memory:)│
              └────────────────┘
```

#### Flujo principal: Ciclo de entrega

```
Usuario (admin)          Repartidor               Sistema
    │                        │                       │
    ├─ POST /api/pedidos ────┤                       │
    │                        │                       ├─ Crea Pedido (estado: pendiente)
    │                        │                       │
    ├─ POST /api/hojas-ruta ─┤                       │
    │                        │                       ├─ Crea HojaRuta + Despachos
    │                        │                       │
    ├─ POST /.../iniciar ────┤                       │
    │                        │                       ├─ estado_ruta → en_progreso
    │                        │                       ├─ Evento: RutaIniciada → 4 listeners
    │                        │                       │
    ├─ POST /.../registrar-llegada ──┤               │
    │                        │                       ├─ Registra hora_llegada en detalle
    │                        │                       │
    ├─ POST /.../entrega-completada ──┤             │
    │                        │                       ├─ Evento: EntregaCompletada → 3 listeners
    │                        │                       │
    ├─ POST /.../regreso-almacen ──┤                │
    │                        │                       ├─ Evento: RepartidorRegresa → 2 listeners
```

### 3.3 Patrones de Diseño Aplicados

| Patrón | Aplicación |
|---|---|
| **REST API** | Todos los endpoints siguen principios RESTful |
| **Active Record** | Eloquent ORM — cada modelo mapea a una tabla |
| **Event-Driven** | 5 eventos de dominio notifican cambios de estado |
| **Observer / Subscriber** | Listeners agrupados en subscribers registrados vía `Event::subscribe()` |
| **Repository-lite** | Servicios encapsulan lógica de asignación (`AsignacionVehiculoService`) |
| **DTO / Response Helper** | `ApiController` con `jsonResponse()`, `errorResponse()`, `paginatedResponse()` |
| **Factory Pattern** | Pest factories para creación de datos de prueba |

---

## 4. CONTRATOS DE INTERFAZ Y MODELO DE DATOS

### 4.1 Esquema de Persistencia / Base de Datos

#### Diagrama entidad-relación

```
usuarios (1) ──< repartidores (1)
usuarios (1) ──< pedidos (*)
farmacias (1) ──< contactos_farmacia (*)
farmacias (1) ──< pedidos (*)
repartidores (1) ──< asignaciones_vehiculo_repartidor (*)
vehiculos (1) ──< asignaciones_vehiculo_repartidor (*)
repartidores (1) ──< hojas_ruta (*)
vehiculos (1) ──< hojas_ruta (*)
repartidores (1) ──< caja_repartidor (*)
hojas_ruta (1) ──< hoja_ruta_detalles (*)
hojas_ruta (1) ── caja_repartidor (1) [id_hoja_ruta unique]
pedidos (1) ──< detalle_pedido (*)
pedidos (1) ──< hoja_ruta_detalles (*)
pedidos (1) ── despacho (1) [id_pedido unique]
productos (1) ──< detalle_pedido (*)
hoja_ruta_detalles (1) ──< despachos (*)
despachos (1) ──< evidencias_entrega (*)
despachos (1) ──< incidencias (*)
caja_repartidor (1) ──< gastos_operativos (*)
```

#### Tablas del sistema (22 totales — 16 negocio + 6 infraestructura)

| # | Tabla | PK | FK | Uniques | Propósito |
|---|---|---|---|---|---|
| 1 | `usuarios` | `id_usuario` | — | `email` | Autenticación + roles Spatie |
| 2 | `farmacias` | `id_farmacia` | — | `email` | Destinos de pedidos |
| 3 | `contactos_farmacia` | `id_contacto` | `id_farmacia → farmacias` | — | Personas de contacto |
| 4 | `repartidores` | `id_repartidor` | `id_usuario → usuarios` | `id_usuario`, `ci`, `firebase_uid` | Personal de entregas |
| 5 | `vehiculos` | `id_vehiculo` | — | `placa` | Flota vehicular |
| 6 | `asignaciones_vehiculo_repartidor` | `id_asignacion` | `id_repartidor → repartidores`, `id_vehiculo → vehiculos` | — | Asignación activa |
| 7 | `productos` | `id_producto` | — | — | Catálogo de productos |
| 8 | `pedidos` | `id_pedido` | `id_farmacia → farmacias`, `id_usuario → usuarios` | — | Órdenes de compra |
| 9 | `detalle_pedido` | `id_detalle` | `id_pedido → pedidos`, `id_producto → productos` | — | Items del pedido |
| 10 | `hojas_ruta` | `id_hoja_ruta` | `id_repartidor → repartidores`, `id_vehiculo → vehiculos` | — | Plan de ruta |
| 11 | `hoja_ruta_detalles` | `id_detalle` | `id_hoja_ruta → hojas_ruta`, `id_pedido → pedidos` | — | Paradas ordenadas |
| 12 | `despachos` | `id_despacho` | `id_pedido → pedidos`, `id_detalle_hoja_ruta → hoja_ruta_detalles` | `id_pedido` | Entrega individual |
| 13 | `evidencias_entrega` | `id_evidencia` | `id_despacho → despachos` | — | Fotos, firmas, sellos |
| 14 | `incidencias` | `id_incidencia` | `id_despacho → despachos` | — | Problemas en entrega |
| 15 | `caja_repartidor` | `id_caja` | `id_hoja_ruta → hojas_ruta`, `id_repartidor → repartidores` | `id_hoja_ruta` | Control de efectivo |
| 16 | `gastos_operativos` | `id_gasto` | `id_caja → caja_repartidor` | — | Gastos en ruta |
| 17-22 | `personal_access_tokens`, `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions` | — | — | — | Infraestructura (Sanctum + Spatie) |

### 4.2 Definición de Endpoints (API Spec)

#### Convenciones generales

| Aspecto | Especificación |
|---|---|
| **Base URL** | `/api` |
| **Autenticación** | Sanctum token (`Authorization: Bearer <token>`) |
| **Formato request** | `application/json` (multipart para archivos) |
| **Formato response exitoso** | `{ "success": true, "message": "...", "data": {...} }` |
| **Formato response paginado** | `{ "success": true, "data": [...], "meta": { "current_page": N, "last_page": N, "per_page": N, "total": N } }` |
| **Formato response error** | `{ "success": false, "message": "...", "errors": {...} }` |
| **Códigos HTTP** | 200 OK, 201 Created, 204 No Content, 400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found, 409 Conflict, 422 Unprocessable Entity, 500 Server Error |

#### 2.627 — Autenticación (2 públicos, 2 autenticados)

| Método | Ruta | Middleware | Descripción |
|---|---|---|---|
| POST | `/api/auth/login` | — | Login con email/password. Retorna `{ user, token, permisos }` |
| POST | `/api/auth/register` | — | Registro + asignación rol Operador. Retorna `{ user, token, permisos }` |
| POST | `/api/auth/logout` | `auth:sanctum` | Revoca token actual |
| GET | `/api/auth/user` | `auth:sanctum` | Perfil del usuario autenticado + roles + permisos |

#### 9.513 — Usuarios

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/usuarios` | Listar usuarios (paginado, filtrar por `?search=`, `?estado=`) |
| POST | `/api/usuarios` | Crear usuario + asignar roles |
| GET | `/api/usuarios/{id}` | Mostrar usuario (con roles, repartidor) |
| PUT | `/api/usuarios/{id}` | Actualizar usuario |
| DELETE | `/api/usuarios/{id}` | Eliminar usuario (409 si tiene relaciones) |
| PUT | `/api/usuarios/{id}/bloquear` | Bloquear usuario (estado → bloqueado) |

#### 9.515 — Farmacias y Contactos

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/farmacias` | Listar farmacias (paginado) |
| POST | `/api/farmacias` | Crear farmacia |
| GET | `/api/farmacias/{id}` | Mostrar farmacia (con contactos) |
| PUT | `/api/farmacias/{id}` | Actualizar farmacia |
| DELETE | `/api/farmacias/{id}` | Eliminar farmacia (409 si tiene relaciones) |
| GET | `/api/farmacias/{id}/contactos` | Listar contactos de farmacia |
| POST | `/api/farmacias/{id}/contactos` | Crear contacto |
| PUT | `/api/farmacias/{id}/contactos/{id}` | Actualizar contacto |
| DELETE | `/api/farmacias/{id}/contactos/{id}` | Eliminar contacto |

#### 9.521 — Productos

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/productos` | Listar productos (paginado) |
| POST | `/api/productos` | Crear producto |
| GET | `/api/productos/{id}` | Mostrar producto |
| PUT | `/api/productos/{id}` | Actualizar producto |
| DELETE | `/api/productos/{id}` | Eliminar producto (409 si tiene relaciones) |

#### 9.526 — Pedidos y Detalles

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/pedidos` | Listar pedidos (paginado, filtros por estado/farmacia/fecha) |
| POST | `/api/pedidos` | Crear pedido (asigna usuario autenticado) |
| GET | `/api/pedidos/{id}` | Mostrar pedido (con detalles, despacho, ruta) |
| PUT | `/api/pedidos/{id}` | Actualizar pedido |
| DELETE | `/api/pedidos/{id}` | Eliminar pedido |
| POST | `/api/pedidos/{id}/cambiar-estado` | Cambiar estado del pedido |
| POST | `/api/pedidos/{id}/subir-archivo` | Subir PDF del pedido |
| GET | `/api/pedidos/{id}/descargar-archivo` | Descargar PDF del pedido |
| POST | `/api/pedidos/extraer-pdf` | Extraer datos desde PDF subido |
| GET | `/api/pedidos/{id}/detalles` | Listar detalles del pedido |
| POST | `/api/pedidos/{id}/detalles` | Agregar detalle (recalcula totales) |
| PUT | `/api/pedidos/{id}/detalles/{id}` | Actualizar detalle (recalcula totales) |
| DELETE | `/api/pedidos/{id}/detalles/{id}` | Eliminar detalle (recalcula totales) |
| PUT | `/api/pedidos/{id}/detalles/{id}/entrega` | Actualizar cantidades entregadas |

#### 9.540 — Repartidores y Asignación Vehicular

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/repartidores` | Listar repartidores (paginado, con asignación activa) |
| POST | `/api/repartidores` | Crear repartidor (con opción de asignar vehículo) |
| GET | `/api/repartidores/{id}` | Mostrar repartidor (con asignaciones, rutas) |
| PUT | `/api/repartidores/{id}` | Actualizar repartidor |
| DELETE | `/api/repartidores/{id}` | Eliminar repartidor (409 si tiene relaciones) |
| POST | `/api/repartidores/{id}/cambiar-estado` | Cambiar estado del repartidor |
| GET | `/api/repartidores/{id}/asignacion-vehiculo` | Ver asignación vehicular actual |
| POST | `/api/repartidores/{id}/asignacion-vehiculo` | Asignar vehículo |
| DELETE | `/api/repartidores/{id}/asignacion-vehiculo` | Remover asignación vehicular |

#### 9.549 — Vehículos

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/vehiculos` | Listar vehículos (paginado) |
| POST | `/api/vehiculos` | Crear vehículo |
| GET | `/api/vehiculos/{id}` | Mostrar vehículo (con asignaciones, rutas) |
| PUT | `/api/vehiculos/{id}` | Actualizar vehículo |
| DELETE | `/api/vehiculos/{id}` | Eliminar vehículo (409 si tiene relaciones) |
| POST | `/api/vehiculos/{id}/cambiar-estado` | Cambiar estado del vehículo |

#### 9.555 — Logística / Hojas de Ruta

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/logistica/pedidos-pendientes` | Pedidos aprobados sin despachar |
| GET | `/api/logistica/repartidores-disponibles` | Repartidores activos con vehículo |
| GET | `/api/logistica/rutas-activas` | Rutas planificadas/en_progreso con avance |
| GET | `/api/hojas-ruta` | Listar hojas de ruta (paginado) |
| POST | `/api/hojas-ruta` | Crear hoja de ruta + despachos automáticos |
| GET | `/api/hojas-ruta/{id}` | Mostrar hoja de ruta (completa) |
| PUT | `/api/hojas-ruta/{id}` | Actualizar hoja de ruta |
| DELETE | `/api/hojas-ruta/{id}` | Eliminar hoja de ruta (409 si tiene relaciones) |
| POST | `/api/hojas-ruta/{id}/iniciar` | Iniciar ruta (dispara RutaIniciada) |
| POST | `/api/hojas-ruta/{id}/registrar-llegada` | Registrar hora de llegada a parada |
| POST | `/api/hojas-ruta/{id}/entrega-completada` | Marcar entrega completada (dispara EntregaCompletada) |
| POST | `/api/hojas-ruta/{id}/entrega-fallida` | Marcar entrega fallida (dispara EntregaFallida) |
| POST | `/api/hojas-ruta/{id}/devolucion` | Registrar devolución (dispara DevolucionProducto) |
| POST | `/api/hojas-ruta/{id}/regreso-almacen` | Regreso a almacén (dispara RepartidorRegresa) |

#### 9.569 — Despachos

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/despachos` | Listar despachos (paginado, filtro por estado) |
| POST | `/api/despachos` | Crear despacho (normalmente automático al crear ruta) |
| GET | `/api/despachos/{id}` | Mostrar despacho (completo con evidencias, incidencias) |
| PUT | `/api/despachos/{id}` | Actualizar despacho |
| DELETE | `/api/despachos/{id}` | Eliminar despacho (409 si tiene relaciones) |
| POST | `/api/despachos/{id}/cambiar-estado` | Cambiar estado del despacho |

#### 9.575 — Incidencias y Evidencias

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/despachos/{id}/incidencias` | Listar incidencias de un despacho |
| POST | `/api/despachos/{id}/incidencias` | Crear incidencia (con opción de subir archivo) |
| GET | `/api/despachos/{id}/incidencias/{id}` | Mostrar incidencia |
| PUT | `/api/despachos/{id}/incidencias/{id}` | Actualizar incidencia |
| DELETE | `/api/despachos/{id}/incidencias/{id}` | Eliminar incidencia |
| GET | `/api/despachos/{id}/evidencias` | Listar evidencias de un despacho |
| POST | `/api/despachos/{id}/evidencias` | Subir evidencia (foto/firma/sello) |
| GET | `/api/despachos/{id}/evidencias/{id}` | Mostrar evidencia |
| PUT | `/api/despachos/{id}/evidencias/{id}` | Actualizar evidencia (puede reemplazar archivo) |
| DELETE | `/api/despachos/{id}/evidencias/{id}` | Eliminar evidencia |

#### 9.585 — Caja Diaria

| Método | Ruta | Descripción |
|---|---|---|
| POST | `/api/caja/abrir` | Abrir caja para ruta/repartidor/día |
| POST | `/api/caja/ingreso` | Registrar ingreso en caja abierta |
| POST | `/api/caja/gasto` | Registrar gasto (con opción de comprobante) |
| PUT | `/api/caja/cerrar/{id}` | Cerrar caja (monto_final requerido) |
| GET | `/api/caja/dia` | Consultar cajas del día (filtro por fecha/repartidor) |
| GET | `/api/caja/resumen` | Resumen diario (conteo, total ingresos) |

#### 9.591 — Reportes

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/reportes/resumen` | Conteos globales del sistema |
| GET | `/api/reportes/pedidos-por-estado` | Pedidos agrupados por estado |
| GET | `/api/reportes/despachos-por-estado` | Despachos agrupados por estado |
| GET | `/api/reportes/pedidos-por-dia` | Pedidos por día (últimos N días) |
| GET | `/api/reportes/repartidores-por-estado` | Repartidores agrupados por estado |
| GET | `/api/reportes/vehiculos-por-estado` | Vehículos agrupados por estado |
| GET | `/api/reportes/incidencias-por-tipo` | Incidencias agrupadas por tipo |

### 4.3 Eventos de Dominio

El sistema utiliza **5 eventos de dominio** para orquestar cambios de estado a través de las entidades. Todos los eventos implementan `Dispatchable`, `InteractsWithSockets` y `SerializesModels`.

#### 4.3.1 RutaIniciada

| Propiedad | Tipo | Descripción |
|---|---|---|
| `$hojaRuta` | `HojaRuta` | Instancia del modelo de hoja de ruta |
| `$pedidoIds` | `array` | IDs de pedidos incluidos en la ruta |
| `$idVehiculo` | `int` | ID del vehículo asignado |

**Disparado por:** `POST /api/hojas-ruta/{id}/iniciar`

**Listeners:**

| Listener | Acción |
|---|---|
| `ActualizarEstadoPedidosListener::onRutaIniciada` | Pedidos pendientes → `aprobado` |
| `ActualizarEstadoRepartidorListener::onRutaIniciada` | Repartidor → `en_ruta` |
| `ActualizarEstadoVehiculoListener::onRutaIniciada` | Vehículo → `en_ruta` |
| `ActualizarEstadoDespachoListener::onRutaIniciada` | Operación idempotente (pendiente → pendiente) |

#### 4.3.2 EntregaCompletada

| Propiedad | Tipo | Descripción |
|---|---|---|
| `$detalle` | `HojaRutaDetalle` | Instancia del detalle de ruta (parada) |
| `$idDespacho` | `int` | ID del despacho |
| `$idPedido` | `int` | ID del pedido |

**Disparado por:** `POST /api/hojas-ruta/{id}/entrega-completada`

**Listeners:**

| Listener | Acción |
|---|---|
| `ActualizarEstadoPedidosListener::onEntregaCompletada` | Pedido → `entregado` |
| `ActualizarEstadoDespachoListener::onEntregaCompletada` | Despacho → `entregado` |
| `RegistrarLlegadaParadaListener::handleEntrega` | Registra `hora_llegada` si no existe |

#### 4.3.3 EntregaFallida

| Propiedad | Tipo | Descripción |
|---|---|---|
| `$detalle` | `HojaRutaDetalle` | Instancia del detalle de ruta (parada) |
| `$idDespacho` | `int` | ID del despacho |
| `$idPedido` | `int` | ID del pedido |
| `$motivo` | `string` | Causa de la falla (default: `''`) |

**Disparado por:** `POST /api/hojas-ruta/{id}/entrega-fallida`

**Listeners:**

| Listener | Acción |
|---|---|
| `ActualizarEstadoPedidosListener::onEntregaFallida` | Pedido revierte a `pendiente` |
| `ActualizarEstadoDespachoListener::onEntregaFallida` | Despacho → `incidencia` |
| `RegistrarLlegadaParadaListener::handleFallida` | Registra `hora_llegada` si no existe |

#### 4.3.4 DevolucionProducto

| Propiedad | Tipo | Descripción |
|---|---|---|
| `$detalle` | `HojaRutaDetalle` | Instancia del detalle de ruta (parada) |
| `$idDespacho` | `int` | ID del despacho |
| `$idPedido` | `int` | ID del pedido |

**Disparado por:** `POST /api/hojas-ruta/{id}/devolucion`

**Listeners:**

| Listener | Acción |
|---|---|
| `ActualizarEstadoPedidosListener::onDevolucionProducto` | Pedido → `entregado` (la devolución no invalida la entrega) |
| `ActualizarEstadoDespachoListener::onDevolucionProducto` | Despacho → `incidencia` |
| `RegistrarLlegadaParadaListener::handleDevolucion` | Registra `hora_llegada` si no existe |

#### 4.3.5 RepartidorRegresa

| Propiedad | Tipo | Descripción |
|---|---|---|
| `$hojaRuta` | `HojaRuta` | Instancia del modelo de hoja de ruta |
| `$idRepartidor` | `int` | ID del repartidor |
| `$idVehiculo` | `int` | ID del vehículo |

**Disparado por:** `POST /api/hojas-ruta/{id}/regreso-almacen`

**Listeners:**

| Listener | Acción |
|---|---|
| `ActualizarEstadoRepartidorListener::onRepartidorRegresa` | Repartidor → `activo` |
| `ActualizarEstadoVehiculoListener::onRepartidorRegresa` | Vehículo → `disponible` |

#### 4.3.6 Matriz completa evento → listeners

```
RutaIniciada
  ├── ActualizarEstadoPedidosListener       (pedidos → aprobado)
  ├── ActualizarEstadoRepartidorListener    (repartidor → en_ruta)
  ├── ActualizarEstadoVehiculoListener      (vehiculo → en_ruta)
  └── ActualizarEstadoDespachoListener      (idempotente)

EntregaCompletada
  ├── ActualizarEstadoPedidosListener       (pedido → entregado)
  ├── ActualizarEstadoDespachoListener      (despacho → entregado)
  └── RegistrarLlegadaParadaListener        (hora_llegada)

EntregaFallida
  ├── ActualizarEstadoPedidosListener       (pedido → pendiente)
  ├── ActualizarEstadoDespachoListener      (despacho → incidencia)
  └── RegistrarLlegadaParadaListener        (hora_llegada)

DevolucionProducto
  ├── ActualizarEstadoPedidosListener       (pedido → entregado)
  ├── ActualizarEstadoDespachoListener      (despacho → incidencia)
  └── RegistrarLlegadaParadaListener        (hora_llegada)

RepartidorRegresa
  ├── ActualizarEstadoRepartidorListener    (repartidor → activo)
  └── ActualizarEstadoVehiculoListener      (vehiculo → disponible)
```

---

## 5. REGLAS DE NEGOCIO ESTRICTAS

### 5.1 Máquinas de Estado

```
Pedido:
  pendiente ──→ aprobado ──→ entregado
         │                    ↑
         └──→ cancelado       │
                        ──────┘ (vía DevolucionProducto)

Despacho:
  pendiente ──→ entregado
         │
         └──→ incidencia

HojaRuta:
  planificado ──→ en_progreso ──→ completado
                            │
                            └──→ cancelado

Repartidor:
  activo ──→ en_ruta ──→ activo
     │
     └──→ inactivo

Vehiculo:
  disponible ──→ en_ruta ──→ disponible
         │
         └──→ taller

Caja:
  abierta ──→ cerrada ──→ auditada
```

### 5.2 Restricciones de Validación

| Entidad | Restricción |
|---|---|
| **Despacho** | `id_pedido` es **UNIQUE** — un pedido no puede tener dos despachos |
| **CajaRepartidor** | `id_hoja_ruta` es **UNIQUE** — una ruta tiene una sola caja |
| **Repartidor** | `id_usuario` es **UNIQUE** — un usuario es un solo repartidor |
| **HojaRuta** | `store` valida que el repartidor esté `activo` (`estado !== 'activo'` → 422) |
| **HojaRuta** | `iniciar` valida estado actual (`estado_ruta !== 'planificado'` → 422) |
| **Despacho** | `cambiarEstado` permite transiciones: `pendiente→entregado`, `pendiente→incidencia`, `entregado→incidencia` |
| **Caja** | No permite duplicado de `id_repartidor + fecha` |
| **Eliminaciones** | Todas protegen con HTTP 409 si existen registros relacionados |
| **Evidencia** | Validación planificada: no se puede marcar `entregado` sin al menos 1 evidencia (ver sección 10) |

### 5.3 Lógica de Cálculo y Fórmulas

| Campo | Fórmula |
|---|---|
| `detalle_pedido.subtotal` | `cantidad_solicitada × precio_unitario` |
| `pedido.subtotal` | `SUM(detalle_pedido.subtotal)` (recalculado en cada CRUD de detalle) |
| `pedido.total` | `subtotal - descuento` |
| `caja_repartidor.ingresos` | Suma de montos registrados vía POST `/api/caja/ingreso` |
| `reportes/resumen` | Conteos agregados: total pedidos × estado, total despachos, total farmacias, etc. |
| `reportes/pedidos-por-dia` | Agrupación por `DATE(fecha_pedido)` para últimos N días |

---

## 6. CRITERIOS DE ACEPTACIÓN (BDD / GHERKIN)

### 6.1 Escenarios del Camino Feliz

#### Feature: Ciclo Completo de Despacho

```gherkin
Scenario: Repartidor completa una entrega exitosa
  Given un pedido "aprobado" asignado a una hoja de ruta "planificado"
  When el repartidor inicia la ruta
  Then el repartidor cambia a "en_ruta"
  And el vehículo cambia a "en_ruta"
  And los pedidos quedan "aprobado"

Scenario: Registro de llegada y entrega
  Given una ruta "en_progreso" con una parada pendiente
  When el repartidor registra llegada
  Then la parada tiene hora_llegada registrada
  When el repartidor completa la entrega
  Then el despacho queda "entregado"
  And el pedido queda "entregado"

Scenario: Regreso a almacén
  Given una ruta "en_progreso" con todas las entregas completadas
  When el repartidor marca regreso a almacén
  Then la ruta queda "completado"
  And el repartidor vuelve a "activo"
  And el vehículo vuelve a "disponible"
```

### 6.2 Escenarios de Error y Excepciones

```gherkin
Scenario: Repartidor reporta entrega fallida
  Given un despacho "pendiente"
  When el repartidor marca entrega como fallida
  Then el despacho queda "incidencia"
  And el pedido revierte a "pendiente"

Scenario: Repartidor registra devolución de producto
  Given un despacho "pendiente"
  When el repartidor registra devolución
  Then el despacho queda "incidencia"
  And el pedido queda "entregado" (la entrega se considera completada)

Scenario: Intentar iniciar ruta ya iniciada
  Given una ruta "en_progreso"
  When se intenta iniciar nuevamente
  Then el sistema rechaza con error 422

Scenario: Intentar entregar despacho sin evidencia (planificado)
  Given un despacho "pendiente" sin evidencias
  When se intenta cambiar estado a "entregado"
  Then el sistema rechaza con error 422
  And el mensaje indica "Debe subir al menos una foto como evidencia"

Scenario: Eliminar entidad con registros relacionados
  Given una farmacia con pedidos asociados
  When se intenta eliminar la farmacia
  Then el sistema rechaza con error 409

Scenario: Autenticación inválida
  When se accede a un endpoint protegido sin token
  Then el sistema retorna 401 Unauthorized
```

---

## 7. ESTRATEGIA DE VALIDACIÓN Y COBERTURA (TDD MATCH)

### 7.1 Pruebas Unitarias Requeridas

| Componente | Tests | Estado |
|---|---|---|
| **Modelos** (16 entidades) | Table name, PK, fillable, relaciones Eloquent, casts, scopes | ✅ 101 tests |
| **Eventos** (5) | Constructor asigna propiedades, traits correctos | ✅ 11 tests |
| **Listeners** (5 subscribers) | Cada método reacciona correctamente al evento | ✅ 15 tests |
| **Requests** (3 FormRequest) | authorize(), rules() con cada campo | ✅ 16 tests |
| **Servicios** (2) | Casos felices y bordes | ✅ 11 tests |
| **Config** (6 módulos) | Estructura esperada de archivos de configuración | ✅ 6 tests |
| **Providers** (1) | Mapeo de eventos y subscribers | ✅ 2 tests |
| **Arquitectura** | Sin debuggers en código fuente | ✅ 1 test |

### 7.2 Pruebas de Integración / Feature

| Módulo | Tests | Estado |
|---|---|---|
| **Auth** (login, logout, register, user) | 14 tests | ✅ |
| **Usuarios CRUD** | 6 tests | ✅ |
| **Farmacias CRUD** + Contactos | 7 tests | ✅ |
| **Productos CRUD** | 6 tests | ✅ |
| **Pedidos CRUD** + Detalles | 8 tests | ✅ |
| **Repartidores CRUD** + Asignación vehicular | 8 tests | ✅ |
| **Vehículos CRUD** | 7 tests | ✅ |
| **Hojas de Ruta** + Workflow | 7 tests | ✅ |
| **Despachos** (CambiarEstado, CicloCompleto, IncidenciaEvidencia) | 11 tests | ✅ |
| **Caja Diaria** (abrir, ingreso, gasto, cerrar, consulta, resumen) | 6 tests | ✅ |
| **Reportes** (7 endpoints) | 7 tests | ✅ |
| **ExampleTest** | 1 test | ✅ |

### 7.3 Cobertura Total

| Tipo | Tests |
|---|---|
| Unit (modelos + eventos + listeners + requests + services + config + providers + arch) | 163 |
| Feature (auth + CRUDs + flujos + reportes) | 82 |
| **Total** | **245** |

### 7.4 Brechas Identificadas

| # | Brecha | Impacto | Prioridad | Acción requerida |
|---|---|---|---|---|
| 1 | Sin tests de autorización por rol (Spatie) | Alto — cualquier usuario autenticado puede hacer cualquier operación | 🔴 Alta | Test cada endpoint con cada rol, esperando 403 donde no corresponda |
| 2 | Validación de evidencia antes de entregar | Medio — regla de negocio implementada en tests pero no en controller | 🟡 Media | Agregar check en `DespachoController::cambiarEstado()` |
| 3 | Sin tests de concurrencia / race conditions | Medio — dos requests simultáneos podrían duplicar estados | 🟡 Media | Tests con `Http::pool()` o similar |
| 4 | Sin tests de rate limiting | Bajo | 🟢 Baja | Verificar config `throttle:api` |
| 5 | Sin tests de almacenamiento S3 | Bajo | 🟢 Baja | Configurar disco S3 en testing |

---

## 8. SEGURIDAD Y PERMISOS

### 8.1 Estado Actual

El sistema utiliza `Laravel Sanctum` para autenticación token-based y `Spatie Permission` para roles y permisos. Sin embargo, existe una **desconexión crítica**:

| Componente | Estado |
|---|---|
| Roles en BD (Administrador, Supervisor, Operador, Repartidor) | ✅ Creados y asignados en seeders |
| Permisos en BD (14 módulos × acciones) | ✅ Creados y asignados a roles |
| `auth:sanctum` middleware en rutas | ✅ Aplicado a 95/97 endpoints |
| `role:`, `permission:`, `role_or_permission:` middleware | ❌ **No aplicado en ningún endpoint** |
| Verificación de permisos en controllers | ❌ **No existe** |

### 8.2 Matriz Deseada: Endpoint → Método → Rol Mínimo

| Endpoint | Método | Rol mínimo requerido |
|---|---|---|
| `/api/auth/login` | POST | Público |
| `/api/auth/register` | POST | Público |
| `/api/auth/logout` | POST | `auth:sanctum` |
| `/api/auth/user` | GET | `auth:sanctum` |
| `/api/usuarios` | GET | Administrador |
| `/api/usuarios` | POST | Administrador |
| `/api/usuarios/{id}` | GET | Administrador |
| `/api/usuarios/{id}` | PUT | Administrador |
| `/api/usuarios/{id}` | DELETE | Administrador |
| `/api/usuarios/{id}/bloquear` | PUT | Administrador |
| `/api/farmacias` | GET | Operador+ |
| `/api/farmacias` | POST | Operador+ |
| `/api/farmacias/{id}` | GET | Operador+ |
| `/api/farmacias/{id}` | PUT | Operador+ |
| `/api/farmacias/{id}` | DELETE | Administrador |
| `/api/farmacias/{id}/contactos` | * | Operador+ |
| `/api/productos` | GET | Operador+ |
| `/api/productos` | POST | Operador+ |
| `/api/productos/{id}` | GET | Operador+ |
| `/api/productos/{id}` | PUT | Operador+ |
| `/api/productos/{id}` | DELETE | Administrador |
| `/api/pedidos` | GET | Operador+ |
| `/api/pedidos` | POST | Operador+ |
| `/api/pedidos/{id}` | GET | Operador+ |
| `/api/pedidos/{id}` | PUT | Operador+ |
| `/api/pedidos/{id}` | DELETE | Administrador |
| `/api/pedidos/{id}/cambiar-estado` | POST | Operador+ |
| `/api/pedidos/extraer-pdf` | POST | Operador+ |
| `/api/pedidos/{id}/detalles` | * | Operador+ |
| `/api/repartidores` | GET | Administrador |
| `/api/repartidores` | POST | Administrador |
| `/api/repartidores/{id}` | GET | Administrador |
| `/api/repartidores/{id}` | PUT | Administrador |
| `/api/repartidores/{id}` | DELETE | Administrador |
| `/api/repartidores/{id}/cambiar-estado` | POST | Administrador |
| `/api/repartidores/{id}/asignacion-vehiculo` | * | Administrador |
| `/api/vehiculos` | GET | Administrador |
| `/api/vehiculos` | POST | Administrador |
| `/api/vehiculos/{id}` | GET | Administrador |
| `/api/vehiculos/{id}` | PUT | Administrador |
| `/api/vehiculos/{id}` | DELETE | Administrador |
| `/api/vehiculos/{id}/cambiar-estado` | POST | Administrador |
| `/api/hojas-ruta` | GET | Supervisor+ |
| `/api/hojas-ruta` | POST | Supervisor+ |
| `/api/hojas-ruta/{id}` | GET | Supervisor+ |
| `/api/hojas-ruta/{id}` | PUT | Supervisor+ |
| `/api/hojas-ruta/{id}` | DELETE | Administrador |
| `/api/hojas-ruta/{id}/iniciar` | POST | Repartidor (propietario)+ |
| `/api/hojas-ruta/{id}/registrar-llegada` | POST | Repartidor (propietario) |
| `/api/hojas-ruta/{id}/entrega-completada` | POST | Repartidor (propietario) |
| `/api/hojas-ruta/{id}/entrega-fallida` | POST | Repartidor (propietario) |
| `/api/hojas-ruta/{id}/devolucion` | POST | Repartidor (propietario) |
| `/api/hojas-ruta/{id}/regreso-almacen` | POST | Repartidor (propietario) |
| `/api/logistica/pedidos-pendientes` | GET | Supervisor+ |
| `/api/logistica/repartidores-disponibles` | GET | Supervisor+ |
| `/api/logistica/rutas-activas` | GET | Supervisor+ |
| `/api/despachos` | GET | Supervisor+ |
| `/api/despachos` | POST | Supervisor+ |
| `/api/despachos/{id}` | GET | Supervisor+ |
| `/api/despachos/{id}` | PUT | Supervisor+ |
| `/api/despachos/{id}` | DELETE | Administrador |
| `/api/despachos/{id}/cambiar-estado` | POST | Repartidor+ |
| `/api/despachos/{id}/incidencias` | * | Repartidor+ |
| `/api/despachos/{id}/evidencias` | * | Repartidor+ |
| `/api/caja/abrir` | POST | Supervisor+ |
| `/api/caja/ingreso` | POST | Supervisor+ |
| `/api/caja/gasto` | POST | Supervisor+ |
| `/api/caja/cerrar/{id}` | PUT | Supervisor+ |
| `/api/caja/dia` | GET | Supervisor+ |
| `/api/caja/resumen` | GET | Supervisor+ |
| `/api/reportes/*` | GET | Operador+ |

---

## 9. AMBIENTES Y CONFIGURACIÓN

### 9.1 Variables de Entorno

| Variable | Desarrollo | Testing | Producción (esperado) |
|---|---|---|---|
| `APP_ENV` | `local` | `testing` (fijo en phpunit.xml) | `production` |
| `APP_DEBUG` | `true` | — | `false` |
| `APP_KEY` | Generado vía `php artisan key:generate` | Mismo que dev | Generado, no expuesto |
| `DB_CONNECTION` | `mysql` | `sqlite` (fijo en phpunit.xml) | `mysql` |
| `DB_HOST` | `127.0.0.1` | `:memory:` (fijo en phpunit.xml) | Endpoint RDS real |
| `DB_PORT` | `3306` | — | `3306` |
| `DB_DATABASE` | `laravel` | — | Nombre real |
| `DB_USERNAME` | `root` | — | Usuario restringido |
| `DB_PASSWORD` | — | — | Secreto |
| `CACHE_DRIVER` | `file` | `array` | `redis` o `file` |
| `QUEUE_CONNECTION` | `sync` | `sync` | `database` o `redis` |
| `SESSION_DRIVER` | `file` | `array` | `redis` o `file` |
| `MAIL_MAILER` | `smtp` (mailhog:1025) | `array` | SES o SMTP real |
| `FILESYSTEM_DISK` | `local` | `local` | `s3` o `local` con backup |
| `FILESYSTEM_CLOUD` | `s3` | — | `s3` |

### 9.2 Configuración por Ambiente

| Aspecto | Dev | Testing | Prod |
|---|---|---|---|
| Base de datos | MySQL (docker/local) | SQLite :memory: | MySQL RDS |
| Archivos | `storage/app/public` | `storage/app/public` (fake) | S3 + CDN |
| Cache | File | Array (desactivada) | Redis cluster |
| Cola | Sync (inmediato) | Sync | Redis + worker |
| Sesión | File | Array | Redis |
| Mail | Mailhog (inbox local) | Array (descartado) | SES |
| Debug | True | False | False |
| Log level | Debug | Debug | Error/Warning |

### 9.3 Archivos de Configuración Clave

| Archivo | Propósito |
|---|---|
| `config/app.php` | Timezone (UTC), locale (en), providers |
| `config/auth.php` | Guard (web), provider (usuarios), model (Usuario) |
| `config/database.php` | Conexiones mysql, sqlite, redis |
| `config/filesystems.php` | Disks local, public, s3 |
| `config/permission.php` | Spatie: teams=false, wildcard=false, cache=24h |
| `config/sanctum.php` | Stateful domains, expiration |
| `.env.example` | Template para nuevos entornos |

---

## 10. DEUDA TÉCNICA Y RIESGOS CONOCIDOS

| # | Ítem | Tipo | Impacto | Prioridad |
|---|---|---|---|---|
| 1 | **Permisos Spatie sin enforcement en endpoints** — roles y permisos existen en BD pero ningún controller aplica middleware `role:`, `permission:` o `role_or_permission:`. Solo se usa `auth:sanctum`. | **Seguridad** | **Alto** — cualquier usuario autenticado puede ejecutar cualquier operación (ej: un Repartidor podría eliminar usuarios o cerrar cajas) | 🔴 Alta |
| 2 | **Validación de evidencia pendiente** — Los tests `ValidarEvidenciaEntregaTest` están planificados pero `DespachoController::cambiarEstado()` aún no implementa el check `$despacho->evidencias()->count() === 0`. | **Funcional** | Medio — la regla de negocio "no entregar sin foto" no se aplica | 🟡 Media |
| 3 | **UsuarioStoreRequest sin usar** — `app/Http/Requests/Seguridad/UsuarioStoreRequest.php` existe con reglas de validación pero `UsuarioController` usa `$request->validate()` inline. | **Calidad** (dead code) | Bajo — duplicación de lógica de validación | 🟡 Media |
| 4 | **Sin transacciones en listeners** — Si un listener de un evento falla, los cambios en BD de listeners previos no se rollbackean. Ej: en RutaIniciada, si falla `ActualizarEstadoVehiculoListener`, el Repartidor ya quedó `en_ruta` pero el Vehículo no. | **Consistencia** | Medio — puede producir estados inconsistentes bajo fallos | 🟡 Media |
| 5 | **Sin soft deletes** — Ningún modelo usa `SoftDeletes`. Las eliminaciones son físicas con try/catch para HTTP 409. No hay trazabilidad de eliminaciones ni recuperación. | **Arquitectura** | Medio — pérdida de datos ante eliminación accidental | 🟡 Media |
| 6 | **onRutaIniciada en DespachoListener es idempotente** — `ActualizarEstadoDespachoListener::onRutaIniciada()` ejecuta `UPDATE despachos SET estado='pendiente' WHERE estado='pendiente'`, una operación sin efecto. Posible legacy o placeholder. | **Calidad** (dead code) | Bajo — no afecta funcionalidad | 🟢 Baja |
| 7 | **BroadcastServiceProvider comentado** — En `config/app.php` el provider de broadcasting está comentado, inhabilitando cualquier integración futura con WebSockets/Pusher. | **Arquitectura** | Bajo — no afecta funcionalidad actual | 🟢 Baja |
| 8 | **Password en seeders hardcodeado** — `DemoDataSeeder.php` usa `'password' => '123456'` (texto plano), aunque `Hash::make()` se aplica en el factory. No es riesgo real pero es mala práctica. | **Calidad** | Bajo | 🟢 Baja |
| 9 | **Sin tests de concurrencia** — No hay tests que verifiquen comportamiento bajo requests simultáneos. Podrían producirse race conditions (ej: dos requests marcando la misma entrega). | **Calidad** | Medio | 🟡 Media |
| 10 | **Sin tests de autorización por rol** — No hay tests que verifiquen que un rol específico NO puede acceder a endpoints que no le corresponden. | **Testing** | Alto — directamente vinculado al ítem #1 | 🔴 Alta |

---

*Fin del documento SDD — PW3C-DIST-SDD-001 v1.0*
