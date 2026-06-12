# Documentación de Modelos - Pw3c Distribuidora

> **Framework:** Laravel 9 (Eloquent ORM)
> **Total de modelos:** 36

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

---

## 1. Seguridad

### Usuario

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_usuario` | PK | Identificador único |
| `nombre` | string | Nombre del usuario |
| `apellido` | string | Apellido del usuario |
| `email` | string | Correo electrónico (unique) |
| `password_hash` | string | Contraseña hasheada |
| `telefono` | string | Teléfono |
| `id_estado_usuario` | FK | Relación con EstadoUsuario |
| `fecha_creacion` | datetime | Fecha de creación |
| `fecha_bloqueo` | datetime | Fecha de bloqueo (nullable) |
| `ultimo_acceso` | datetime | Último acceso (nullable) |

**Tabla:** `usuarios` | **PK:** `id_usuario` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsTo | `EstadoUsuario` | `id_estado_usuario` |
| belongsToMany | `Rol` | pivot: `usuario_roles` |
| hasMany | `Pedido` | `id_usuario` |
| hasOne | `Repartidor` | `id_usuario` |
| hasMany | `SesionUsuario` | `id_usuario` |
| hasMany | `Auditoria` | `id_usuario` |

---

### EstadoUsuario

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_estado_usuario` | PK | Identificador único |
| `nombre_estado` | string | Nombre del estado (ej: Activo, Bloqueado) |

**Tabla:** `estados_usuario` | **PK:** `id_estado_usuario` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| hasMany | `Usuario` | `id_estado_usuario` |

---

### Rol

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_rol` | PK | Identificador único |
| `nombre` | string | Nombre del rol (ej: Admin, Repartidor) |

**Tabla:** `roles` | **PK:** `id_rol` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsToMany | `Usuario` | pivot: `usuario_roles` |
| belongsToMany | `Permiso` | pivot: `rol_permiso` |

---

### Permiso

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_permiso` | PK | Identificador único |
| `id_modulo` | FK | Relación con Modulo |
| `id_accion` | FK | Relación con Accion |

**Tabla:** `permisos` | **PK:** `id_permiso` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsTo | `Modulo` | `id_modulo` |
| belongsTo | `Accion` | `id_accion` |
| belongsToMany | `Rol` | pivot: `rol_permiso` |

---

### Modulo

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_modulo` | PK | Identificador único |
| `nombre` | string | Nombre del módulo (ej: Usuarios, Pedidos) |

**Tabla:** `modulos` | **PK:** `id_modulo` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| hasMany | `Permiso` | `id_modulo` |

---

### Accion

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_accion` | PK | Identificador único |
| `nombre` | string | Nombre de la acción (ej: Crear, Leer, Actualizar, Eliminar) |

**Tabla:** `acciones` | **PK:** `id_accion` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| hasMany | `Permiso` | `id_accion` |

---

### TablaSistema

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_tabla` | PK | Identificador único |
| `nombre` | string | Nombre de la tabla del sistema auditada |

**Tabla:** `tablas_sistema` | **PK:** `id_tabla` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| hasMany | `Auditoria` | `id_tabla` |

---

### Auditoria

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_auditoria` | PK | Identificador único |
| `id_usuario` | FK | Relación con Usuario |
| `id_accion` | FK | Relación con Accion |
| `id_tabla` | FK | Relación con TablaSistema |
| `registro_id` | int | ID del registro afectado |
| `fecha_hora` | datetime | Fecha y hora de la auditoría |

**Tabla:** `auditorias` | **PK:** `id_auditoria` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsTo | `Usuario` | `id_usuario` |
| belongsTo | `Accion` | `id_accion` |
| belongsTo | `TablaSistema` | `id_tabla` |

---

