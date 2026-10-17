# SDD-GLOBAL: Sistema de Gestión de Distribuidora Farmacéutica

Documento raíz del sistema. Contiene las secciones transversales a todos los dominios. Cada dominio tiene su propio SDD específico.

---

## 1. CONTROL DEL DOCUMENTO Y METADATOS

### 1.1 Identificación

| Campo | Valor |
|---|---|
| **ID** | `PW3C-DIST-SDD-000` |
| **Nombre** | SDD Global — Sistema de Gestión de Distribuidora |
| **Tipo** | Documento raíz (transversal) |

### 1.2 Subdocumentos por dominio

| ID | Dominio | Archivo | Cobertura |
|---|---|---|---|
| `PW3C-DIST-SDD-AUTH` | Auth y Usuarios | `SDD-Auth.md` | Autenticación, roles, permisos, CRUD de usuarios |
| `PW3C-DIST-SDD-CAT` | Catálogo | `SDD-Catalogo.md` | Farmacias, contactos, productos |
| `PW3C-DIST-SDD-PED` | Pedidos | `SDD-Pedidos.md` | Pedidos, detalle de pedidos, extracción PDF |
| `PW3C-DIST-SDD-LOG` | Logística | `SDD-Logistica.md` | Repartidores, vehículos, hojas de ruta, despachos, incidencias, evidencias |
| `PW3C-DIST-SDD-CAJ` | Caja | `SDD-Caja.md` | Caja diaria, gastos operativos |
| `PW3C-DIST-SDD-RPT` | Reportes | `SDD-Reportes.md` | 7 endpoints de reportes y estadísticas |

### 1.3 Historial de Versiones

| Versión | Fecha | Autor | Descripción |
|---|---|---|---|
| 1.0 | 2026-07-13 | — | Versión inicial. 6 SDDs de dominio + 1 global |

### 1.4 Estado del Spec

`Aprobado para Desarrollo`

### 1.5 Dependencias y Bloqueos

| Dependencia | Tipo | Versión |
|---|---|---|
| PHP | Lenguaje | ^8.2 |
| Laravel | Framework | ^9.0 |
| MySQL | Base de datos (prod/dev) | 8+ |
| SQLite | Base de datos (testing) | — |
| Pest PHP | Testing | ^1.0 |
| Spatie Permission | Roles/Permisos | ^5.0 |
| Laravel Sanctum | Autenticación API | ^3.0 |

> Sin bloqueos externos. Todos los dominios comparten el mismo código base.

---

## 2. ARQUITECTURA Y CONTRATO TECNOLÓGICO (GLOBAL)

### 2.1 Stack de Implementación

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

### 2.2 Diagrama de Arquitectura General

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

### 2.3 Patrones de Diseño Aplicados

| Patrón | Aplicación |
|---|---|
| **REST API** | Todos los endpoints siguen principios RESTful |
| **Active Record** | Eloquent ORM — cada modelo mapea a una tabla |
| **Event-Driven** | 5 eventos de dominio notifican cambios de estado (ver SDD-Logistica) |
| **Observer / Subscriber** | Listeners agrupados en subscribers registrados vía `Event::subscribe()` |
| **Repository-lite** | Servicios encapsulan lógica de asignación (`AsignacionVehiculoService`) |
| **DTO / Response Helper** | `ApiController` con `jsonResponse()`, `errorResponse()`, `paginatedResponse()` |
| **Factory Pattern** | Pest factories para creación de datos de prueba |

### 2.4 Convenciones de API

| Aspecto | Especificación |
|---|---|
| **Base URL** | `/api` |
| **Autenticación** | Sanctum token (`Authorization: Bearer <token>`) |
| **Formato request** | `application/json` (multipart para archivos) |
| **Formato response exitoso** | `{ "success": true, "message": "...", "data": {...} }` |
| **Formato response paginado** | `{ "success": true, "data": [...], "meta": { "current_page": N, "last_page": N, "per_page": N, "total": N } }` |
| **Formato response error** | `{ "success": false, "message": "...", "errors": {...} }` |
| **Códigos HTTP** | 200 OK, 201 Created, 204 No Content, 400 Bad Request, 401 Unauthorized, 403 Forbidden, 404 Not Found, 409 Conflict, 422 Unprocessable Entity, 500 Server Error |

