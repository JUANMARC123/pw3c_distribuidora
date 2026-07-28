# SDD-AUTH: Dominio Autenticación y Usuarios

**ID:** `PW3C-DIST-SDD-AUTH` | **Depende de:** SDD-Global (secciones 1-5 transversales)

---

## 1. CONTROL DEL DOCUMENTO

| Versión | Fecha | Descripción |
|---|---|---|
| 1.0 | 2026-07-13 | Versión inicial |

## 2. DESCRIPCIÓN GENERAL

### 2.1 Propósito

Gestionar el ciclo de vida de usuarios del sistema: registro, autenticación, roles, permisos y bloqueo.

### 2.2 Actores y Permisos

| Actor | Rol en BD | Acceso |
|---|---|---|
| Visitante | — | Solo login y register (público) |
| Administrador | `Administrador` | CRUD completo de usuarios + asignación de roles |
| Supervisor | `Supervisor` | Solo lectura de usuarios |
| Operador | `Operador` | Solo lectura de usuarios |
| Repartidor | `Repartidor` | Solo su propio perfil |

### 2.3 Alcance

| In Scope | Out of Scope |
|---|---|
| Login con email/password | OAuth2 / SSO |
| Registro con asignación automática de rol | Recuperación de contraseña |
| CRUD de usuarios | Verificación de email |
| Bloqueo de usuarios | |
| Roles y permisos vía Spatie | |

---

## 3. ARQUITECTURA

### 3.1 Stack específico del dominio

| Componente | Tecnología |
|---|---|
| Autenticación | Laravel Sanctum (token-based) |
| Roles/Permisos | Spatie Permission v5 |
| Modelo de usuario | `App\Models\Usuario` (extends Authenticatable) |
| Password | Hash::make (bcrypt) vía `getAuthPassword()` |

### 3.2 Patrones

- **Token-based Auth:** Sanctum genera tokens en login, se revocan en logout
- **Role-based Access Control:** Spatie `HasRoles` trait en Usuario

---

## 4. CONTRATOS

### 4.1 Base de Datos

#### Tabla `usuarios`

| Columna | Tipo | Restricciones |
|---|---|---|
| `id_usuario` | bigint PK | Auto-increment |
| `nombre` | string | NOT NULL |
| `apellido` | string | NOT NULL |
| `email` | string | NOT NULL, UNIQUE |
| `password_hash` | string | NOT NULL |
| `telefono` | string | NULLABLE |
| `estado` | enum(`activo`,`bloqueado`) | DEFAULT `activo` |
| `remember_token` | string(100) | NULLABLE |
| `created_at` / `updated_at` | timestamp | — |

#### Tablas del sistema (Spatie + Sanctum)

| Tabla | Propósito |
|---|---|
| `personal_access_tokens` | Tokens de Sanctum por usuario |
| `permissions` | Catálogo de permisos (14 módulos × acciones) |
| `roles` | Catálogo de roles (4) |
| `model_has_permissions` | Asignación permiso → usuario |
| `model_has_roles` | Asignación rol → usuario |
| `role_has_permissions` | Asignación permiso → rol |

### 4.2 API Endpoints

#### Públicos (sin autenticación)

| Método | Ruta | Request | Response exitoso |
|---|---|---|---|
| POST | `/api/auth/login` | `{ email, password }` | `200: { user, token, permisos }` |
| POST | `/api/auth/register` | `{ nombre, apellido, email, password, telefono? }` | `201: { user, token, permisos }` |

#### Autenticados (`auth:sanctum`)

| Método | Ruta | Descripción |
|---|---|---|
| POST | `/api/auth/logout` | Revoca token actual. Response: `200` |
| GET | `/api/auth/user` | Perfil + roles + permisos. Response: `200: { user, permisos }` |
| GET | `/api/usuarios` | Listar usuarios (paginado). Query: `?search=`, `?estado=`, `?per_page=` |
| POST | `/api/usuarios` | Crear usuario + asignar roles |
| GET | `/api/usuarios/{id}` | Mostrar usuario (con roles, repartidor) |
| PUT | `/api/usuarios/{id}` | Actualizar usuario |
| DELETE | `/api/usuarios/{id}` | Eliminar usuario (409 si tiene relaciones) |
| PUT | `/api/usuarios/{id}/bloquear` | Cambia estado a `bloqueado` |

### 4.3 Errores Comunes

| Código | Causa |
|---|---|
| 401 | Token no provisto o inválido |
| 403 | Usuario bloqueado (`estado = bloqueado`) |
| 422 | Validación fallida (email duplicado, password muy corto, etc.) |
| 404 | Usuario no encontrado |
| 409 | No se puede eliminar por relaciones existentes |

---

## 5. REGLAS DE NEGOCIO

| # | Regla |
|---|---|
| 1 | Al registrarse, se asigna automáticamente el rol `Operador` |
| 2 | El email debe ser único en todo el sistema |
| 3 | Un usuario bloqueado no puede iniciar sesión (403) |
| 4 | No se puede eliminar un usuario que tenga pedidos o sea repartidor (409) |
| 5 | La contraseña debe tener mínimo 6 caracteres |

### 5.1 Máquina de Estado

```
Usuario: activo ──→ bloqueado
```

---

## 6. CRITERIOS DE ACEPTACIÓN (BDD)

```gherkin
Feature: Login
  Scenario: Credenciales correctas
    Given un usuario activo con email "test@test.com" y password "123456"
    When se envía POST /api/auth/login con esas credenciales
    Then retorna 200 con token y datos del usuario

  Scenario: Usuario bloqueado
    Given un usuario con estado "bloqueado"
    When se intenta loguear
    Then retorna 403

Feature: Usuarios CRUD
  Scenario: Crear usuario con roles
    When se envía POST /api/usuarios con datos válidos y un rol
    Then retorna 201 y el usuario tiene el rol asignado

  Scenario: Bloquear usuario
    Given un usuario activo
    When se envía PUT /api/usuarios/{id}/bloquear
    Then el usuario queda con estado "bloqueado"
```

---

## 7. ESTRATEGIA DE VALIDACIÓN

### 7.1 Tests del dominio

| Tipo | Archivo | Tests |
|---|---|---|
| Feature | `LoginTest.php` | 5 |
| Feature | `LogoutTest.php` | 2 |
| Feature | `RegisterTest.php` | 4 |
| Feature | `UserTest.php` | 3 |
| Feature | `UsuarioCRUDTest.php` | 6 |
| Unit | `LoginRequestTest.php` | 4 |
| Unit | `RegisterRequestTest.php` | 6 |
| Unit | `UsuarioStoreRequestTest.php` | 6 |
| Unit | `UsuarioTest.php` (modelo) | 10 |
| **Total** | | **46** |

### 7.2 Brechas

| Brecha | Impacto |
|---|---|
| Sin tests de autorización por rol (Spatie no verificado en endpoints) | Alto |
| Sin tests de rate limiting en login | Bajo |

---

## 8. SEGURIDAD Y PERMISOS

### Matriz endpoint → rol mínimo deseado

| Endpoint | Método | Rol mínimo |
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

---

## 9. DEUDA TÉCNICA DEL DOMINIO

| # | Ítem | Prioridad |
|---|---|---|
| 1 | `UsuarioStoreRequest` existe pero `UsuarioController` valida inline — dead code | 🟡 Media |
| 2 | Sin middleware `role:Administrador` en endpoints de usuarios | 🔴 Alta (ver #1 global) |

---

*Fin del SDD-AUTH — PW3C-DIST-SDD-AUTH v1.0*