### SesionUsuario

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_sesion` | PK | Identificador único |
| `id_usuario` | FK | Relación con Usuario |
| `fecha_inicio` | datetime | Inicio de sesión |
| `fecha_fin` | datetime | Fin de sesión (nullable) |

**Tabla:** `sesiones_usuario` | **PK:** `id_sesion` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsTo | `Usuario` | `id_usuario` |

---

## 2. Farmacia

### Farmacia

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_farmacia` | PK | Identificador único |
| `nombre` | string | Nombre de la farmacia |
| `direccion` | string | Dirección |
| `telefono` | string | Teléfono |
| `email` | string | Correo electrónico |
| `latitud` | decimal | Latitud de ubicación |
| `longitud` | decimal | Longitud de ubicación |

**Tabla:** `farmacias` | **PK:** `id_farmacia` | **Timestamps:** Sí (por defecto)

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| hasMany | `ContactoFarmacia` | `id_farmacia` |
| hasMany | `Pedido` | `id_farmacia` |
| hasMany | `RutaParada` | `id_farmacia` |

---

### ContactoFarmacia

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_contacto` | PK | Identificador único |
| `id_farmacia` | FK | Relación con Farmacia |
| `nombre_contacto` | string | Nombre del contacto |
| `id_cargo` | FK | Relación con Cargo |
| `telefono` | string | Teléfono del contacto |
| `email` | string | Correo electrónico del contacto |

**Tabla:** `contactos_farmacia` | **PK:** `id_contacto` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsTo | `Farmacia` | `id_farmacia` |
| belongsTo | `Cargo` | `id_cargo` |

---

### Cargo

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_cargo` | PK | Identificador único |
| `nombre_cargo` | string | Nombre del cargo (ej: Gerente, Encargado) |

**Tabla:** `cargos` | **PK:** `id_cargo` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| hasMany | `ContactoFarmacia` | `id_cargo` |

---

## 3. Repartidor

### Repartidor

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_repartidor` | PK | Identificador único |
| `id_usuario` | FK | Relación con Usuario |
| `ci` | string | Cédula de identidad |
| `id_extension_ci` | FK | Relación con ExtensionCI |
| `id_licencia` | FK | Relación con Licencia |
| `id_estado_repartidor` | FK | Relación con EstadoRepartidor |

**Tabla:** `repartidores` | **PK:** `id_repartidor` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsTo | `Usuario` | `id_usuario` |
| belongsTo | `ExtensionCI` | `id_extension_ci` |
| belongsTo | `Licencia` | `id_licencia` |
| belongsTo | `EstadoRepartidor` | `id_estado_repartidor` |
| hasMany | `HistorialEstadoRepartidor` | `id_repartidor` |
| hasMany | `ControlRuta` | `id_repartidor` |

---

### EstadoRepartidor

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_estado_repartidor` | PK | Identificador único |
| `nombre_estado` | string | Nombre del estado (ej: Disponible, Ocupado, Inactivo) |

**Tabla:** `estados_repartidor` | **PK:** `id_estado_repartidor` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| hasMany | `Repartidor` | `id_estado_repartidor` |
| hasMany | `HistorialEstadoRepartidor` | `id_estado_repartidor` |

---

### HistorialEstadoRepartidor

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_historial` | PK | Identificador único |
| `id_repartidor` | FK | Relación con Repartidor |
| `id_estado_repartidor` | FK | Relación con EstadoRepartidor |
| `fecha_inicio` | datetime | Fecha de inicio del estado |
| `fecha_fin` | datetime | Fecha de fin del estado (nullable) |

**Tabla:** `historial_estado_repartidor` | **PK:** `id_historial` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsTo | `Repartidor` | `id_repartidor` |
| belongsTo | `EstadoRepartidor` | `id_estado_repartidor` |

---

### Licencia

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_licencia` | PK | Identificador único |
| `categoria` | string | Categoría de licencia (ej: A, B, C) |

**Tabla:** `licencias` | **PK:** `id_licencia` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| hasMany | `Repartidor` | `id_licencia` |

---

