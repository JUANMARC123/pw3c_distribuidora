# Módulo de Autenticación y Usuarios

## 1. Arquitectura Técnica (SDD)

### Archivos de Código Fuente
- `app/Http/Controllers/AuthController.php` — login, register, logout, user
- `app/Http/Controllers/Seguridad/UsuarioController.php` — CRUD usuarios
- `app/Models/Usuario.php` — modelo con Sanctum + Spatie Permission
- `app/Http/Requests/LoginRequest.php` — validación de login
- `app/Http/Requests/RegisterRequest.php` — validación de registro
- `app/Http/Requests/Seguridad/UsuarioStoreRequest.php` — validación de creación de usuarios

### Stack
- **Autenticación:** Laravel Sanctum (token-based)
- **Roles/Permisos:** Spatie Permission v5
- **Modelo:** `App\Models\Usuario` (extends Authenticatable, `getAuthPassword()` retorna `password_hash`)

### Endpoints

| Método | Ruta | Controlador | Permiso |
|--------|------|-------------|---------|
| POST | `/api/auth/login` | `AuthController::login` | Público |
| POST | `/api/auth/register` | `AuthController::register` | Público |
| POST | `/api/auth/logout` | `AuthController::logout` | `auth:sanctum` |
| GET | `/api/auth/user` | `AuthController::user` | `auth:sanctum` |
| GET | `/api/usuarios` | `UsuarioController::index` | `permission:Usuarios,listar` |
| POST | `/api/usuarios` | `UsuarioController::store` | `permission:Usuarios,crear` |
| GET | `/api/usuarios/{id}` | `UsuarioController::show` | `permission:Usuarios,listar` |
| PUT | `/api/usuarios/{id}` | `UsuarioController::update` | `permission:Usuarios,editar` |
| DELETE | `/api/usuarios/{id}` | `UsuarioController::destroy` | `permission:Usuarios,eliminar` |
| PUT | `/api/usuarios/{id}/bloquear` | `UsuarioController::bloquear` | `permission:Usuarios,editar` |

### Reglas de Negocio Clave
1. **Password personalizado:** El campo en BD es `password_hash`, no `password`. `getAuthPassword()` lo retorna.
2. **Registro asigna rol:** Si no se especifica rol, se asigna `Repartidor` (id=4).
3. **Login registra último acceso:** `ultimo_acceso = now()` en cada login exitoso.
4. **Usuario bloqueado:** No puede iniciar sesión (HTTP 403).
5. **Protección de eliminación:** No se puede eliminar usuario con pedidos (HTTP 409 + `can_block: true`).

---

## 2. Especificación de Comportamiento (BDD)

### Escenario A1: Login exitoso
```
Dado un usuario registrado con email "test@example.com" y password "password123"
Cuando envía POST /api/auth/login con esas credenciales
Entonces recibe HTTP 200 con estructura { success, message, data: { user, token, permisos } }
Y se registra el último acceso del usuario
```
- **Test Feature:** `Auth/LoginTest.php` → `it('loguea con credenciales correctas')`
- **Test Unit:** `Auth/Requests/LoginRequestTest.php`
- **Controlador:** `AuthController::login`

### Escenario A2: Login con credenciales incorrectas
```
Dado un usuario registrado con email "test@example.com"
Cuando envía POST /api/auth/login con contraseña incorrecta
Entonces recibe HTTP 422
```
- **Test Feature:** `Auth/LoginTest.php` → `it('rechaza contraseña incorrecta')`, `it('rechaza email inexistente')`
- **Controlador:** `AuthController::login`

### Escenario A3: Usuario bloqueado no puede iniciar sesión
```
Dado un usuario con estado "bloqueado"
Cuando envía POST /api/auth/login con credenciales correctas
Entonces recibe HTTP 403
```
- **Test Feature:** `Auth/LoginTest.php` → `it('bloquea usuario inactivo')`
- **Controlador:** `AuthController::login`

### Escenario A4: Logout invalida token
```
Dado un usuario autenticado con Sanctum
Cuando envía POST /api/auth/logout
Entonces recibe HTTP 200 con success: true
Y el token actual queda invalidado
```
- **Test Feature:** `Auth/LogoutTest.php` → `it('cierra sesion correctamente')`
- **Controlador:** `AuthController::logout`

### Escenario A5: CRUD de usuarios
```
Dado un administrador autenticado
Cuando ejecuta operaciones CRUD sobre /api/usuarios
Entonces puede listar, crear, mostrar, actualizar, eliminar y bloquear usuarios
Y no puede eliminar un usuario con pedidos asociados
```
- **Test Feature:** `Auth/UsuarioCRUDTest.php`
- **Test Unit:** `Auth/Models/UsuarioTest.php`, `Auth/Requests/UsuarioStoreRequestTest.php`
- **Controlador:** `UsuarioController`

---

## 3. Trazabilidad de Tests

### Tests Feature
| Archivo de Test | Grupo Pest | Controlador |
|---|---|---|
| `tests/Feature/Auth/LoginTest.php` | `auth, login` | `AuthController::login` |
| `tests/Feature/Auth/LogoutTest.php` | `auth, logout` | `AuthController::logout` |
| `tests/Feature/Auth/RegisterTest.php` | `auth, register` | `AuthController::register` |
| `tests/Feature/Auth/UserTest.php` | `auth, user` | `AuthController::user` |
| `tests/Feature/Auth/UsuarioCRUDTest.php` | `usuarios, crud` | `UsuarioController` |

### Tests Unit
| Archivo de Test | Namespace |
|---|---|
| `tests/Unit/Auth/Models/UsuarioTest.php` | `Tests\Unit\Auth\Models` |
| `tests/Unit/Auth/Requests/LoginRequestTest.php` | `Tests\Unit\Auth\Requests` |
| `tests/Unit/Auth/Requests/RegisterRequestTest.php` | `Tests\Unit\Auth\Requests` |
| `tests/Unit/Auth/Requests/UsuarioStoreRequestTest.php` | `Tests\Unit\Auth\Requests` |
