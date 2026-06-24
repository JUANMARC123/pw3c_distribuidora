# Reglas de Negocio - Pw3c Distribuidora

> **Versión:** 1.0  
> **Propósito:** Documentar todas las reglas de negocio implementadas en el sistema de gestión de distribución Pw3c, incluyendo su mecanismo de funcionamiento y ubicación exacta en el código.

---

## Índice

1. [Módulo de Seguridad (Usuarios, Roles, Permisos)](#1-módulo-de-seguridad)
2. [Módulo de Farmacias y Contactos](#2-módulo-de-farmacias)
3. [Módulo de Pedidos](#3-módulo-de-pedidos)
4. [Módulo de Repartidores](#4-módulo-de-repartidores)
5. [Módulo de Vehículos](#5-módulo-de-vehículos)
6. [Módulo de Rutas y Paradas](#6-módulo-de-rutas-y-logística)
7. [Módulo de Control de Rutas](#7-módulo-de-control-de-rutas)
8. [Módulo de Despachos](#8-módulo-de-despachos)
9. [Módulo de Evidencias e Incidencias](#9-módulo-de-evidencias-e-incidencias)
10. [Módulo de Reportes](#10-módulo-de-reportes)
11. [Módulo de Catálogos](#11-módulo-de-catálogos)
12. [Reglas Transversales](#12-reglas-transversales)

---

## 1. Módulo de Seguridad

### 1.1 Registro de usuario: estado inicial siempre "Activo"

- **Cómo funciona**: Al registrarse un nuevo usuario mediante `AuthController::register()`, el campo `id_estado_usuario` se fuerza a `1` (Activo) antes de crear el registro, sin importar lo que envíe el cliente.
- **Dónde se aplica**:
  - `app/Http/Controllers/AuthController.php:48` — `$data['id_estado_usuario'] = 1;`
  - Estados definidos en `database/seeders/CatalogoSeeder.php:44-48` (Activo=1, Bloqueado=2, Suspendido=3)

### 1.2 Usuario bloqueado no puede iniciar sesión

- **Cómo funciona**: En `AuthController::login()`, después de verificar que el email existe y la contraseña es correcta, se valida que `id_estado_usuario === 1`. Si el usuario tiene estado 2 (Bloqueado) o 3 (Suspendido), se retorna HTTP 403 con mensaje `"Cuenta de usuario bloqueada o suspendida."` y no se genera ningún token.
- **Dónde se aplica**:
  - `app/Http/Controllers/AuthController.php:24-26` — Validación del estado
  - `app/Http/Controllers/Seguridad/UsuarioController.php:116-128` — Endpoint `bloquear()` que cambia el estado a 2

### 1.3 Autenticación con campo personalizado `password_hash`

- **Cómo funciona**: El modelo `Usuario` sobreescribe el método `getAuthPassword()` de Laravel para retornar `password_hash` en lugar del campo convencional `password`. Esto permite usar un nombre de columna distinto al estándar de Laravel.
- **Dónde se aplica**:
  - `app/Models/Seguridad/Usuario.php:41-44` — Método `getAuthPassword()`
  - `app/Http/Controllers/AuthController.php:18` — Verificación con `Hash::check($request->password, $user->password_hash)`

### 1.4 Rol por defecto "Repartidor" en auto-registro

- **Cómo funciona**: Cuando un usuario se registra vía `AuthController::register()` y no envía el campo `roles`, el sistema asigna automáticamente el rol con `id_rol = 4` (Repartidor). Si se envían roles, se asignan los indicados.
- **Dónde se aplica**:
  - `app/Http/Controllers/AuthController.php:53-57` — `if ($request->filled('roles')) { ... } else { $user->roles()->attach(4); }`

### 1.5 Transformación de contraseña antes de guardar

- **Cómo funciona**: Tanto en registro como en creación/actualización de usuarios desde el backend, el campo `password` recibido se transforma: se genera `password_hash = Hash::make(password)`, se agrega al arreglo de datos y se elimina el campo `password` original. El campo `password` no existe en la base de datos.
- **Dónde se aplica**:
  - `app/Http/Controllers/AuthController.php:46` — `$data['password_hash'] = Hash::make($data['password']);`
  - `app/Http/Controllers/Seguridad/UsuarioController.php:38-39` — En store
  - `app/Http/Controllers/Seguridad/UsuarioController.php:78-80` — En update

### 1.6 Protección: no eliminar usuario si tiene pedidos

- **Cómo funciona**: Al intentar eliminar un usuario, si la base de datos lanza una excepción con código `23000` (violación de llave foránea), se captura y se retorna HTTP 409 con `can_block: true` y el mensaje `"No se puede eliminar este usuario porque tiene pedidos registrados."`, sugiriendo bloquear en lugar de eliminar.
- **Dónde se aplica**:
  - `app/Http/Controllers/Seguridad/UsuarioController.php:98-113` — Try-catch con `QueryException`
  - Migración FK: `database/migrations/2024_01_03_000001_create_pedidos_table.php:25-29` — `onDelete('restrict')`

### 1.7 Protección: no eliminar rol si está asignado a usuarios

- **Cómo funciona**: Similar a usuarios, el controlador captura la excepción `23000` al eliminar un rol y retorna HTTP 409 con `assigned: true` y mensaje `"No se puede eliminar este rol porque está asignado a uno o más usuarios."`.
- **Dónde se aplica**:
  - `app/Http/Controllers/Seguridad/RolController.php:49-67`
  - Migración FK: `database/migrations/2024_01_03_000003_create_usuario_roles_table.php` — FK con `onDelete('restrict')`

### 1.8 Middleware CheckPermission: mapeo de método HTTP a acción

- **Cómo funciona**: El middleware recibe el nombre del módulo y, opcionalmente, la acción. Si no se especifica acción, la deduce del método del controlador: `index/show → listar`, `store → crear`, `update → editar`, `destroy → eliminar`. Luego llama a `$user->hasPermission(module, action)`.
- **Dónde se aplica**:
  - `app/Http/Middleware/CheckPermission.php:10-16` — Mapa de acciones
  - `app/Http/Middleware/CheckPermission.php:18-46` — Método `handle()`
  - `app/Models/Seguridad/Usuario.php:76-86` — Método `hasPermission()`
  - `routes/api.php:30` — Aplicación del middleware en rutas (ej: `->middleware('permission:Usuarios')`)

### 1.9 Matriz de permisos por roles (RBAC)

- **Cómo funciona**: Se definen 4 roles semilla: Administrador (todos los permisos), Supervisor (permisos completos en la mayoría de módulos excepto Usuarios y Roles), Operador (permisos limitados: solo lectura en Farmacias, Repartidores, Vehículos, Rutas), Repartidor (solo Dashboard, Rutas/Control listar, registrar-llegada, Despachos listar). El Administrador recibe automáticamente TODOS los permisos existentes.
- **Dónde se aplica**:
  - `database/seeders/CatalogoSeeder.php:28-40` — Definición de módulos y acciones
  - `database/seeders/CatalogoSeeder.php:96-126` — Asignación de permisos por rol
  - `database/seeders/CatalogoSeeder.php:134-163` — Lógica de asignación (todos los permisos para Admin)

### 1.10 Login registra último acceso

- **Cómo funciona**: Cada vez que un usuario inicia sesión exitosamente, se actualiza el campo `ultimo_acceso` con la fecha/hora actual (`now()`) antes de generar el token.
- **Dónde se aplica**:
  - `app/Http/Controllers/AuthController.php:28-29` — `$user->ultimo_acceso = now(); $user->save();`

### 1.11 Logout elimina token actual

- **Cómo funciona**: Al cerrar sesión, se elimina el token de acceso actual (Sanctum) mediante `currentAccessToken()->delete()`, invalidando la sesión del dispositivo.
- **Dónde se aplica**:
  - `app/Http/Controllers/AuthController.php:71-76`
  - `routes/api.php:24` — Ruta protegida por `auth:sanctum`

### 1.12 Protección de rutas de escritura en catálogos

- **Cómo funciona**: Los endpoints GET de catálogos son accesibles para cualquier usuario autenticado. Los endpoints POST/PUT/DELETE están agrupados bajo el middleware `permission:Usuarios`, lo que significa que solo usuarios con ALGÚN permiso en el módulo Usuarios pueden modificarlos.
- **Dónde se aplica**:
  - `routes/api.php:95-119` — GET sin middleware adicional, POST/PUT/DELETE con `->middleware('permission:Usuarios')`
  - `app/Http/Middleware/CheckPermission.php:26-36` — Cuando no se especifica acción, deduce del método

---

## 2. Módulo de Farmacias

### 2.1 Validación de coordenadas geográficas

- **Cómo funciona**: Los campos `latitud` y `longitud` se validan con `between:-90,90` y `between:-180,180` respectivamente. En la base de datos son `decimal(10,7)`, permitiendo 7 decimales de precisión.
- **Dónde se aplica**:
  - `app/Http/Controllers/Farmacia/FarmaciaController.php:35-36` — Validación en store
  - `app/Http/Controllers/Farmacia/FarmaciaController.php:59-60` — Validación en update
  - Migración: `database/migrations/2024_01_02_000002_create_farmacias_table.php`

### 2.2 Email único (nullable)

- **Cómo funciona**: El email de la farmacia es opcional (nullable), pero si se proporciona, debe tener formato email válido y ser único en la tabla `farmacias`. La validación con `unique:farmacias,email` garantiza que no haya duplicados.
- **Dónde se aplica**:
  - `app/Http/Controllers/Farmacia/FarmaciaController.php:34` — Store
  - `app/Http/Controllers/Farmacia/FarmaciaController.php:58` — Update (ignora el registro actual)

### 2.3 Contactos se eliminan en cascada con la farmacia

- **Cómo funciona**: Cuando se elimina una farmacia, todos sus contactos asociados se eliminan automáticamente gracias a la FK con `onDelete('cascade')`.
- **Dónde se aplica**:
  - Migración: `database/migrations/2024_01_03_000002_create_contactos_farmacia_table.php` — FK con `cascade`

### 2.4 Contactos siempre dentro del contexto de una farmacia

- **Cómo funciona**: Todas las operaciones de contactos (CRUD) están anidadas bajo la ruta `/farmacias/{farmacia}/contactos`. El controlador siempre verifica que la farmacia exista antes de operar sobre los contactos, y filtra por `id_farmacia`.
- **Dónde se aplica**:
  - `routes/api.php:40-44` — Rutas anidadas
  - `app/Http/Controllers/Farmacia/ContactoFarmaciaController.php:12-17, 22-23, 43-46, 51, 70` — Filtrado por farmacia

### 2.5 Cargo del contacto debe existir en catálogo

- **Cómo funciona**: El campo `id_cargo` es obligatorio y debe existir en la tabla `cargos`. Los cargos disponibles son: Gerente, Administrador, Farmaceutico, Recepcionista, Almacenero.
- **Dónde se aplica**:
  - `app/Http/Controllers/Farmacia/ContactoFarmaciaController.php:26` — `'id_cargo' => 'required|integer|exists:cargos,id_cargo'`
  - `database/seeders/CatalogoSeeder.php:272-278` — Seed de cargos

### 2.6 Búsqueda de farmacias por múltiples campos

- **Cómo funciona**: El endpoint `GET /farmacias` permite buscar por `search` que aplica LIKE sobre `nombre`, `direccion` y `telefono` simultáneamente.
- **Dónde se aplica**:
  - `app/Http/Controllers/Farmacia/FarmaciaController.php:15-22` — Búsqueda con `orWhere`

---

## 3. Módulo de Pedidos

### 3.1 Máquina de estados de pedidos (6 estados)

- **Cómo funciona**: Los pedidos pueden estar en uno de 6 estados: Pendiente, Aprobado, En preparacion, Despachado, Entregado, Cancelado. El sistema NO valida transiciones válidas/inválidas — cualquier estado puede cambiarse a cualquier otro mediante el endpoint `cambiarEstado`.
- **Dónde se aplica**:
  - `database/seeders/CatalogoSeeder.php:180-187` — Definición de estados
  - `app/Http/Controllers/Pedido/PedidoController.php:115-144` — Método `cambiarEstado()` sin validación de transiciones
  - `routes/api.php:47` — Ruta `POST /pedidos/{pedido}/cambiar-estado`

### 3.2 Historial de cambios de estado

- **Cómo funciona**: Cada vez que se cambia el estado de un pedido: (1) se busca el registro activo actual del historial (donde `fecha_fin IS NULL`), (2) se le asigna `fecha_fin = now()`, (3) se crea un nuevo registro con `fecha_inicio = now()` y `fecha_fin = NULL`, (4) se actualiza el estado actual del pedido.
- **Dónde se aplica**:
  - `app/Http/Controllers/Pedido/PedidoController.php:123-138` — Patrón de historial
  - `app/Http/Controllers/Pedido/PedidoController.php:51-55` — Creación del historial inicial al crear pedido
  - Modelo: `app/Models/Pedido/HistorialEstadoPedido.php` (y sus homólogos para Repartidor, Vehículo, Despacho)

### 3.3 Fecha del pedido se asigna automáticamente

- **Cómo funciona**: Al crear un pedido, el campo `fecha_pedido` se fuerza a `now()` desde el controlador, sin importar lo que envíe el cliente. En BD tiene `useCurrent()` como valor por defecto.
- **Dónde se aplica**:
  - `app/Http/Controllers/Pedido/PedidoController.php:47` — `$data['fecha_pedido'] = now();`
  - Migración: `database/migrations/2024_01_03_000001_create_pedidos_table.php:16` — `->useCurrent()`

### 3.4 Protección: no eliminar pedido si tiene despacho

- **Cómo funciona**: Similar a usuarios, al eliminar un pedido que ya tiene un despacho registrado se captura la excepción `23000` y se retorna HTTP 409 con `has_dispatch: true` y mensaje `"No se puede eliminar este pedido porque tiene un despacho registrado."`.
- **Dónde se aplica**:
  - `app/Http/Controllers/Pedido/PedidoController.php:94-113` — Try-catch con `QueryException`
  - Migración: `database/migrations/2024_01_03_000008_create_despachos_table.php:19-23` — FK `onDelete('restrict')`

### 3.5 Un pedido puede tener como máximo un despacho (1:1)

- **Cómo funciona**: La columna `id_pedido` en la tabla `despachos` tiene una restricción UNIQUE implícita (validada por Laravel con `unique:despachos,id_pedido`), lo que garantiza que un pedido solo puede ser despachado una vez.
- **Dónde se aplica**:
  - `app/Http/Controllers/Despacho/DespachoController.php:44` — Validación `'id_pedido' => 'required|integer|exists:pedidos,id_pedido|unique:despachos,id_pedido'`

### 3.6 Actualización limitada de pedidos

- **Cómo funciona**: Al actualizar un pedido, solo se permite modificar `id_farmacia` y `observaciones`. Los campos `id_estado_pedido`, `id_usuario` y `fecha_pedido` no pueden cambiarse mediante update (el estado tiene endpoint dedicado).
- **Dónde se aplica**:
  - `app/Http/Controllers/Pedido/PedidoController.php:81-84` — Solo `id_farmacia` (sometimes) y `observaciones` (nullable)

### 3.7 Filtros de búsqueda de pedidos

- **Cómo funciona**: El endpoint `GET /pedidos` permite filtrar por `id_estado_pedido`, `id_farmacia`, `fecha_desde` y `fecha_hasta`. La ordenación por defecto es `fecha_pedido DESC`.
- **Dónde se aplica**:
  - `app/Http/Controllers/Pedido/PedidoController.php:17-35` — Filtros
  - `app/Http/Controllers/Pedido/PedidoController.php:34` — `orderBy('fecha_pedido', 'desc')`

### 3.8 Estado del pedido requerido al crear

- **Cómo funciona**: Al crear un pedido, el campo `id_estado_pedido` es obligatorio. No hay un valor por defecto a nivel de sistema — el usuario debe especificarlo explícitamente.
- **Dónde se aplica**:
  - `app/Http/Controllers/Pedido/PedidoController.php:43` — `'id_estado_pedido' => 'required|integer|exists:estados_pedido,id_estado_pedido'`

---

## 4. Módulo de Repartidores

### 4.1 Máquina de estados de repartidores (3 estados)

- **Cómo funciona**: Los repartidores pueden estar en Disponible, En ruta o Inactivo. No hay validación de transiciones — cualquier estado puede cambiar a cualquier otro.
- **Dónde se aplica**:
  - `database/seeders/CatalogoSeeder.php:189-193` — Estados semilla
  - `app/Http/Controllers/Repartidor/RepartidorController.php:98-127` — `cambiarEstado()` sin restricciones

### 4.2 CI único del repartidor

- **Cómo funciona**: El número de carnet de identidad (`ci`) debe ser único en toda la tabla de repartidores, validado con `unique:repartidores,ci`.
- **Dónde se aplica**:
  - `app/Http/Controllers/Repartidor/RepartidorController.php:37` — Store
  - `app/Http/Controllers/Repartidor/RepartidorController.php:77` — Update (ignora el actual)

### 4.3 Un usuario = un repartidor (unique id_usuario)

- **Cómo funciona**: La columna `id_usuario` en `repartidores` tiene restricción UNIQUE, garantizando que un usuario del sistema puede estar asociado a máximo un registro de repartidor.
- **Dónde se aplica**:
  - `app/Http/Controllers/Repartidor/RepartidorController.php:36` — `'id_usuario' => 'required|integer|exists:usuarios,id_usuario|unique:repartidores,id_usuario'`

### 4.4 Licencia y extensión de CI obligatorias

- **Cómo funciona**: Al crear un repartidor, deben especificarse `id_licencia` e `id_extension_ci`, ambos deben existir en sus tablas catálogo correspondientes.
- **Dónde se aplica**:
  - `app/Http/Controllers/Repartidor/RepartidorController.php:38-39` — Validación
  - `database/seeders/CatalogoSeeder.php:208-226` — Licencias (A, B, C, P, Profesional) y Extensiones (LP, CBBA, SC, OR, PT, TJ, CH, BE, PD)

### 4.5 Al eliminar usuario, se elimina repartidor en cascada

- **Cómo funciona**: La FK de `repartidores.id_usuario` hacia `usuarios.id_usuario` tiene `onDelete('cascade')`, por lo que eliminar un usuario elimina automáticamente su registro de repartidor.
- **Dónde se aplica**:
  - Migración: `database/migrations/2024_01_02_000004_create_repartidores_table.php` — FK con `cascade`

### 4.6 Historial de estados de repartidor

- **Cómo funciona**: Mismo patrón que pedidos: al cambiar estado, se cierra el historial activo anterior y se crea uno nuevo con `fecha_inicio = now()`.
- **Dónde se aplica**:
  - `app/Http/Controllers/Repartidor/RepartidorController.php:106-121` — Mismo patrón que Pedido

---

## 5. Módulo de Vehículos

### 5.1 Máquina de estados de vehículos (3 estados)

- **Cómo funciona**: Los vehículos pueden estar en Operativo, En mantenimiento o Fuera de servicio. Sin validación de transiciones.
- **Dónde se aplica**:
  - `database/seeders/CatalogoSeeder.php:195-199` — Estados semilla
  - `app/Http/Controllers/Vehiculo/VehiculoController.php:96-125` — `cambiarEstado()`

### 5.2 Placa única del vehículo

- **Cómo funciona**: La placa debe ser única en la tabla `vehiculos`, validado con `unique:vehiculos,placa`.
- **Dónde se aplica**:
  - `app/Http/Controllers/Vehiculo/VehiculoController.php:36` — Store
  - `app/Http/Controllers/Vehiculo/VehiculoController.php:75` — Update

### 5.3 Modelo y capacidad obligatorios

- **Cómo funciona**: Al crear un vehículo, `id_modelo` e `id_capacidad` son obligatorios y deben existir en sus tablas correspondientes.
- **Dónde se aplica**:
  - `app/Http/Controllers/Vehiculo/VehiculoController.php:37-38` — Validación

### 5.4 Modelo único por marca (unique compuesto)

- **Cómo funciona**: En el catálogo de modelos, la combinación `(id_marca, nombre_modelo)` debe ser única. Esto permite que el mismo nombre de modelo exista bajo diferentes marcas, pero no bajo la misma.
- **Dónde se aplica**:
  - `app/Http/Controllers/CatalogoController.php:54-61` — Validación con `Rule::unique('modelos', 'nombre_modelo')->where(fn $q => $q->where('id_marca', $request->id_marca))`
  - Migración: `database/migrations/2024_01_02_000003_create_modelos_table.php` — Unique compuesto

### 5.5 Marca y modelo en cascada

- **Cómo funciona**: Si se elimina una marca, todos sus modelos asociados se eliminan en cascada (FK con `onDelete('cascade')`). No se puede eliminar un modelo si hay vehículos que lo referencian (FK con `onDelete('restrict')`).
- **Dónde se aplica**:
  - Migraciones: FK de modelos→marcas (`cascade`), vehículos→modelos (`restrict`)

### 5.6 Capacidad en kg única y mayor que cero

- **Cómo funciona**: El valor de capacidad en kg debe ser único, numérico, y mínimo 0. Valores semilla: 500, 1000, 2000, 3000, 5000 kg.
- **Dónde se aplica**:
  - `app/Http/Controllers/CatalogoController.php:62-65` — Validación `'required|numeric|min:0|unique:capacidades,capacidad_kg'`
  - `database/seeders/CatalogoSeeder.php:249-255` — Valores semilla

---

## 6. Módulo de Rutas y Logística

### 6.1 Nombre de ruta único

- **Cómo funciona**: El nombre de la ruta debe ser único en la tabla `rutas`, validado con `unique:rutas,nombre_ruta`.
- **Dónde se aplica**:
  - `app/Http/Controllers/Logistica/RutaController.php:24` — Store
  - `app/Http/Controllers/Logistica/RutaController.php:43` — Update

### 6.2 Paradas ordenadas dentro de una ruta

- **Cómo funciona**: Cada parada tiene un `orden_parada` que debe ser único dentro de la misma ruta (unique compuesto `(id_ruta, orden_parada)`). Al listar paradas, se ordenan por `orden_parada ASC`.
- **Dónde se aplica**:
  - `app/Http/Controllers/Logistica/RutaController.php:63` — `orderBy('orden_parada')`
  - `app/Http/Controllers/Logistica/RutaController.php:73` — `'orden_parada' => 'required|integer|min:1|unique:ruta_paradas,orden_parada,NULL,id_parada,id_ruta,' . $id`
  - Migración: `database/migrations/2024_01_03_000006_create_ruta_paradas_table.php:30` — `$table->unique(['id_ruta', 'orden_parada'])`

### 6.3 Hora estimada en formato H:i:s

- **Cómo funciona**: Tanto al crear como al actualizar una parada, `hora_estimada` debe cumplir el formato `H:i:s` (24 horas).
- **Dónde se aplica**:
  - `app/Http/Controllers/Logistica/RutaController.php:74` — `'hora_estimada' => 'required|date_format:H:i:s'`
  - `app/Http/Controllers/Logistica/RutaController.php:95` — Update

### 6.4 Eliminación de ruta elimina paradas en cascada

- **Cómo funciona**: La FK de `ruta_paradas.id_ruta` tiene `onDelete('cascade')`, por lo que eliminar una ruta elimina todas sus paradas. Pero la FK hacia farmacias es `restrict`.
- **Dónde se aplica**:
  - Migración: `database/migrations/2024_01_03_000006_create_ruta_paradas_table.php:18-23` — Cascade hacia rutas, Restrict hacia farmacias

### 6.5 Una parada no puede tener orden_parada duplicado en la misma ruta

- **Cómo funciona**: La validación usa una regla `unique` compuesta con condición adicional: `unique:ruta_paradas,orden_parada,NULL,id_parada,id_ruta,{id_ruta}`. Esto evita que dos paradas tengan el mismo orden dentro de la misma ruta, pero permite el mismo orden en rutas diferentes.
- **Dónde se aplica**:
  - `app/Http/Controllers/Logistica/RutaController.php:73` — Store
  - `app/Http/Controllers/Logistica/RutaController.php:94` — Update

---

## 7. Módulo de Control de Rutas

### 7.1 Una ruta solo puede ser controlada una vez por día

- **Cómo funciona**: El sistema verifica explícitamente si ya existe un `ControlRuta` con el mismo `id_ruta` y `fecha_ruta`. Si existe, retorna HTTP 422 con mensaje `"Ya existe un control para esta ruta en la fecha indicada."`. Además, hay un unique compuesto `(id_ruta, fecha_ruta)` a nivel de BD.
- **Dónde se aplica**:
  - `app/Http/Controllers/Logistica/ControlRutaController.php:54-60` — Validación explícita
  - Migración: `database/migrations/2024_01_03_000007_create_control_rutas_table.php:38` — `$table->unique(['id_ruta', 'fecha_ruta'])`

### 7.2 Asignación de repartidor y vehículo obligatoria

- **Cómo funciona**: Al crear un control de ruta, deben asignarse un repartidor y un vehículo específicos. Ambos deben existir en sus respectivas tablas. No hay validación cruzada (ej: que el repartidor tenga licencia para el vehículo).
- **Dónde se aplica**:
  - `app/Http/Controllers/Logistica/ControlRutaController.php:50-51` — `'id_repartidor' => 'required|integer|exists:repartidores,id_repartidor'`, `'id_vehiculo' => 'required|integer|exists:vehiculos,id_vehiculo'`

### 7.3 Registro de hora de salida obligatorio, hora de llegada opcional

- **Cómo funciona**: `hora_salida` es obligatoria al crear el control. `hora_llegada_real` es `nullable` en BD y se registra posteriormente mediante el endpoint dedicado `registrarLlegada`.
- **Dónde se aplica**:
  - `app/Http/Controllers/Logistica/ControlRutaController.php:49` — `'hora_salida' => 'required|date_format:H:i:s'`
  - `app/Http/Controllers/Logistica/ControlRutaController.php:109-122` — `registrarLlegada()`
  - Migración: `database/migrations/2024_01_03_000007_create_control_rutas_table.php:16` — `$table->time('hora_llegada_real')->nullable();`

### 7.4 Actualización limitada del control de ruta

- **Cómo funciona**: Al actualizar un control de ruta, solo se permite modificar `hora_salida`, `id_repartidor` e `id_vehiculo`. No se puede cambiar la ruta ni la fecha.
- **Dónde se aplica**:
  - `app/Http/Controllers/Logistica/ControlRutaController.php:87-91` — Validación con `sometimes`

### 7.5 Fechas en control de ruta con filtro por rango

- **Cómo funciona**: El endpoint `GET /controles-ruta` permite filtrar por rango de fechas (`fecha_desde`, `fecha_hasta`) y por `id_ruta`, `fecha_ruta`, `id_repartidor`, `id_vehiculo`. El ordenamiento es por `fecha_ruta DESC` y luego `hora_salida DESC`.
- **Dónde se aplica**:
  - `app/Http/Controllers/Logistica/ControlRutaController.php:15-41` — Filtros y ordenamiento

---

## 8. Módulo de Despachos

### 8.1 Máquina de estados de despachos (4 estados)

- **Cómo funciona**: Los despachos pueden estar en Pendiente, En camino, Entregado o Fallido. Sin validación de transiciones.
- **Dónde se aplica**:
  - `database/seeders/CatalogoSeeder.php:201-206` — Estados semilla
  - `app/Http/Controllers/Despacho/DespachoController.php:110-139` — `cambiarEstado()`

### 8.2 Fecha/hora de despacho automática

- **Cómo funciona**: Al crear un despacho, `fecha_hora_despacho` se fuerza a `now()` desde el controlador y en BD tiene `useCurrent()` como valor por defecto.
- **Dónde se aplica**:
  - `app/Http/Controllers/Despacho/DespachoController.php:50` — `$data['fecha_hora_despacho'] = now();`
  - Migración: `database/migrations/2024_01_03_000008_create_despachos_table.php:16` — `->useCurrent()`

### 8.3 Parada y control de ruta obligatorios

- **Cómo funciona**: Al crear un despacho, debe asociarse a una parada (`id_parada`) y a un control de ruta (`id_control_ruta`), ambos existentes en sus tablas.
- **Dónde se aplica**:
  - `app/Http/Controllers/Despacho/DespachoController.php:45-46` — Validación

### 8.4 Actualización limitada del despacho

- **Cómo funciona**: Al actualizar un despacho, solo se permite modificar `id_parada` e `id_control_ruta`. `id_pedido`, `id_estado_despacho` y `fecha_hora_despacho` no pueden modificarse.
- **Dónde se aplica**:
  - `app/Http/Controllers/Despacho/DespachoController.php:89-91` — Solo `id_parada` e `id_control_ruta` (sometimes)

### 8.5 Eliminación de despacho elimina incidencias y evidencias en cascada

- **Cómo funciona**: Las FK de `incidencias` y `evidencias_entrega` hacia `despachos` tienen `onDelete('cascade')`, por lo que eliminar un despacho elimina todas sus incidencias y evidencias asociadas automáticamente.
- **Dónde se aplica**:
  - Migraciones: FK de incidencias → despachos (`cascade`), evidencias → despachos (`cascade`)

### 8.6 Relaciones foráneas restrictivas de despacho

- **Cómo funciona**: Las FK de despacho hacia pedido, ruta_paradas y control_rutas usan `onDelete('restrict')`, evitando que se eliminen registros padre si tienen despachos asociados.
- **Dónde se aplica**:
  - Migración: `database/migrations/2024_01_03_000008_create_despachos_table.php:19-36` — Tres FK con `restrict`

### 8.7 Búsqueda de despachos por múltiples criterios

- **Cómo funciona**: El endpoint `GET /despachos` permite filtrar por `id_estado_despacho`, `id_pedido`, `id_control_ruta`, `fecha_desde`, `fecha_hasta`. Ordena por `fecha_hora_despacho DESC`.
- **Dónde se aplica**:
  - `app/Http/Controllers/Despacho/DespachoController.php:16-38` — Filtros

---

## 9. Módulo de Evidencias e Incidencias

### 9.1 Archivos de evidencia: solo formatos de imagen/PDF, máx 2MB

- **Cómo funciona**: Al cargar un archivo de evidencia, se valida que sea de tipo `jpg, jpeg, png, gif, pdf` y que no exceda 2048 KB (2 MB). Se almacena en el disco `public` dentro de la carpeta `evidencias`.
- **Dónde se aplica**:
  - `app/Http/Controllers/Despacho/EvidenciaController.php:26` — `'archivo' => 'required|file|mimes:jpg,jpeg,png,gif,pdf|max:2048'`
  - `app/Http/Controllers/Despacho/EvidenciaController.php:29` — `$request->file('archivo')->store('evidencias', 'public')`
  - `app/Http/Controllers/Despacho/EvidenciaController.php:57` — Validación en update (sometimes)

### 9.2 Evidencias siempre asociadas a un despacho

- **Cómo funciona**: Todas las operaciones de evidencias están anidadas bajo `/despachos/{despacho}/evidencias`. El controlador siempre verifica que el despacho exista y filtra evidencias por `id_despacho`.
- **Dónde se aplica**:
  - `routes/api.php:77-81` — Rutas anidadas
  - `app/Http/Controllers/Despacho/EvidenciaController.php:14, 22, 45, 53, 74` — Filtrado por despacho

### 9.3 Fecha de incidencia automática

- **Cómo funciona**: Al crear una incidencia, `fecha_incidencia` se establece automáticamente a `now()` desde el controlador.
- **Dónde se aplica**:
  - `app/Http/Controllers/Despacho/IncidenciaController.php:30` — `$data['fecha_incidencia'] = now();`

### 9.4 Tipos de incidencia predefinidos

- **Cómo funciona**: Las incidencias deben clasificarse según tipos predefinidos: Retraso en la entrega, Producto dañado, Direccion incorrecta, Cliente ausente, Fallo mecanico, Otro.
- **Dónde se aplica**:
  - `database/seeders/CatalogoSeeder.php:257-264` — Tipos semilla
  - `app/Http/Controllers/Despacho/IncidenciaController.php:25` — `'id_tipo_incidencia' => 'required|integer|exists:tipos_incidencia,id_tipo_incidencia'`

### 9.5 Tipos de evidencia predefinidos

- **Cómo funciona**: Las evidencias deben clasificarse según tipos predefinidos: Foto de entrega, Firma digital, Documento adjunto.
- **Dónde se aplica**:
  - `database/seeders/CatalogoSeeder.php:266-270` — Tipos semilla
  - `app/Http/Controllers/Despacho/EvidenciaController.php:25` — `'id_tipo_evidencia' => 'required|integer|exists:tipos_evidencia,id_tipo_evidencia'`

---

## 10. Módulo de Reportes

### 10.1 Reporte resumen: conteo de todas las entidades

- **Cómo funciona**: El endpoint GET `/reportes/resumen` devuelve los totales de: usuarios, farmacias, pedidos, repartidores, vehículos, rutas, despachos e incidencias. Sin filtros ni parámetros.
- **Dónde se aplica**:
  - `app/Http/Controllers/Reportes/ReporteController.php:19-31`
  - `routes/api.php:85` — `'permission:Reportes,acceder'`

### 10.2 Reportes agrupados por estado

- **Cómo funciona**: Los reportes de "pedidos por estado", "despachos por estado", "repartidores por estado" y "vehículos por estado" agrupan por el campo `id_estado_*` y devuelven el conteo con el nombre del estado.
- **Dónde se aplica**:
  - `app/Http/Controllers/Reportes/ReporteController.php:33-48` — `pedidosPorEstado()`
  - `app/Http/Controllers/Reportes/ReporteController.php:50-65` — `despachosPorEstado()`
  - `app/Http/Controllers/Reportes/ReporteController.php:83-98` — `repartidoresPorEstado()`
  - `app/Http/Controllers/Reportes/ReporteController.php:100-115` — `vehiculosPorEstado()`

### 10.3 Reporte de pedidos por día (últimos N días)

- **Cómo funciona**: Agrupa pedidos por `DATE(fecha_pedido)` para los últimos 30 días (por defecto, configurable con parámetro `dias`).
- **Dónde se aplica**:
  - `app/Http/Controllers/Reportes/ReporteController.php:67-81` — `pedidosPorDia()`

### 10.4 Reporte de incidencias por tipo

- **Cómo funciona**: Agrupa incidencias por `id_tipo_incidencia` y devuelve el conteo con el nombre del tipo.
- **Dónde se aplica**:
  - `app/Http/Controllers/Reportes/ReporteController.php:117-132` — `incidenciasPorTipo()`

### 10.5 Permisos específicos para reportes

- **Cómo funciona**: El reporte resumen requiere permiso `Reportes,acceder`. Todos los demás reportes requieren `Reportes,listar`.
- **Dónde se aplica**:
  - `routes/api.php:84-92` — `->middleware('permission:Reportes,acceder')` para resumen, `->middleware('permission:Reportes,listar')` para los demás

---

## 11. Módulo de Catálogos

### 11.1 CRUD genérico para 17 catálogos

- **Cómo funciona**: Un solo `CatalogoController` maneja 17 catálogos diferentes mediante un mapa de configuración que define: modelo, campo, tabla, ID, longitud máxima, si es numérico y si tiene FK. Las validaciones son genéricas según el tipo.
- **Dónde se aplica**:
  - `app/Http/Controllers/CatalogoController.php:27-45` — Mapa de 17 catálogos
  - Catálogos: estados-usuario, roles, modulos, acciones, tablas-sistema, estados-pedido, estados-repartidor, estados-vehiculo, estados-despacho, extensiones-ci, licencias, marcas, modelos, capacidades, tipos-incidencia, tipos-evidencia, cargos

### 11.2 Validación genérica por tipo de catálogo

- **Cómo funciona**: Para catálogos string: `required|string|max:{configMax}|unique:{table},{field}`. Para numéricos (capacidades): `required|numeric|min:0|unique:{table},{field}`. Para modelos: validación especial con `id_marca` FK y unique compuesto.
- **Dónde se aplica**:
  - `app/Http/Controllers/CatalogoController.php:54-70` — Store con 3 ramas de validación
  - `app/Http/Controllers/CatalogoController.php:86-104` — Update con 3 ramas

### 11.3 Catálogo de modelos por marca

- **Cómo funciona**: El endpoint `GET /catalogos/modelos-por-marca/{idMarca}` devuelve los modelos filtrados por marca, útil para interfaces que necesitan listas dependientes (selects en cascada).
- **Dónde se aplica**:
  - `routes/api.php:96` — Ruta
  - `app/Http/Controllers/CatalogoController.php:124-127` — `modelosPorMarca()`

### 11.4 Catálogos de solo lectura para usuarios sin permisos de escritura

- **Cómo funciona**: Cualquier usuario autenticado puede leer todos los catálogos (GET). Solo los usuarios con permisos en el módulo Usuarios pueden crear, actualizar o eliminar registros de catálogo.
- **Dónde se aplica**:
  - `routes/api.php:95-119` — GET sin middleware adicional, POST/PUT/DELETE con `->middleware('permission:Usuarios')`

---

## 12. Reglas Transversales

### 12.1 Formato de respuesta API unificado

- **Cómo funciona**: Todas las respuestas exitosas usan `{ success: true, message: string, data: ... }`. Las respuestas de error usan `{ success: false, message: string, errors?: ... }`. Las respuestas paginadas incluyen `meta: { current_page, last_page, per_page, total }`.
- **Dónde se aplica**:
  - `app/Http/Controllers/ApiController.php:9-44` — Métodos `jsonResponse()`, `errorResponse()`, `paginatedResponse()`

### 12.2 Paginación por defecto: 15 registros

- **Cómo funciona**: Todos los endpoints de listado usan `$request->per_page ?? 15` como tamaño de página predeterminado.
- **Dónde se aplica**:
  - Múltiples controladores: `UsuarioController:31`, `FarmaciaController:25`, `PedidoController:34`, `RepartidorController:29`, `VehiculoController:29`, `RutaController:17`, `ControlRutaController:40`, `DespachoController:37`

### 12.3 Patrón de historial de estados (4 entidades)

- **Cómo funciona**: Las entidades Pedido, Repartidor, Vehículo y Despacho comparten el mismo patrón: (1) tienen una tabla de historial con `fecha_inicio` (default: now), `fecha_fin` (nullable), (2) al cambiar estado se cierra el registro activo y se crea uno nuevo, (3) el historial activo es aquel con `fecha_fin IS NULL`.
- **Dónde se aplica**:
  - `app/Http/Controllers/Pedido/PedidoController.php:123-138` — Implementación en Pedido
  - `app/Http/Controllers/Repartidor/RepartidorController.php:106-121` — Implementación en Repartidor
  - `app/Http/Controllers/Vehiculo/VehiculoController.php:104-119` — Implementación en Vehículo
  - `app/Http/Controllers/Despacho/DespachoController.php:118-133` — Implementación en Despacho

### 12.4 Política de llaves foráneas (RESTRICT vs CASCADE)

- **Cómo funciona**: Las entidades principales (pedidos, despachos, control_rutas, etc.) usan `onDelete('restrict')` para proteger la integridad referencial. Las entidades secundarias (contactos, paradas, historiales, incidencias, evidencias) usan `onDelete('cascade')` para limpieza automática.
- **Dónde se aplica**:
  - Migraciones: Ver tabla resumen en cada migración
  - **RESTRICT**: pedidos→farmacias/usuarios/estados, despachos→pedidos/ruta_paradas/control_rutas/estados, control_rutas→rutas/repartidores/vehiculos, vehiculos→modelos/capacidades/estados, repartidores→extensiones_ci/licencias/estados
  - **CASCADE**: modelos→marcas, ruta_paradas→rutas, contactos→farmacias, repartidores→usuarios, usuario_roles→usuarios, historiales→entidad padre, incidencias→despachos, evidencias→despachos

### 12.5 Sin timestamps automáticos de Laravel

- **Cómo funciona**: La gran mayoría de modelos tienen `$timestamps = false`, utilizando campos manuales como `fecha_creacion`, `ultimo_acceso`, `fecha_pedido`, `fecha_hora_despacho`. La excepción es `Farmacia` que usa los timestamps por defecto de Laravel (`created_at`, `updated_at`).
- **Dónde se aplica**:
  - `app/Models/Seguridad/Usuario.php:16` — `public $timestamps = false;`
  - Verificar cada modelo en `app/Models/`

### 12.6 Sin soft deletes

- **Cómo funciona**: Ninguna entidad del sistema utiliza soft deletes. Todas las eliminaciones son físicas (hard delete). La única protección son las restricciones de llaves foráneas y las capturas de excepción 23000.
- **Dónde se aplica**:
  - Ningún modelo usa `SoftDeletes` trait
  - Ninguna migración incluye `softDeletes()`

### 12.7 Sin validación de transiciones entre estados

- **Cómo funciona**: En las 4 máquinas de estado (Pedido, Repartidor, Vehículo, Despacho), no hay lógica que valide si una transición es válida. Por ejemplo, un pedido puede pasar de "Entregado" a "Cancelado" o viceversa sin restricciones.
- **Dónde se aplica**:
  - Ausencia de validación en todos los métodos `cambiarEstado()` de los 4 controladores

### 12.8 Sin eventos de modelo (boot methods)

- **Cómo funciona**: Ningún modelo utiliza eventos de Eloquent (creating, created, updating, etc.) para lógica automática como logging, cambios en cascada entre entidades o notificaciones.
- **Dónde se aplica**:
  - Ausencia de `boot()` methods en todos los modelos

### 12.9 Sin inventario ni productos en pedidos

- **Cómo funciona**: La entidad `Pedido` no tiene líneas de detalle, productos, cantidades ni precios. Es un registro simple que asocia una farmacia con un usuario y un estado, con observaciones opcionales en texto libre.
- **Dónde se aplica**:
  - Migración: `database/migrations/2024_01_03_000001_create_pedidos_table.php` — Sin tabla de detalle de pedido
  - Controlador: `PedidoController` — Sin lógica de productos

### 12.10 Protección de token inválido en frontend

- **Cómo funciona**: El frontend valida que el token almacenado tenga más de 20 caracteres y no sea "undefined"/"null" antes de enviarlo. Si el servidor responde 401, el frontend limpia el almacenamiento local y redirige al login.
- **Dónde se aplica**:
  - `frontenda_distribuidora/js/config.js:21-28` — Validación de token
  - `frontenda_distribuidora/js/config.js:52-63` — Manejo de 401

### 12.11 Sidebar dinámico según permisos

- **Cómo funciona**: El menú lateral del frontend se filtra según los permisos del usuario. Cada ítem tiene un `moduleKey` y solo se muestra si el usuario tiene el permiso `{moduleKey},acceder`. Si el usuario no tiene ningún permiso, se recargan desde el servidor.
- **Dónde se aplica**:
  - `frontenda_distribuidora/js/layout.js:55-59` — Filtro `hasPermission(item.moduleKey, 'acceder')`
  - `frontenda_distribuidora/js/layout.js:32-34` — Recarga de permisos si está vacío

### 12.12 Dashboard verifica permiso de acceso

- **Cómo funciona**: El frontend del Dashboard verifica que el usuario tenga el permiso `Dashboard,acceder`. Si no, muestra un mensaje de error y no carga los datos.
- **Dónde se aplica**:
  - `frontenda_distribuidora/js/modules/dashboard.js:4-7` — `if (!hasPermission('Dashboard', 'acceder')) { ... }`

---

## Resumen de Máquinas de Estado

| Entidad | Estados Posibles | Validación de Transiciones |
|---------|-----------------|---------------------------|
| **Pedido** | Pendiente → Aprobado → En preparacion → Despachado → Entregado / Cancelado | ❌ No validada |
| **Repartidor** | Disponible → En ruta → Inactivo | ❌ No validada |
| **Vehículo** | Operativo → En mantenimiento → Fuera de servicio | ❌ No validada |
| **Despacho** | Pendiente → En camino → Entregado / Fallido | ❌ No validada |

## Resumen de Roles y Permisos Base

| Rol | Alcance |
|-----|---------|
| **Administrador** (id=1) | Todos los permisos de todos los módulos |
| **Supervisor** (id=2) | Dashboard, Reportes, Farmacias (full), Pedidos (full), Repartidores (full), Vehículos (full), Rutas (full), Control Rutas (full), Despachos (full) |
| **Operador** (id=3) | Dashboard, Reportes (listar), Farmacias (acceder, listar), Pedidos (full excepto eliminar), Repartidores (acceder, listar), Vehículos (acceder, listar), Rutas (acceder, listar), Control Rutas (crear, editar, listar), Despachos (full excepto incidencias/evidencias) |
| **Repartidor** (id=4) | Dashboard, Rutas (acceder, listar), Control Rutas (acceder, listar, registrar-llegada), Despachos (acceder, listar) |