### ExtensionCI

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_extension_ci` | PK | Identificador único |
| `nombre_extension` | string | Nombre de extensión de CI (ej: LP, SC, CB) |

**Tabla:** `extensiones_ci` | **PK:** `id_extension_ci` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| hasMany | `Repartidor` | `id_extension_ci` |

---

## 4. Vehículo

### Vehiculo

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_vehiculo` | PK | Identificador único |
| `placa` | string | Placa del vehículo |
| `id_modelo` | FK | Relación con Modelo |
| `id_capacidad` | FK | Relación con Capacidad |
| `id_estado_vehiculo` | FK | Relación con EstadoVehiculo |

**Tabla:** `vehiculos` | **PK:** `id_vehiculo` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsTo | `Modelo` | `id_modelo` |
| belongsTo | `Capacidad` | `id_capacidad` |
| belongsTo | `EstadoVehiculo` | `id_estado_vehiculo` |
| hasMany | `HistorialEstadoVehiculo` | `id_vehiculo` |
| hasMany | `ControlRuta` | `id_vehiculo` |

---

### EstadoVehiculo

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_estado_vehiculo` | PK | Identificador único |
| `nombre_estado` | string | Nombre del estado (ej: Operativo, Mantenimiento) |

**Tabla:** `estados_vehiculo` | **PK:** `id_estado_vehiculo` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| hasMany | `Vehiculo` | `id_estado_vehiculo` |
| hasMany | `HistorialEstadoVehiculo` | `id_estado_vehiculo` |

---

### HistorialEstadoVehiculo

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_historial` | PK | Identificador único |
| `id_vehiculo` | FK | Relación con Vehiculo |
| `id_estado_vehiculo` | FK | Relación con EstadoVehiculo |
| `fecha_inicio` | datetime | Fecha de inicio del estado |
| `fecha_fin` | datetime | Fecha de fin del estado (nullable) |

**Tabla:** `historial_estado_vehiculo` | **PK:** `id_historial` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsTo | `Vehiculo` | `id_vehiculo` |
| belongsTo | `EstadoVehiculo` | `id_estado_vehiculo` |

---

### Marca

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_marca` | PK | Identificador único |
| `nombre_marca` | string | Nombre de la marca (ej: Toyota, Nissan) |

**Tabla:** `marcas` | **PK:** `id_marca` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| hasMany | `Modelo` | `id_marca` |

---

### Modelo

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_modelo` | PK | Identificador único |
| `id_marca` | FK | Relación con Marca |
| `nombre_modelo` | string | Nombre del modelo (ej: Hilux, Hiace) |

**Tabla:** `modelos` | **PK:** `id_modelo` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsTo | `Marca` | `id_marca` |
| hasMany | `Vehiculo` | `id_modelo` |

---

### Capacidad

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_capacidad` | PK | Identificador único |
| `capacidad_kg` | decimal(2) | Capacidad en kilogramos |

**Tabla:** `capacidades` | **PK:** `id_capacidad` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| hasMany | `Vehiculo` | `id_capacidad` |

---

## 5. Pedido

### Pedido

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_pedido` | PK | Identificador único |
| `id_farmacia` | FK | Relación con Farmacia |
| `id_usuario` | FK | Relación con Usuario |
| `id_estado_pedido` | FK | Relación con EstadoPedido |
| `fecha_pedido` | datetime | Fecha del pedido |
| `observaciones` | text | Observaciones (nullable) |

**Tabla:** `pedidos` | **PK:** `id_pedido` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsTo | `Farmacia` | `id_farmacia` |
| belongsTo | `Usuario` | `id_usuario` |
| belongsTo | `EstadoPedido` | `id_estado_pedido` |
| hasMany | `HistorialEstadoPedido` | `id_pedido` |
| hasOne | `Despacho` | `id_pedido` |

---

### EstadoPedido

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_estado_pedido` | PK | Identificador único |
| `nombre_estado` | string | Nombre del estado (ej: Pendiente, Aprobado, Despachado) |

**Tabla:** `estados_pedido` | **PK:** `id_estado_pedido` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| hasMany | `Pedido` | `id_estado_pedido` |
| hasMany | `HistorialEstadoPedido` | `id_estado_pedido` |

---

### HistorialEstadoPedido

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_historial` | PK | Identificador único |
| `id_pedido` | FK | Relación con Pedido |
| `id_estado_pedido` | FK | Relación con EstadoPedido |
| `fecha_inicio` | datetime | Fecha de inicio del estado |
| `fecha_fin` | datetime | Fecha de fin del estado (nullable) |