---

## 3. SEGURIDAD Y PERMISOS (GLOBAL)

### 3.1 Roles del Sistema

| Rol | Descripción |
|---|---|
| **Administrador** | Acceso total a todas las funcionalidades del sistema |
| **Supervisor** | Gestión de rutas, pedidos, reportes |
| **Operador** | Creación y gestión de pedidos, farmacias, productos |
| **Repartidor** | Ejecución de rutas: entregas, incidencias, evidencias |

### 3.2 Estado Actual

| Componente | Estado |
|---|---|
| Roles en BD (4 roles) | ✅ Creados y asignados en seeders |
| Permisos en BD (14 módulos × acciones) | ✅ Creados y asignados a roles |
| `auth:sanctum` middleware en rutas | ✅ Aplicado a 95/97 endpoints |
| `role:`, `permission:`, `role_or_permission:` middleware | ❌ **No aplicado en ningún endpoint** |

> ⚠️ **Riesgo crítico:** Spatie Permission está configurado y los roles existen, pero ningún endpoint verifica permisos vía middleware. Ver sección de Deuda Técnica en cada SDD de dominio para el detalle de endpoints afectados.

### 3.3 Matriz General de Módulos y Permisos

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

---

## 4. AMBIENTES Y CONFIGURACIÓN (GLOBAL)

### 4.1 Variables de Entorno

| Variable | Desarrollo | Testing | Producción (esperado) |
|---|---|---|---|
| `APP_ENV` | `local` | `testing` | `production` |
| `APP_DEBUG` | `true` | — | `false` |
| `DB_CONNECTION` | `mysql` | `sqlite` | `mysql` |
| `DB_HOST` | `127.0.0.1` | `:memory:` | Endpoint RDS real |
| `DB_PORT` | `3306` | — | `3306` |
| `CACHE_DRIVER` | `file` | `array` | `redis` o `file` |
| `QUEUE_CONNECTION` | `sync` | `sync` | `database` o `redis` |
| `SESSION_DRIVER` | `file` | `array` | `redis` o `file` |
| `MAIL_MAILER` | `smtp` (mailhog) | `array` | SES |
| `FILESYSTEM_DISK` | `local` | `local` | `s3` o `local` |

### 4.2 Archivos de Configuración Clave

| Archivo | Propósito |
|---|---|
| `config/app.php` | Timezone (UTC), locale (en), service providers |
| `config/auth.php` | Guard (web), provider (usuarios), model (Usuario::class) |
| `config/database.php` | Conexiones mysql, sqlite, redis |
| `config/filesystems.php` | Discos local, public, s3 |
| `config/permission.php` | Spatie: teams=false, wildcard=false, cache=24h |
| `config/sanctum.php` | Stateful domains, token expiration |
| `.env.example` | Template para nuevos entornos |

---

## 5. DEUDA TÉCNICA Y RIESGOS CONOCIDOS (GLOBAL)

| # | Ítem | Tipo | Impacto | Prioridad |
|---|---|---|---|---|
| 1 | **Permisos Spatie sin enforcement** — roles existen pero ningún endpoint verifica middleware `role:` | Seguridad | Alto — cualquier usuario autenticado puede ejecutar cualquier operación | 🔴 Alta |
| 2 | **Sin transacciones en listeners** — Si un listener falla, los cambios de listeners previos no se rollbackean | Consistencia | Medio | 🟡 Media |
| 3 | **Sin soft deletes** — Eliminaciones físicas, sin trazabilidad ni recuperación | Arquitectura | Medio | 🟡 Media |
| 4 | **UsuarioStoreRequest sin usar** — FormRequest existe pero no se referencia en ningún controller | Calidad (dead code) | Bajo | 🟢 Baja |
| 5 | **BroadcastServiceProvider comentado** — WebSockets deshabilitados | Arquitectura | Bajo | 🟢 Baja |
| 6 | **Sin tests de concurrencia** — No hay tests que verifiquen race conditions | Testing | Medio | 🟡 Media |

> Para deuda técnica específica de cada dominio, ver la sección 10 del SDD correspondiente.

---

*Fin del documento SDD-GLOBAL — PW3C-DIST-SDD-000 v1.0*
