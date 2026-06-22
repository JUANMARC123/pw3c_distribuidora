# Documentación de Modelos - Pw3c Distribuidora

> **Framework:** Laravel 9 (Eloquent ORM)
> **Total de modelos:** 67

---

## Índice

1. [Seguridad](#1-seguridad)
   - [Usuario](#usuario)
   - [EstadoUsuario](#estadousuario)
   - [Rol](#rol)
   - [Permiso](#permiso)
   - [Modulo](#modulo)
   - [Accion](#accion)
   - [TablaSistema](#tablasistema)
   - [Auditoria](#auditoria)
   - [SesionUsuario](#sesionusuario)
2. [Farmacia](#2-farmacia)
   - [Farmacia](#farmacia)
   - [EstadoFarmacia](#estadofarmacia)
   - [ContactoFarmacia](#contactofarmacia)
   - [Cargo](#cargo)
3. [Repartidor](#3-repartidor)
   - [Repartidor](#repartidor)
   - [EstadoRepartidor](#estadorepartidor)
   - [HistorialEstadoRepartidor](#historialestadorepartidor)
   - [Licencia](#licencia)
   - [ExtensionCI](#extensionci)
4. [Vehículo](#4-vehículo)
   - [Vehiculo](#vehiculo)
   - [EstadoVehiculo](#estadovehiculo)
   - [HistorialEstadoVehiculo](#historialestadovehiculo)
   - [Marca](#marca)
   - [Modelo](#modelo)
   - [Capacidad](#capacidad)
5. [Pedido](#5-pedido)
   - [Pedido](#pedido)
   - [DetallePedido](#detallepedido)
   - [EstadoPedido](#estadopedido)
   - [HistorialEstadoPedido](#historialestadopedido)
6. [Despacho](#6-despacho)
   - [Despacho](#despacho)
   - [EstadoDespacho](#estadodespacho)
   - [HistorialEstadoDespacho](#historialestadodespacho)
7. [Logística](#7-logística)
   - [Ruta](#ruta)
   - [RutaParada](#rutaparada)
   - [ControlRuta](#controlruta)
8. [Evidencia](#8-evidencia)
   - [EvidenciaEntrega](#evidenciaentrega)
   - [Incidencia](#incidencia)
   - [TipoEvidencia](#tipoevidencia)
   - [TipoIncidencia](#tipoincidencia)
9. [Medicamento](#9-medicamento)
   - [Categoria](#categoria)
   - [Laboratorio](#laboratorio)
   - [UnidadMedida](#unidadmedida)
   - [Presentacion](#presentacion)
   - [Producto](#producto-medicamento)
   - [Lote](#lote)
10. [Inventario](#10-inventario)
    - [TipoMovimiento](#tipomovimiento)
    - [Inventario](#inventario)
    - [MovimientoInventario](#movimientoinventario)
    - [Almacen](#almacen)
    - [UbicacionAlmacen](#ubicacionalmacen)
11. [Compra](#11-compra)
   - [Proveedor](#proveedor)
   - [ContactoProveedor](#contactoproveedor)
   - [EstadoOrdenCompra](#estadoordencompra)
   - [OrdenCompra](#ordencompra)
   - [DetalleCompra](#detallecompra)
12. [Devolucion](#12-devolucion)
   - [TipoDevolucion](#tipodevolucion)
   - [EstadoDevolucion](#estadodevolucion)
   - [Devolucion](#devolucion)
   - [DetalleDevolucion](#detalledevolucion)
   - [HistorialEstadoDevolucion](#historialestadodevolucion)
13. [Promocion](#13-promocion)
    - [TipoPromocion](#tipopromocion)
    - [Promocion](#promocion)
    - [ProductoPromocion](#productopromocion)
14. [Venta](#14-venta)
    - [EstadoVenta](#estadoventa)
    - [Venta](#venta)
    - [DetalleVenta](#detalleventa)
    - [MetodoPago](#metodopago)
    - [Pago](#pago)
15. [Leyenda](#15-leyenda)

---
## 1. Seguridad

### Usuario

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_usuario` | PK | Identificador único |
| `nombre` | string(100) | Nombre del usuario |
| `apellido` | string(100) | Apellido del usuario |
| `email` | string(180) | Correo electrónico (**unique**) |
| `password_hash` | string | Contraseña hasheada (hidden) |
| `telefono` | string(20) | Teléfono |
| `id_estado_usuario` | FK→`estados_usuario` | Estado del usuario |
| `fecha_creacion` | datetime | Fecha de creación |
| `fecha_bloqueo` | datetime | Fecha de bloqueo (nullable) |
| `ultimo_acceso` | datetime | Último acceso (nullable) |

**Tabla:** `usuarios` | **PK:** `id_usuario` | **Timestamps:** No | **Traits:** HasApiTokens, HasFactory, Notifiable

**Hidden:** `password_hash`, `remember_token`

**Casts:** `fecha_creacion:datetime`, `fecha_bloqueo:datetime`, `ultimo_acceso:datetime`

**Métodos destacados:**
- `getAuthPassword(): string` — Retorna `password_hash` en lugar del campo `password` convencional de Laravel
- `hasPermission(string $modulo, string $accion): bool` — Itera roles y permisos para verificar acceso

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `estado()` | `EstadoUsuario` | `id_estado_usuario` |
| belongsToMany | `roles()` | `Rol` | pivot: `usuario_roles` |
| hasMany | `pedidos()` | `Pedido` | `id_usuario` |
| hasOne | `repartidor()` | `Repartidor` | `id_usuario` |
| hasMany | `sesiones()` | `SesionUsuario` | `id_usuario` |
| hasMany | `auditorias()` | `Auditoria` | `id_usuario` |

---

### EstadoUsuario

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_estado_usuario` | PK (tinyInteger) | Identificador único |
| `nombre_estado` | string(50) | Nombre del estado (**unique**) |

**Tabla:** `estados_usuario` | **PK:** `id_estado_usuario` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `usuarios()` | `Usuario` | `id_estado_usuario` |

---

### Rol

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_rol` | PK (tinyInteger) | Identificador único |
| `nombre` | string(50) | Nombre del rol (**unique**) |

**Tabla:** `roles` | **PK:** `id_rol` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsToMany | `usuarios()` | `Usuario` | pivot: `usuario_roles` |
| belongsToMany | `permisos()` | `Permiso` | pivot: `rol_permiso` |

---

### Permiso

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_permiso` | PK | Identificador único |
| `id_modulo` | FK→`modulos` | Módulo asociado |
| `id_accion` | FK→`acciones` | Acción asociada |

**Tabla:** `permisos` | **PK:** `id_permiso` | **Timestamps:** No

**Unique compuesto:** `(id_modulo, id_accion)`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `modulo()` | `Modulo` | `id_modulo` |
| belongsTo | `accion()` | `Accion` | `id_accion` |
| belongsToMany | `roles()` | `Rol` | pivot: `rol_permiso` |

---

### Modulo

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_modulo` | PK (tinyInteger) | Identificador único |
| `nombre` | string(50) | Nombre del módulo (**unique**) |

**Tabla:** `modulos` | **PK:** `id_modulo` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `permisos()` | `Permiso` | `id_modulo` |

---

### Accion

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_accion` | PK (tinyInteger) | Identificador único |
| `nombre` | string(50) | Nombre de la acción (**unique**) |

**Tabla:** `acciones` | **PK:** `id_accion` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `permisos()` | `Permiso` | `id_accion` |

---

### TablaSistema

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_tabla` | PK (tinyInteger) | Identificador único |
| `nombre` | string(100) | Nombre de la tabla del sistema auditada (**unique**) |

**Tabla:** `tablas_sistema` | **PK:** `id_tabla` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `auditorias()` | `Auditoria` | `id_tabla` |

---

### Auditoria

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_auditoria` | PK | Identificador único |
| `id_usuario` | FK→`usuarios` | Usuario que realizó la acción |
| `id_accion` | FK→`acciones` | Acción realizada |
| `id_tabla` | FK→`tablas_sistema` | Tabla afectada |
| `registro_id` | integer | ID del registro afectado |
| `fecha_hora` | datetime | Fecha y hora de la auditoría |

**Tabla:** `auditorias` | **PK:** `id_auditoria` | **Timestamps:** No

**Casts:** `fecha_hora:datetime`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `usuario()` | `Usuario` | `id_usuario` |
| belongsTo | `accion()` | `Accion` | `id_accion` |
| belongsTo | `tabla()` | `TablaSistema` | `id_tabla` |

---

### SesionUsuario

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_sesion` | PK | Identificador único |
| `id_usuario` | FK→`usuarios` | Usuario de la sesión |
| `fecha_inicio` | datetime | Inicio de sesión |
| `fecha_fin` | datetime | Fin de sesión (nullable) |

**Tabla:** `sesiones_usuario` | **PK:** `id_sesion` | **Timestamps:** No

**Casts:** `fecha_inicio:datetime`, `fecha_fin:datetime`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `usuario()` | `Usuario` | `id_usuario` |

---

## 2. Farmacia

### Farmacia

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_farmacia` | PK | Identificador único |
| `nombre` | string(150) | Nombre de la farmacia |
| `direccion` | text | Dirección |
| `telefono` | string(20) | Teléfono |
| `email` | string(180) | Correo electrónico (**unique**, nullable) |
| `latitud` | decimal(10,7) | Latitud de ubicación (entre -90 y 90) |
| `longitud` | decimal(10,7) | Longitud de ubicación (entre -180 y 180) |
| `id_estado_farmacia` | FK→`estados_farmacia` | Estado de la farmacia (nullable) |
| `descripcion` | text | Descripción (nullable) |
| `es_24_horas` | boolean | Indica si la farmacia abre 24 horas |
| `horario_apertura` | time | Hora de apertura (nullable) |
| `horario_cierre` | time | Hora de cierre (nullable) |
| `fecha_verificacion` | datetime | Fecha de última verificación (nullable) |

**Tabla:** `farmacias` | **PK:** `id_farmacia` | **Timestamps:** Sí (created_at, updated_at) | **Traits:** HasFactory

**CHECK:** `horario_cierre > horario_apertura`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `estado()` | `EstadoFarmacia` | `id_estado_farmacia` |
| hasMany | `contactos()` | `ContactoFarmacia` | `id_farmacia` |
| hasMany | `pedidos()` | `Pedido` | `id_farmacia` |
| hasMany | `paradas()` | `RutaParada` | `id_farmacia` |
| hasMany | `almacenes()` | `Almacen` | `id_farmacia` |

---

### EstadoFarmacia

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_estado_farmacia` | PK (tinyIncrements) | Identificador único |
| `nombre_estado` | string(50) | Nombre del estado (**unique**) |

**Tabla:** `estados_farmacia` | **PK:** `id_estado_farmacia` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `farmacias()` | `Farmacia` | `id_estado_farmacia` |

---

### ContactoFarmacia

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_contacto` | PK | Identificador único |
| `id_farmacia` | FK→`farmacias` | Farmacia asociada |
| `nombre_contacto` | string(150) | Nombre del contacto |
| `id_cargo` | FK→`cargos` | Cargo del contacto |
| `telefono` | string(20) | Teléfono del contacto |
| `email` | string(180) | Correo electrónico (nullable) |

**Tabla:** `contactos_farmacia` | **PK:** `id_contacto` | **Timestamps:** No | **Traits:** HasFactory

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `farmacia()` | `Farmacia` | `id_farmacia` |
| belongsTo | `cargo()` | `Cargo` | `id_cargo` |

---

### Cargo

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_cargo` | PK (tinyInteger) | Identificador único |
| `nombre_cargo` | string(100) | Nombre del cargo (**unique**) |

**Tabla:** `cargos` | **PK:** `id_cargo` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `contactos()` | `ContactoFarmacia` | `id_cargo` |

---

## 3. Repartidor

### Repartidor

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_repartidor` | PK | Identificador único |
| `id_usuario` | FK→`usuarios` | Usuario asociado (**unique**) |
| `ci` | string(20) | Cédula de identidad (**unique**) |
| `id_extension_ci` | FK→`extensiones_ci` | Extensión del CI |
| `id_licencia` | FK→`licencias` | Categoría de licencia |
| `id_estado_repartidor` | FK→`estados_repartidor` | Estado del repartidor |

**Tabla:** `repartidores` | **PK:** `id_repartidor` | **Timestamps:** No | **Traits:** HasFactory

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `usuario()` | `Usuario` | `id_usuario` |
| belongsTo | `extensionCi()` | `ExtensionCI` | `id_extension_ci` |
| belongsTo | `licencia()` | `Licencia` | `id_licencia` |
| belongsTo | `estado()` | `EstadoRepartidor` | `id_estado_repartidor` |
| hasMany | `historiales()` | `HistorialEstadoRepartidor` | `id_repartidor` |
| hasMany | `controlRutas()` | `ControlRuta` | `id_repartidor` |

---

### EstadoRepartidor

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_estado_repartidor` | PK (tinyInteger) | Identificador único |
| `nombre_estado` | string(50) | Nombre del estado (**unique**) |

**Tabla:** `estados_repartidor` | **PK:** `id_estado_repartidor` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `repartidores()` | `Repartidor` | `id_estado_repartidor` |
| hasMany | `historiales()` | `HistorialEstadoRepartidor` | `id_estado_repartidor` |

---

### HistorialEstadoRepartidor

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_historial` | PK | Identificador único |
| `id_repartidor` | FK→`repartidores` | Repartidor asociado |
| `id_estado_repartidor` | FK→`estados_repartidor` | Estado asignado |
| `fecha_inicio` | datetime | Fecha de inicio del estado |
| `fecha_fin` | datetime | Fecha de fin del estado (nullable) |

**Tabla:** `historial_estado_repartidor` | **PK:** `id_historial` | **Timestamps:** No

**Casts:** `fecha_inicio:datetime`, `fecha_fin:datetime`

**Unique compuesto:** `(id_repartidor, fecha_inicio)`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `repartidor()` | `Repartidor` | `id_repartidor` |
| belongsTo | `estado()` | `EstadoRepartidor` | `id_estado_repartidor` |

---

### Licencia

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_licencia` | PK (tinyInteger) | Identificador único |
| `categoria` | string(20) | Categoría de licencia (**unique**) |

**Tabla:** `licencias` | **PK:** `id_licencia` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `repartidores()` | `Repartidor` | `id_licencia` |

---

### ExtensionCI

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_extension_ci` | PK (tinyInteger) | Identificador único |
| `nombre_extension` | string(10) | Extensión del CI (**unique**) |

**Tabla:** `extensiones_ci` | **PK:** `id_extension_ci` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `repartidores()` | `Repartidor` | `id_extension_ci` |

---

## 4. Vehículo

### Vehiculo

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_vehiculo` | PK | Identificador único |
| `placa` | string(20) | Placa del vehículo (**unique**) |
| `id_modelo` | FK→`modelos` | Modelo del vehículo |
| `id_capacidad` | FK→`capacidades` | Capacidad de carga |
| `id_estado_vehiculo` | FK→`estados_vehiculo` | Estado del vehículo |

**Tabla:** `vehiculos` | **PK:** `id_vehiculo` | **Timestamps:** No | **Traits:** HasFactory

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `modelo()` | `Modelo` | `id_modelo` |
| belongsTo | `capacidad()` | `Capacidad` | `id_capacidad` |
| belongsTo | `estado()` | `EstadoVehiculo` | `id_estado_vehiculo` |
| hasMany | `historiales()` | `HistorialEstadoVehiculo` | `id_vehiculo` |
| hasMany | `controlRutas()` | `ControlRuta` | `id_vehiculo` |

---

### EstadoVehiculo

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_estado_vehiculo` | PK (tinyInteger) | Identificador único |
| `nombre_estado` | string(50) | Nombre del estado (**unique**) |

**Tabla:** `estados_vehiculo` | **PK:** `id_estado_vehiculo` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `vehiculos()` | `Vehiculo` | `id_estado_vehiculo` |
| hasMany | `historiales()` | `HistorialEstadoVehiculo` | `id_estado_vehiculo` |

---

### HistorialEstadoVehiculo

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_historial` | PK | Identificador único |
| `id_vehiculo` | FK→`vehiculos` | Vehículo asociado |
| `id_estado_vehiculo` | FK→`estados_vehiculo` | Estado asignado |
| `fecha_inicio` | datetime | Fecha de inicio del estado |
| `fecha_fin` | datetime | Fecha de fin del estado (nullable) |

**Tabla:** `historial_estado_vehiculo` | **PK:** `id_historial` | **Timestamps:** No

**Casts:** `fecha_inicio:datetime`, `fecha_fin:datetime`

**Unique compuesto:** `(id_vehiculo, fecha_inicio)`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `vehiculo()` | `Vehiculo` | `id_vehiculo` |
| belongsTo | `estado()` | `EstadoVehiculo` | `id_estado_vehiculo` |

---

### Marca

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_marca` | PK (tinyIncrements) | Identificador único |
| `nombre_marca` | string(50) | Nombre de la marca (**unique**) |

**Tabla:** `marcas` | **PK:** `id_marca` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `modelos()` | `Modelo` | `id_marca` |

---

### Modelo

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_modelo` | PK (smallIncrements) | Identificador único |
| `id_marca` | FK→`marcas` | Marca asociada |
| `nombre_modelo` | string(100) | Nombre del modelo |

**Tabla:** `modelos` | **PK:** `id_modelo` | **Timestamps:** No

**Unique compuesto:** `(id_marca, nombre_modelo)`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `marca()` | `Marca` | `id_marca` |
| hasMany | `vehiculos()` | `Vehiculo` | `id_modelo` |

---

### Capacidad

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_capacidad` | PK (tinyInteger) | Identificador único |
| `capacidad_kg` | decimal(8,2) | Capacidad en kilogramos (**unique**, >= 0) |

**Tabla:** `capacidades` | **PK:** `id_capacidad` | **Timestamps:** No

**Casts:** `capacidad_kg:decimal:2`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `vehiculos()` | `Vehiculo` | `id_capacidad` |

---

## 5. Pedido

### Pedido

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_pedido` | PK | Identificador único |
| `id_farmacia` | FK→`farmacias` | Farmacia destino |
| `id_usuario` | FK→`usuarios` | Usuario que registró |
| `id_estado_pedido` | FK→`estados_pedido` | Estado del pedido |
| `fecha_pedido` | timestamp | Fecha del pedido (default: now) |
| `observaciones` | text | Observaciones (nullable) |

**Tabla:** `pedidos` | **PK:** `id_pedido` | **Timestamps:** No | **Traits:** HasFactory

**Casts:** `fecha_pedido:datetime`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `farmacia()` | `Farmacia` | `id_farmacia` |
| belongsTo | `usuario()` | `Usuario` | `id_usuario` |
| belongsTo | `estado()` | `EstadoPedido` | `id_estado_pedido` |
| belongsTo | `contacto()` | `ContactoFarmacia` | `id_contacto` |
| hasMany | `detalles()` | `DetallePedido` | `id_pedido` |
| hasMany | `historiales()` | `HistorialEstadoPedido` | `id_pedido` |
| hasOne | `despacho()` | `Despacho` | `id_pedido` |

---

### DetallePedido

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_detalle_pedido` | PK | Identificador único |
| `id_pedido` | FK→`pedidos` | Pedido asociado |
| `id_producto` | FK→`productos` | Producto |
| `cantidad` | decimal(10,2) | Cantidad (> 0) |
| `precio_unitario` | decimal(10,2) | Precio unitario (>= 0) |
| `subtotal` | decimal(10,2) | Subtotal |

**Tabla:** `detalle_pedido` | **PK:** `id_detalle_pedido` | **Timestamps:** No

**Casts:** `cantidad:decimal:2`, `precio_unitario:decimal:2`, `subtotal:decimal:2`

**CHECK:** `cantidad > 0`, `precio_unitario >= 0`

**Unique compuesto:** `(id_pedido, id_producto)`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `pedido()` | `Pedido` | `id_pedido` |
| belongsTo | `producto()` | `Producto` | `id_producto` |

---

### EstadoPedido

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_estado_pedido` | PK (tinyInteger) | Identificador único |
| `nombre_estado` | string(50) | Nombre del estado (**unique**) |

**Tabla:** `estados_pedido` | **PK:** `id_estado_pedido` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `pedidos()` | `Pedido` | `id_estado_pedido` |
| hasMany | `historiales()` | `HistorialEstadoPedido` | `id_estado_pedido` |

---

### HistorialEstadoPedido

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_historial` | PK | Identificador único |
| `id_pedido` | FK→`pedidos` | Pedido asociado |
| `id_estado_pedido` | FK→`estados_pedido` | Estado asignado |
| `fecha_inicio` | datetime | Fecha de inicio del estado |
| `fecha_fin` | datetime | Fecha de fin del estado (nullable) |

**Tabla:** `historial_estado_pedido` | **PK:** `id_historial` | **Timestamps:** No

**Casts:** `fecha_inicio:datetime`, `fecha_fin:datetime`

**Unique compuesto:** `(id_pedido, fecha_inicio)`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `pedido()` | `Pedido` | `id_pedido` |
| belongsTo | `estado()` | `EstadoPedido` | `id_estado_pedido` |

---

## 6. Despacho

### Despacho

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_despacho` | PK | Identificador único |
| `id_pedido` | FK→`pedidos` | Pedido asociado (**unique**) |
| `id_parada` | FK→`ruta_paradas` | Parada de ruta |
| `id_control_ruta` | FK→`control_rutas` | Control de ruta |
| `fecha_hora_despacho` | timestamp | Fecha y hora del despacho (default: now) |
| `id_estado_despacho` | FK→`estados_despacho` | Estado del despacho |

**Tabla:** `despachos` | **PK:** `id_despacho` | **Timestamps:** No | **Traits:** HasFactory

**Casts:** `fecha_hora_despacho:datetime`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `pedido()` | `Pedido` | `id_pedido` |
| belongsTo | `parada()` | `RutaParada` | `id_parada` |
| belongsTo | `controlRuta()` | `ControlRuta` | `id_control_ruta` |
| belongsTo | `estado()` | `EstadoDespacho` | `id_estado_despacho` |
| hasMany | `historiales()` | `HistorialEstadoDespacho` | `id_despacho` |
| hasMany | `incidencias()` | `Incidencia` | `id_despacho` |
| hasMany | `evidencias()` | `EvidenciaEntrega` | `id_despacho` |

---

### EstadoDespacho

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_estado_despacho` | PK (tinyInteger) | Identificador único |
| `nombre_estado` | string(50) | Nombre del estado (**unique**) |

**Tabla:** `estados_despacho` | **PK:** `id_estado_despacho` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `despachos()` | `Despacho` | `id_estado_despacho` |
| hasMany | `historiales()` | `HistorialEstadoDespacho` | `id_estado_despacho` |

---

### HistorialEstadoDespacho

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_historial` | PK | Identificador único |
| `id_despacho` | FK→`despachos` | Despacho asociado |
| `id_estado_despacho` | FK→`estados_despacho` | Estado asignado |
| `fecha_inicio` | datetime | Fecha de inicio del estado |
| `fecha_fin` | datetime | Fecha de fin del estado (nullable) |

**Tabla:** `historial_estado_despacho` | **PK:** `id_historial` | **Timestamps:** No

**Casts:** `fecha_inicio:datetime`, `fecha_fin:datetime`

**Unique compuesto:** `(id_despacho, fecha_inicio)`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `despacho()` | `Despacho` | `id_despacho` |
| belongsTo | `estado()` | `EstadoDespacho` | `id_estado_despacho` |

---

## 7. Logística

### Ruta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_ruta` | PK (smallIncrements) | Identificador único |
| `nombre_ruta` | string(100) | Nombre de la ruta (**unique**) |

**Tabla:** `rutas` | **PK:** `id_ruta` | **Timestamps:** No | **Traits:** HasFactory

**Relaciones:**
| Tipo | Método | Modelo destino | FK | Nota |
|------|--------|----------------|-----|------|
| hasMany | `paradas()` | `RutaParada` | `id_ruta` | Ordenado por `orden_parada ASC` |
| hasMany | `controles()` | `ControlRuta` | `id_ruta` | |

---

### RutaParada

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_parada` | PK | Identificador único |
| `id_ruta` | FK→`rutas` | Ruta asociada |
| `id_farmacia` | FK→`farmacias` | Farmacia de la parada |
| `orden_parada` | smallInteger | Orden de la parada en la ruta (>= 1) |
| `hora_estimada` | string (time) | Hora estimada de llegada (H:i:s) |

**Tabla:** `ruta_paradas` | **PK:** `id_parada` | **Timestamps:** No | **Traits:** HasFactory

**Casts:** `hora_estimada:string`

**Unique compuesto:** `(id_ruta, orden_parada)`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `ruta()` | `Ruta` | `id_ruta` |
| belongsTo | `farmacia()` | `Farmacia` | `id_farmacia` |
| hasMany | `despachos()` | `Despacho` | `id_parada` |

---

### ControlRuta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_control_ruta` | PK | Identificador único |
| `id_ruta` | FK→`rutas` | Ruta controlada |
| `fecha_ruta` | date | Fecha de la ruta |
| `hora_salida` | string (time) | Hora de salida (H:i:s) |
| `hora_llegada_real` | string (time) | Hora de llegada real (nullable) |
| `id_repartidor` | FK→`repartidores` | Repartidor asignado |
| `id_vehiculo` | FK→`vehiculos` | Vehículo asignado |

**Tabla:** `control_rutas` | **PK:** `id_control_ruta` | **Timestamps:** No | **Traits:** HasFactory

**Casts:** `fecha_ruta:date`, `hora_salida:string`, `hora_llegada_real:string`

**Unique compuesto:** `(id_ruta, fecha_ruta)`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `ruta()` | `Ruta` | `id_ruta` |
| belongsTo | `repartidor()` | `Repartidor` | `id_repartidor` |
| belongsTo | `vehiculo()` | `Vehiculo` | `id_vehiculo` |
| hasMany | `despachos()` | `Despacho` | `id_control_ruta` |

---

## 8. Evidencia

### EvidenciaEntrega

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_evidencia` | PK | Identificador único |
| `id_despacho` | FK→`despachos` | Despacho asociado |
| `id_tipo_evidencia` | FK→`tipos_evidencia` | Tipo de evidencia |
| `archivo` | string | Ruta del archivo de evidencia |
| `fecha_registro` | datetime | Fecha de registro |

**Tabla:** `evidencias_entrega` | **PK:** `id_evidencia` | **Timestamps:** No

**Casts:** `fecha_registro:datetime`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `despacho()` | `Despacho` | `id_despacho` |
| belongsTo | `tipoEvidencia()` | `TipoEvidencia` | `id_tipo_evidencia` |

---

### Incidencia

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_incidencia` | PK | Identificador único |
| `id_despacho` | FK→`despachos` | Despacho asociado |
| `id_tipo_incidencia` | FK→`tipos_incidencia` | Tipo de incidencia |
| `descripcion` | text | Descripción de la incidencia |
| `fecha_incidencia` | datetime | Fecha de la incidencia |

**Tabla:** `incidencias` | **PK:** `id_incidencia` | **Timestamps:** No

**Casts:** `fecha_incidencia:datetime`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `despacho()` | `Despacho` | `id_despacho` |
| belongsTo | `tipoIncidencia()` | `TipoIncidencia` | `id_tipo_incidencia` |

---

### TipoEvidencia

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_tipo_evidencia` | PK (tinyInteger) | Identificador único |
| `nombre_tipo` | string(100) | Nombre del tipo de evidencia (**unique**) |

**Tabla:** `tipos_evidencia` | **PK:** `id_tipo_evidencia` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `evidencias()` | `EvidenciaEntrega` | `id_tipo_evidencia` |

---

### TipoIncidencia

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_tipo_incidencia` | PK (tinyInteger) | Identificador único |
| `nombre_tipo` | string(100) | Nombre del tipo de incidencia (**unique**) |

**Tabla:** `tipos_incidencia` | **PK:** `id_tipo_incidencia` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `incidencias()` | `Incidencia` | `id_tipo_incidencia` |

---

## 9. Medicamento

### Categoria

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_categoria` | PK (tinyIncrements) | Identificador único |
| `nombre_categoria` | string(100) | Nombre de la categoría (**unique**) |

**Tabla:** `categorias` | **PK:** `id_categoria` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `productos()` | `Producto` | `id_categoria` |

---

### Laboratorio

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_laboratorio` | PK (tinyIncrements) | Identificador único |
| `nombre_laboratorio` | string(150) | Nombre del laboratorio (**unique**) |
| `telefono` | string(20) | Teléfono (nullable) |
| `direccion` | text | Dirección (nullable) |

**Tabla:** `laboratorios` | **PK:** `id_laboratorio` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `productos()` | `Producto` | `id_laboratorio` |

---

### UnidadMedida

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_unidad_medida` | PK (tinyIncrements) | Identificador único |
| `nombre_unidad` | string(50) | Nombre de la unidad (**unique**) |

**Tabla:** `unidades_medida` | **PK:** `id_unidad_medida` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `productos()` | `Producto` | `id_unidad_medida` |

---

### Presentacion

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_presentacion` | PK (tinyIncrements) | Identificador único |
| `nombre_presentacion` | string(100) | Nombre de la presentación (**unique**) |

**Tabla:** `presentaciones` | **PK:** `id_presentacion` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `productos()` | `Producto` | `id_presentacion` |

---

### Producto (Medicamento)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_producto` | PK | Identificador único |
| `codigo_producto` | string(50) | Código del producto (**unique**) |
| `nombre_producto` | string(200) | Nombre del producto |
| `descripcion` | text | Descripción (nullable) |
| `id_categoria` | FK→`categorias` | Categoría del producto |
| `id_laboratorio` | FK→`laboratorios` | Laboratorio fabricante |
| `id_presentacion` | FK→`presentaciones` | Presentación del producto |
| `id_unidad_medida` | FK→`unidades_medida` | Unidad de medida |
| `concentracion` | string(100) | Concentración (nullable) |
| `precio_unitario` | decimal(10,2) | Precio unitario (>= 0) |
| `requiere_receta` | boolean | Requiere receta médica |
| `activo` | boolean | Producto activo/inactivo |

**Tabla:** `productos` | **PK:** `id_producto` | **Timestamps:** Sí | **Traits:** HasFactory

**Casts:** `precio_unitario:decimal:2`, `requiere_receta:boolean`, `activo:boolean`

**CHECK:** `precio_unitario >= 0`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `categoria()` | `Categoria` | `id_categoria` |
| belongsTo | `laboratorio()` | `Laboratorio` | `id_laboratorio` |
| belongsTo | `presentacion()` | `Presentacion` | `id_presentacion` |
| belongsTo | `unidadMedida()` | `UnidadMedida` | `id_unidad_medida` |
| hasMany | `lotes()` | `Lote` | `id_producto` |

---

### Lote

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_lote` | PK | Identificador único |
| `id_producto` | FK→`productos` | Producto asociado |
| `codigo_lote` | string(50) | Código del lote |
| `fecha_vencimiento` | date | Fecha de vencimiento |
| `costo_unitario` | decimal(10,2) | Costo unitario |

**Tabla:** `lotes` | **PK:** `id_lote` | **Timestamps:** No

**Casts:** `fecha_vencimiento:date`, `costo_unitario:decimal:2`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `producto()` | `Producto` | `id_producto` |

---

## 10. Inventario

### TipoMovimiento

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_tipo_movimiento` | PK (tinyIncrements) | Identificador único |
| `nombre_tipo` | string(50) | Nombre del tipo (**unique**) |

**Tabla:** `tipos_movimiento` | **PK:** `id_tipo_movimiento` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `movimientos()` | `MovimientoInventario` | `id_tipo_movimiento` |

---

### Inventario

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_inventario` | PK | Identificador único |
| `id_producto` | FK→`productos` | Producto |
| `id_lote` | FK→`lotes` | Lote (nullable) |
| `id_ubicacion` | FK→`ubicaciones_almacen` | Ubicación en almacén (nullable) |
| `stock_actual` | decimal(10,2) | Stock actual (>= 0) |
| `stock_minimo` | decimal(10,2) | Stock mínimo (>= 0) |
| `precio_venta` | decimal(10,2) | Precio de venta (nullable, >= 0) |
| `fecha_actualizacion` | datetime | Fecha de última actualización (nullable) |

**Tabla:** `inventario` | **PK:** `id_inventario` | **Timestamps:** Sí (created_at, updated_at)

**Casts:** `stock_actual:decimal:2`, `stock_minimo:decimal:2`, `precio_venta:decimal:2`, `fecha_actualizacion:datetime`

**CHECK:** `stock_actual >= 0`, `stock_minimo >= 0`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `producto()` | `Producto` | `id_producto` |
| belongsTo | `lote()` | `Lote` | `id_lote` |
| belongsTo | `ubicacion()` | `UbicacionAlmacen` | `id_ubicacion` |
| hasMany | `movimientos()` | `MovimientoInventario` | `id_inventario` |

---

### MovimientoInventario

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_movimiento` | PK | Identificador único |
| `id_inventario` | FK→`inventario` | Registro de inventario |
| `id_tipo_movimiento` | FK→`tipos_movimiento` | Tipo de movimiento |
| `id_usuario` | FK→`usuarios` | Usuario que registró |
| `cantidad` | decimal(10,2) | Cantidad movida |
| `fecha_movimiento` | timestamp | Fecha del movimiento (default: now) |
| `observaciones` | text | Observaciones (nullable) |

**Tabla:** `movimientos_inventario` | **PK:** `id_movimiento` | **Timestamps:** No

**Casts:** `cantidad:decimal:2`, `fecha_movimiento:datetime`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `inventario()` | `Inventario` | `id_inventario` |
| belongsTo | `tipoMovimiento()` | `TipoMovimiento` | `id_tipo_movimiento` |
| belongsTo | `usuario()` | `Usuario` | `id_usuario` |

---

### Almacen

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_almacen` | PK | Identificador único |
| `id_farmacia` | FK→`farmacias` | Farmacia asociada |
| `nombre` | string(100) | Nombre del almacén |

**Tabla:** `almacenes` | **PK:** `id_almacen` | **Timestamps:** No

**Unique compuesto:** `(id_farmacia, nombre)`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `farmacia()` | `Farmacia` | `id_farmacia` |
| hasMany | `ubicaciones()` | `UbicacionAlmacen` | `id_almacen` |

---

### UbicacionAlmacen

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_ubicacion` | PK | Identificador único |
| `id_almacen` | FK→`almacenes` | Almacén asociado |
| `pasillo` | string(20) | Pasillo de la ubicación |
| `estante` | string(20) | Estante de la ubicación |

**Tabla:** `ubicaciones_almacen` | **PK:** `id_ubicacion` | **Timestamps:** No

**Unique compuesto:** `(id_almacen, pasillo, estante)`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `almacen()` | `Almacen` | `id_almacen` |
| hasMany | `inventarios()` | `Inventario` | `id_ubicacion` |

---

## 11. Compra

### Proveedor

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_proveedor` | PK | Identificador único |
| `nombre_proveedor` | string(150) | Nombre del proveedor |
| `nit` | string(50) | NIT (nullable) |
| `telefono` | string(20) | Teléfono (nullable) |
| `email` | string(180) | Correo electrónico (nullable) |
| `direccion` | text | Dirección (nullable) |

**Tabla:** `proveedores` | **PK:** `id_proveedor` | **Timestamps:** No | **Traits:** HasFactory

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `contactos()` | `ContactoProveedor` | `id_proveedor` |
| hasMany | `ordenes()` | `OrdenCompra` | `id_proveedor` |

---

### ContactoProveedor

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_contacto_proveedor` | PK | Identificador único |
| `id_proveedor` | FK→`proveedores` | Proveedor asociado |
| `nombre_contacto` | string(150) | Nombre del contacto |
| `telefono` | string(20) | Teléfono |
| `email` | string(180) | Correo electrónico (nullable) |
| `cargo` | string(100) | Cargo (nullable) |

**Tabla:** `contactos_proveedor` | **PK:** `id_contacto_proveedor` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `proveedor()` | `Proveedor` | `id_proveedor` |

---

### EstadoOrdenCompra

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_estado_orden_compra` | PK (tinyIncrements) | Identificador único |
| `nombre_estado` | string(50) | Nombre del estado (**unique**) |

**Tabla:** `estados_orden_compra` | **PK:** `id_estado_orden_compra` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `ordenes()` | `OrdenCompra` | `id_estado_orden_compra` |

---

### OrdenCompra

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_orden_compra` | PK | Identificador único |
| `codigo_orden` | string(50) | Código de orden (**unique**) |
| `id_proveedor` | FK→`proveedores` | Proveedor |
| `id_usuario` | FK→`usuarios` | Usuario que registró |
| `id_estado_orden_compra` | FK→`estados_orden_compra` | Estado |
| `fecha_orden` | timestamp | Fecha de orden (default: now) |
| `fecha_estimada_recibido` | date | Fecha estimada de recibido (nullable) |
| `observaciones` | text | Observaciones (nullable) |

**Tabla:** `ordenes_compra` | **PK:** `id_orden_compra` | **Timestamps:** No | **Traits:** HasFactory

**Casts:** `fecha_orden:datetime`, `fecha_estimada_recibido:date`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `proveedor()` | `Proveedor` | `id_proveedor` |
| belongsTo | `usuario()` | `Usuario` | `id_usuario` |
| belongsTo | `estado()` | `EstadoOrdenCompra` | `id_estado_orden_compra` |
| hasMany | `detalles()` | `DetalleCompra` | `id_orden_compra` |

---

### DetalleCompra

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_detalle_compra` | PK | Identificador único |
| `id_orden_compra` | FK→`ordenes_compra` | Orden de compra |
| `id_producto` | FK→`productos` | Producto |
| `cantidad` | decimal(10,2) | Cantidad (> 0) |
| `precio_unitario` | decimal(10,2) | Precio unitario (>= 0) |
| `subtotal` | decimal(10,2) | Subtotal |

**Tabla:** `detalles_compra` | **PK:** `id_detalle_compra` | **Timestamps:** No

**Casts:** `cantidad:decimal:2`, `precio_unitario:decimal:2`, `subtotal:decimal:2`

**CHECK:** `cantidad > 0`, `precio_unitario >= 0`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `ordenCompra()` | `OrdenCompra` | `id_orden_compra` |
| belongsTo | `producto()` | `Producto` | `id_producto` |

---

## 12. Devolucion

### TipoDevolucion

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_tipo_devolucion` | PK (tinyIncrements) | Identificador único |
| `nombre_tipo` | string(100) | Nombre del tipo (**unique**) |

**Tabla:** `tipos_devolucion` | **PK:** `id_tipo_devolucion` | **Timestamps:** No

---

### EstadoDevolucion

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_estado_devolucion` | PK (tinyIncrements) | Identificador único |
| `nombre_estado` | string(50) | Nombre del estado (**unique**) |

**Tabla:** `estados_devolucion` | **PK:** `id_estado_devolucion` | **Timestamps:** No

---

### Devolucion

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_devolucion` | PK | Identificador único |
| `id_pedido` | FK→`pedidos` | Pedido asociado |
| `id_usuario` | FK→`usuarios` | Usuario que registró |
| `id_tipo_devolucion` | FK→`tipos_devolucion` | Tipo de devolución |
| `id_estado_devolucion` | FK→`estados_devolucion` | Estado de la devolución |
| `motivo` | text | Motivo (nullable) |
| `fecha_devolucion` | timestamp | Fecha de devolución (default: now) |

**Tabla:** `devoluciones` | **PK:** `id_devolucion` | **Timestamps:** No

**Casts:** `fecha_devolucion:datetime`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `pedido()` | `Pedido` | `id_pedido` |
| belongsTo | `usuario()` | `Usuario` | `id_usuario` |
| belongsTo | `tipoDevolucion()` | `TipoDevolucion` | `id_tipo_devolucion` |
| belongsTo | `estado()` | `EstadoDevolucion` | `id_estado_devolucion` |
| hasMany | `detalles()` | `DetalleDevolucion` | `id_devolucion` |
| hasMany | `historiales()` | `HistorialEstadoDevolucion` | `id_devolucion` |

---

### DetalleDevolucion

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_detalle_devolucion` | PK | Identificador único |
| `id_devolucion` | FK→`devoluciones` | Devolución asociada |
| `id_producto` | FK→`productos` | Producto |
| `cantidad` | decimal(10,2) | Cantidad (> 0) |
| `precio_unitario` | decimal(10,2) | Precio unitario (>= 0) |
| `subtotal` | decimal(10,2) | Subtotal |
| `motivo_detalle` | text | Motivo del detalle (nullable) |

**Tabla:** `detalles_devolucion` | **PK:** `id_detalle_devolucion` | **Timestamps:** No

**Casts:** `cantidad:decimal:2`, `precio_unitario:decimal:2`, `subtotal:decimal:2`

**CHECK:** `cantidad > 0`, `precio_unitario >= 0`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `devolucion()` | `Devolucion` | `id_devolucion` |
| belongsTo | `producto()` | `Producto` | `id_producto` |

---

### HistorialEstadoDevolucion

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_historial` | PK | Identificador único |
| `id_devolucion` | FK→`devoluciones` | Devolución asociada |
| `id_estado_devolucion` | FK→`estados_devolucion` | Estado asignado |
| `fecha_inicio` | timestamp | Inicio del estado (default: now) |
| `fecha_fin` | timestamp | Fin del estado (nullable) |

**Tabla:** `historial_estado_devolucion` | **PK:** `id_historial` | **Timestamps:** No

**Casts:** `fecha_inicio:datetime`, `fecha_fin:datetime`

**Unique compuesto:** `(id_devolucion, fecha_inicio)`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `devolucion()` | `Devolucion` | `id_devolucion` |
| belongsTo | `estado()` | `EstadoDevolucion` | `id_estado_devolucion` |

---

## 13. Promocion

### TipoPromocion

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_tipo_promocion` | PK (tinyIncrements) | Identificador único |
| `nombre_tipo` | string(100) | Nombre del tipo (**unique**) |

**Tabla:** `tipos_promocion` | **PK:** `id_tipo_promocion` | **Timestamps:** No

---

### Promocion

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_promocion` | PK | Identificador único |
| `nombre_promocion` | string(200) | Nombre de la promoción |
| `descripcion` | text | Descripción (nullable) |
| `id_tipo_promocion` | FK→`tipos_promocion` | Tipo de promoción |
| `descuento` | decimal(10,2) | Valor del descuento (>= 0) |
| `es_porcentual` | boolean | true=porcentaje, false=monto fijo |
| `fecha_inicio` | date | Fecha de inicio |
| `fecha_fin` | date | Fecha de fin (nullable) |
| `activo` | boolean | Promoción activa/inactiva |
| `id_usuario` | FK→`usuarios` | Usuario que creó |

**Tabla:** `promociones` | **PK:** `id_promocion` | **Timestamps:** No

**Casts:** `descuento:decimal:2`, `es_porcentual:boolean`, `activo:boolean`, `fecha_inicio:date`, `fecha_fin:date`

**CHECK:** `descuento >= 0`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `tipoPromocion()` | `TipoPromocion` | `id_tipo_promocion` |
| belongsTo | `usuario()` | `Usuario` | `id_usuario` |
| hasMany | `productos()` | `ProductoPromocion` | `id_promocion` |

---

### ProductoPromocion

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_producto_promocion` | PK | Identificador único |
| `id_promocion` | FK→`promociones` | Promoción asociada |
| `id_producto` | FK→`productos` | Producto incluido |
| `cantidad_minima` | decimal(10,2) | Cantidad mínima |

**Tabla:** `productos_promocion` | **PK:** `id_producto_promocion` | **Timestamps:** No

**Casts:** `cantidad_minima:decimal:2`

**Unique compuesto:** `(id_promocion, id_producto)`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `promocion()` | `Promocion` | `id_promocion` |
| belongsTo | `producto()` | `Producto` | `id_producto` |

---

## 14. Venta

### EstadoVenta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_estado_venta` | PK (tinyIncrements) | Identificador único |
| `nombre_estado` | string(50) | Nombre del estado (**unique**) |

**Tabla:** `estados_venta` | **PK:** `id_estado_venta` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `ventas()` | `Venta` | `id_estado_venta` |

---

### Venta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_venta` | PK | Identificador único |
| `id_pedido` | FK→`pedidos` | Pedido asociado |
| `id_usuario` | FK→`usuarios` | Usuario que registró |
| `id_estado_venta` | FK→`estados_venta` | Estado de la venta |
| `total` | decimal(12,2) | Total de la venta |
| `fecha_venta` | timestamp | Fecha de la venta (default: now) |

**Tabla:** `ventas` | **PK:** `id_venta` | **Timestamps:** No

**Casts:** `total:decimal:2`, `fecha_venta:datetime`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `pedido()` | `Pedido` | `id_pedido` |
| belongsTo | `usuario()` | `Usuario` | `id_usuario` |
| belongsTo | `estado()` | `EstadoVenta` | `id_estado_venta` |
| hasMany | `detalles()` | `DetalleVenta` | `id_venta` |
| hasMany | `pagos()` | `Pago` | `id_venta` |

---

### DetalleVenta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_detalle_venta` | PK | Identificador único |
| `id_venta` | FK→`ventas` | Venta asociada |
| `id_lote` | FK→`lotes` | Lote del producto |
| `cantidad` | decimal(10,2) | Cantidad vendida |
| `precio_unitario` | decimal(10,2) | Precio unitario |
| `subtotal` | decimal(10,2) | Subtotal |

**Tabla:** `detalle_venta` | **PK:** `id_detalle_venta` | **Timestamps:** No

**Casts:** `cantidad:decimal:2`, `precio_unitario:decimal:2`, `subtotal:decimal:2`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `venta()` | `Venta` | `id_venta` |
| belongsTo | `lote()` | `Lote` | `id_lote` |

---

### MetodoPago

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_metodo_pago` | PK (tinyIncrements) | Identificador único |
| `nombre_metodo` | string(50) | Nombre del método (**unique**) |

**Tabla:** `metodos_pago` | **PK:** `id_metodo_pago` | **Timestamps:** No

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| hasMany | `pagos()` | `Pago` | `id_metodo_pago` |

---

### Pago

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_pago` | PK | Identificador único |
| `id_venta` | FK→`ventas` | Venta asociada |
| `id_metodo_pago` | FK→`metodos_pago` | Método de pago |
| `monto` | decimal(10,2) | Monto del pago |
| `fecha_pago` | timestamp | Fecha del pago (default: now) |
| `referencia` | string(100) | Referencia del pago (nullable) |

**Tabla:** `pagos` | **PK:** `id_pago` | **Timestamps:** No

**Casts:** `monto:decimal:2`, `fecha_pago:datetime`

**Relaciones:**
| Tipo | Método | Modelo destino | FK |
|------|--------|----------------|----|
| belongsTo | `venta()` | `Venta` | `id_venta` |
| belongsTo | `metodoPago()` | `MetodoPago` | `id_metodo_pago` |

---

## 15. Leyenda

| Símbolo | Significado |
|---------|-------------|
| PK | Primary Key (clave primaria) |
| FK→`tabla` | Foreign Key que referencia a la tabla indicada |
| (**unique**) | Restricción de unicidad en la BD |
| (nullable) | Permite valores nulos |
| **Traits** | Traits de Laravel usados por el modelo |
| **Hidden** | Atributos ocultos en respuestas JSON |
| **Casts** | Conversión automática de tipos al acceder al atributo |
| Timestamps: No | `$timestamps = false` (sin created_at / updated_at) |
| `hasOne` | Relación 1:1 |
| `hasMany` | Relación 1:N |
| `belongsTo` | Relación N:1 (inversa) |
| `belongsToMany` | Relación N:M |
| tinyInteger / smallIncrements | Tipo de columna en BD |

---

## Diagrama de Relaciones por Módulo

### Seguridad
```
EstadoUsuario (1) --< (N) Usuario (N) >-- (N) Rol (N) >-- (N) Permiso
                                  |                         |         |
                                  |                         1         1
                                  |                     Modulo    Accion
                                  |
                   SesionUsuario | Auditoria
```

### Farmacia
```
EstadoFarmacia (1) --< (N) Farmacia (N) >-- (N) ContactoFarmacia (N) >-- (1) Cargo
                                       |
                                 Almacen
```

### Repartidor
```
ExtensionCI (1) --< (N) Repartidor (N) >-- (1) EstadoRepartidor
Licencia (1) --< (N)             |       (N) >-- (1) Usuario
                                 |
                     HistorialEstadoRepartidor
```

### Vehículo
```
Marca (1) --< (N) Modelo (N) >-- (1) Vehiculo (N) >-- (1) EstadoVehiculo
Capacidad (1) --< (N)           |                       |
                                |    HistorialEstadoVehiculo
```

### Pedido
```
EstadoPedido (1) --< (N) Pedido (N) >-- (1) Farmacia
                                 |     (N) >-- (1) Usuario
                                 |     (N) >-- (1) ContactoFarmacia
                                 |
                     HistorialEstadoPedido
                      DetallePedido (N) >-- (1) Producto
```

### Despacho
```
EstadoDespacho (1) --< (N) Despacho (N) >-- (1) Pedido
                                |        (N) >-- (1) RutaParada
                                |        (N) >-- (1) ControlRuta
                                |
                    HistorialEstadoDespacho
                     Incidencia
                     EvidenciaEntrega
```

### Logística
```
Ruta (1) --< (N) RutaParada (N) >-- (1) Farmacia
  |                      |
  |               Despacho
  |
  +--< ControlRuta (N) >-- (1) Repartidor
                  |         >-- (1) Vehiculo
                  |
            Despacho
```

### Evidencia
```
TipoEvidencia (1) --< (N) EvidenciaEntrega (N) >-- (1) Despacho
TipoIncidencia (1) --< (N) Incidencia (N) >-- (1) Despacho
```

### Medicamento
```
Categoria (1) --< (N) Producto (N) >-- (1) Laboratorio
UnidadMedida (1) --< (N)    |    >-- (1) Presentacion
                            |
                         Lote
```

### Inventario
```
TipoMovimiento (1) --< (N) MovimientoInventario (N) >-- (1) Inventario (N) >-- (1) Producto
                                                                    |     (N) >-- (1) Lote
                                                                    |     (N) >-- (1) UbicacionAlmacen (N) >-- (1) Almacen (N) >-- (1) Farmacia
```

### Compra
```
Proveedor (1) --< (N) ContactoProveedor
         (1) --< (N) OrdenCompra (N) >-- (1) EstadoOrdenCompra
                            |     >-- (1) Usuario
                            |
                   DetalleCompra (N) >-- (1) Producto
```

### Devolucion
```
TipoDevolucion (1) --< (N) Devolucion (N) >-- (1) Pedido
EstadoDevolucion (1) --< (N)   |    >-- (1) Usuario
                               |
                    DetalleDevolucion (N) >-- (1) Producto
                    HistorialEstadoDevolucion
```

### Promocion
```
TipoPromocion (1) --< (N) Promocion (N) >-- (1) Usuario
                                |
                     ProductoPromocion (N) >-- (1) Producto
```

### Venta
```
EstadoVenta (1) --< (N) Venta (N) >-- (1) Pedido
                           |      >-- (1) Usuario
                           |
                DetalleVenta (N) >-- (1) Lote
                   Pago (N) >-- (1) MetodoPago
```

### Almacen
```
Farmacia (1) --< (N) Almacen (1) --< (N) UbicacionAlmacen (1) --< (N) Inventario
```