**Tabla:** `historial_estado_pedido` | **PK:** `id_historial` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsTo | `Pedido` | `id_pedido` |
| belongsTo | `EstadoPedido` | `id_estado_pedido` |

---

## 6. Despacho

### Despacho

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_despacho` | PK | Identificador único |
| `id_pedido` | FK | Relación con Pedido |
| `id_parada` | FK | Relación con RutaParada |
| `id_control_ruta` | FK | Relación con ControlRuta |
| `fecha_hora_despacho` | datetime | Fecha y hora del despacho |
| `id_estado_despacho` | FK | Relación con EstadoDespacho |

**Tabla:** `despachos` | **PK:** `id_despacho` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsTo | `Pedido` | `id_pedido` |
| belongsTo | `RutaParada` | `id_parada` |
| belongsTo | `ControlRuta` | `id_control_ruta` |
| belongsTo | `EstadoDespacho` | `id_estado_despacho` |
| hasMany | `HistorialEstadoDespacho` | `id_despacho` |
| hasMany | `Incidencia` | `id_despacho` |
| hasMany | `EvidenciaEntrega` | `id_despacho` |

---

### EstadoDespacho

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_estado_despacho` | PK | Identificador único |
| `nombre_estado` | string | Nombre del estado (ej: Pendiente, En ruta, Entregado) |

**Tabla:** `estados_despacho` | **PK:** `id_estado_despacho` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| hasMany | `Despacho` | `id_estado_despacho` |
| hasMany | `HistorialEstadoDespacho` | `id_estado_despacho` |

---

### HistorialEstadoDespacho

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_historial` | PK | Identificador único |
| `id_despacho` | FK | Relación con Despacho |
| `id_estado_despacho` | FK | Relación con EstadoDespacho |
| `fecha_inicio` | datetime | Fecha de inicio del estado |
| `fecha_fin` | datetime | Fecha de fin del estado (nullable) |

**Tabla:** `historial_estado_despacho` | **PK:** `id_historial` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsTo | `Despacho` | `id_despacho` |
| belongsTo | `EstadoDespacho` | `id_estado_despacho` |

---

## 7. Logística

### Ruta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_ruta` | PK | Identificador único |
| `nombre_ruta` | string | Nombre de la ruta |

**Tabla:** `rutas` | **PK:** `id_ruta` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| hasMany | `RutaParada` | `id_ruta` (ordenado por `orden_parada`) |
| hasMany | `ControlRuta` | `id_ruta` |

---

### RutaParada

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_parada` | PK | Identificador único |
| `id_ruta` | FK | Relación con Ruta |
| `id_farmacia` | FK | Relación con Farmacia |
| `orden_parada` | int | Orden de la parada en la ruta |
| `hora_estimada` | string | Hora estimada de llegada |

**Tabla:** `ruta_paradas` | **PK:** `id_parada` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsTo | `Ruta` | `id_ruta` |
| belongsTo | `Farmacia` | `id_farmacia` |
| hasMany | `Despacho` | `id_parada` |

---

### ControlRuta

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_control_ruta` | PK | Identificador único |
| `id_ruta` | FK | Relación con Ruta |
| `fecha_ruta` | date | Fecha de la ruta |
| `hora_salida` | string | Hora de salida |
| `hora_llegada_real` | string | Hora de llegada real (nullable) |
| `id_repartidor` | FK | Relación con Repartidor |
| `id_vehiculo` | FK | Relación con Vehiculo |

**Tabla:** `control_rutas` | **PK:** `id_control_ruta` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsTo | `Ruta` | `id_ruta` |
| belongsTo | `Repartidor` | `id_repartidor` |
| belongsTo | `Vehiculo` | `id_vehiculo` |
| hasMany | `Despacho` | `id_control_ruta` |

---

## 8. Evidencia

### EvidenciaEntrega

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_evidencia` | PK | Identificador único |
| `id_despacho` | FK | Relación con Despacho |
| `id_tipo_evidencia` | FK | Relación con TipoEvidencia |
| `archivo` | string | Ruta del archivo de evidencia |
| `fecha_registro` | datetime | Fecha de registro de la evidencia |

**Tabla:** `evidencias_entrega` | **PK:** `id_evidencia` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsTo | `Despacho` | `id_despacho` |
| belongsTo | `TipoEvidencia` | `id_tipo_evidencia` |

---

### Incidencia

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_incidencia` | PK | Identificador único |
| `id_despacho` | FK | Relación con Despacho |
| `id_tipo_incidencia` | FK | Relación con TipoIncidencia |
| `descripcion` | text | Descripción de la incidencia |
| `fecha_incidencia` | datetime | Fecha de la incidencia |

**Tabla:** `incidencias` | **PK:** `id_incidencia` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| belongsTo | `Despacho` | `id_despacho` |
| belongsTo | `TipoIncidencia` | `id_tipo_incidencia` |

---

### TipoEvidencia

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_tipo_evidencia` | PK | Identificador único |
| `nombre_tipo` | string | Nombre del tipo de evidencia (ej: Foto, PDF, Firma) |

**Tabla:** `tipos_evidencia` | **PK:** `id_tipo_evidencia` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| hasMany | `EvidenciaEntrega` | `id_tipo_evidencia` |

---

### TipoIncidencia

| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id_tipo_incidencia` | PK | Identificador único |
| `nombre_tipo` | string | Nombre del tipo de incidencia (ej: Producto dañado, Retraso) |

**Tabla:** `tipos_incidencia` | **PK:** `id_tipo_incidencia` | **Timestamps:** No

**Relaciones:**
| Tipo | Modelo | FK |
|------|--------|----|
| hasMany | `Incidencia` | `id_tipo_incidencia` |

---

## Diagrama de Relaciones por Módulo

### Seguridad
```
EstadoUsuario (1) ----< (N) Usuario (N) >---- (N) Rol (N) >---- (N) Permiso
                                 |                                  |        |
                                 |                                  1        1
                                 |                              Modulo    Accion
                                 |
                  SesionUsuario | Auditoria
```

### Farmacia
```
Cargo (1) ----< (N) ContactoFarmacia (N) >---- (1) Farmacia
```

### Repartidor
```
ExtensionCI (1) ----< (N) Repartidor (N) >---- (1) EstadoRepartidor
Licencia (1) ----< (N)        |        (N) >---- (1) Usuario
                              |
                  HistorialEstadoRepartidor
```

### Vehículo
```
Marca (1) ----< (N) Modelo (N) >---- (1) Vehiculo (N) >---- (1) EstadoVehiculo
Capacidad (1) ----< (N)      |                              |
                              |           HistorialEstadoVehiculo
```

### Pedido
```
EstadoPedido (1) ----< (N) Pedido (N) >---- (1) Farmacia
                              |        (N) >---- (1) Usuario
                              |
                  HistorialEstadoPedido
```

### Despacho
```
EstadoDespacho (1) ----< (N) Despacho (N) >---- (1) Pedido
                              |        (N) >---- (1) RutaParada
                              |        (N) >---- (1) ControlRuta
                              |
                  HistorialEstadoDespacho
```

### Logística
```
Ruta (1) ----< (N) RutaParada (N) >---- (1) Farmacia
  |                      |
  |               Despacho
  |
  +---< ControlRuta (N) >---- (1) Repartidor
                  |           >---- (1) Vehiculo
                  |
            Despacho
```

### Evidencia
```
TipoEvidencia (1) ----< (N) EvidenciaEntrega (N) >---- (1) Despacho
TipoIncidencia (1) ----< (N) Incidencia (N) >---- (1) Despacho
```
