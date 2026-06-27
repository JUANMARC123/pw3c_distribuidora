# Análisis Completo del Sistema de Migraciones - Pw3c Distribuidora

> **Fecha:** 2026-06-25
> **Propósito:** Auditar las 59 migraciones, 47 modelos, 22 controladores y 19 módulos frontend para identificar tablas huérfanas, rutas no utilizadas y oportunidades de limpieza.

---

## 1. Resumen Ejecutivo

| Métrica | Valor |
|---------|-------|
| Migraciones totales | 59 |
| Tablas en BD (activas) | 53 |
| Modelos Eloquent | 47 |
| Controladores | 22 |
| Rutas API definidas | ~134 |
| Rutas llamadas desde frontend | ~122 (91%) |
| Rutas orphaned (sin frontend) | 12 |
| Modelos completamente huérfanos | 2 (`SesionUsuario`, `Auditoria`) |
| Modelos solo vía relación | 4 |
| Catálogos lookup (patrón repetido) | 16 |
| Historiales de estado (patrón repetido) | 5 |

### Conclusión principal

El sistema tiene **59 migraciones** porque:
1. **16 catálogos** son tablas separadas con patrón `tinyIncrements PK + string UNIQUE`
2. **8 historiales** de cambios de estado (5 siguen patrón idéntico)
3. **Diseño inicial incompleto**: 5 migraciones `add_` corrigen columnas olvidadas
4. **Fase 2**: 11 migraciones nuevas agregaron módulos completos (Caja, Planificación, Productos)

**No hay tablas sin modelo** (excepto pivotes). Pero hay **2 tablas con modelo que ningún controlador usa**: `sesiones_usuario` y `auditorias`.

---

## 2. Taxonomía de Migraciones

### 2.1 Por tipo de operación

| Prefijo | Significado | Cantidad |
|---------|-------------|----------|
| `create_` | Creación de tabla nueva | 52 |
| `add_` | Agregar columna/índice a tabla existente | 5 |
| `make_` | Modificar propiedad de columna existente | 1 |
| `drop_` | Eliminar tabla existente | 1 |

### 2.2 Por fase del proyecto

```
FASE 1 (Enero 2024) — 41 migraciones
├── Laravel defaults: 2014_10_12, 2019_08_19, 2019_12_14
├── 01_01: 16 catálogos lookup
├── 01_02: 6 entidades core
├── 01_03: 8 transaccionales/pivotes
└── 01_04: 8 historiales/auditoría

FASE 2 (Junio 2024) — 18 migraciones
├── 06_11_000001: DROP users table (migración a custom usuarios)
├── 06_11_000002-000005: Módulo Productos
├── 06_11_000006-000009: Módulo Rutas extendido
├── 06_11_000010-000012: Módulo Caja
├── 06_11_000013-000014: Fixes a pedidos y repartidores
├── 06_11_000015-000016: Asignaciones vehículo-repartidor
└── 06_11_000017-000018: Fixes a despachos y farmacias
```

### 2.3 Tablas modificadas después de creadas

| Tabla | Creada en | Modificada en | Cambio |
|-------|-----------|---------------|--------|
| `pedidos` | `01_03_000001` | `06_11_000004` | ADD subtotal, descuento, total |
| | | `06_11_000013` | ADD archivo_pedido |
| `control_rutas` | `01_03_000007` | `06_11_000007` | ADD id_estado_ruta FK |
| `repartidores` | `01_02_000004` | `06_11_000014` | ADD firebase_uid + unique id_usuario |
| `despachos` | `01_03_000008` | `06_11_000017` | ADD unique id_pedido |
| `farmacias` | `01_02_000002` | `06_11_000018` | MAKE latitud/longitud nullable |

---

## 3. Mapeo Completo: Migration → Modelo → Controlador → Frontend

### 3.1 Catálogos / Lookup (16 tablas)

| Migración | Tabla BD | Modelo | Controller | Frontend |
|-----------|----------|--------|------------|----------|
| `01_01_000001` | `estados_usuario` | ✅ | ✅ CatalogoController | ✅ usuarios.js |
| `01_01_000002` | `roles` | ✅ | ✅ RolController | ✅ roles.js |
| `01_01_000003` | `modulos` | ✅ | ✅ CatalogoController | 🟡 No llamado |
| `01_01_000004` | `acciones` | ✅ | ✅ CatalogoController | 🟡 No llamado |
| `01_01_000005` | `tablas_sistema` | ✅ | ✅ CatalogoController | 🟡 No llamado |
| `01_01_000006` | `estados_pedido` | ✅ | ✅ CatalogoController | ✅ pedidos.js |
| `01_01_000007` | `estados_repartidor` | ✅ | ✅ CatalogoController | ✅ repartidores.js |
| `01_01_000008` | `estados_vehiculo` | ✅ | ✅ CatalogoController | ✅ vehiculos.js |
| `01_01_000009` | `estados_despacho` | ✅ | ✅ CatalogoController | ✅ despachos.js |
| `01_01_000010` | `licencias` | ✅ | ✅ CatalogoController | ✅ repartidores.js |
| `01_01_000011` | `extensiones_ci` | ✅ | ✅ CatalogoController | ✅ repartidores.js |
| `01_01_000012` | `marcas` | ✅ | ✅ CatalogoController | ✅ vehiculos.js |
| `01_01_000013` | `capacidades` | ✅ | ✅ CatalogoController | ✅ vehiculos.js |
| `01_01_000014` | `tipos_incidencia` | ✅ | ✅ CatalogoController | ✅ despachos.js |
| `01_01_000015` | `tipos_evidencia` | ✅ | ✅ CatalogoController | ✅ despachos.js |
| `01_01_000016` | `cargos` | ✅ | ✅ CatalogoController | ✅ farmacias.js |

### 3.2 Entidades Core (6 tablas)

| Migración | Tabla BD | Modelo | Controller | Frontend |
|-----------|----------|--------|------------|----------|
| `01_02_000001` | `usuarios` | ✅ | ✅ UsuarioController | ✅ usuarios.js |
| `01_02_000002` | `farmacias` | ✅ | ✅ FarmaciaController | ✅ farmacias.js |
| `01_02_000004` | `repartidores` | ✅ | ✅ RepartidorController | ✅ repartidores.js |
| `01_02_000005` | `vehiculos` | ✅ | ✅ VehiculoController | ✅ vehiculos.js |
| `01_02_000006` | `rutas` | ✅ | ✅ RutaController | ✅ rutas.js |
| `06_11_000003` | `productos` | ✅ | ✅ ProductoController | ✅ productos.js |

### 3.3 Transaccionales (9 tablas)

| Migración | Tabla BD | Modelo | Controller | Frontend |
|-----------|----------|--------|------------|----------|
| `01_03_000001` | `pedidos` | ✅ | ✅ PedidoController | ✅ pedidos.js |
| `01_03_000002` | `contactos_farmacia` | ✅ | ✅ ContactoFarmaciaController | ✅ farmacias.js |
| `01_03_000003` | `usuario_roles` | ➖ Pivot | (belongsToMany) | ✅ usuarios.js |
| `01_03_000004` | `permisos` | ✅ | ✅ RolController | ✅ roles.js |
| `01_03_000005` | `rol_permiso` | ➖ Pivot | (belongsToMany) | ✅ roles.js |
| `01_03_000006` | `ruta_paradas` | ✅ | ✅ RutaController | ✅ rutas.js |
| `01_03_000007` | `control_rutas` | ✅ | ✅ ControlRutaController | ✅ controles-ruta.js |
| `01_03_000008` | `despachos` | ✅ | ✅ DespachoController | ✅ despachos.js |
| `06_11_000005` | `detalle_pedido` | ✅ | ✅ DetallePedidoController | ✅ pedidos.js |

### 3.4 Historiales y Auditoría (8 tablas)

| Migración | Tabla BD | Modelo | Controller | Frontend | Estado |
|-----------|----------|--------|------------|----------|--------|
| `01_04_000001` | `historial_estado_pedido` | ✅ | ✅ PedidoController | ⬜ Indirecto | ✅ |
| `01_04_000002` | `historial_estado_repartidor` | ✅ | ✅ RepartidorController | ⬜ Indirecto | ✅ |
| `01_04_000003` | `historial_estado_vehiculo` | ✅ | ✅ VehiculoController | ⬜ Indirecto | ✅ |
| `01_04_000004` | `historial_estado_despacho` | ✅ | ✅ DespachoController | ⬜ Indirecto | ✅ |
| `01_04_000005` | `auditorias` | ✅ | ❌ Ninguno | ❌ | **🔴 HUÉRFANO** |
| `01_04_000006` | `sesiones_usuario` | ✅ | ❌ Ninguno | ❌ | **🔴 HUÉRFANO** |
| `01_04_000007` | `incidencias` | ✅ | ✅ IncidenciaController | ✅ despachos.js | ✅ |
| `01_04_000008` | `evidencias_entrega` | ✅ | ✅ EvidenciaController | ✅ despachos.js | ✅ |
| `06_11_000008` | `historial_estado_ruta` | ✅ | ✅ ControlRutaController | ⬜ Indirecto | ✅ |

### 3.5 Módulo Caja (3 tablas)

| Migración | Tabla BD | Modelo | Controller | Frontend |
|-----------|----------|--------|------------|----------|
| `06_11_000010` | `estados_caja` | ✅ | (vía relación) | ⬜ |
| `06_11_000011` | `caja_repartidor` | ✅ | ✅ CajaController | ✅ caja.js |
| `06_11_000012` | `gastos_operativos` | ✅ | ✅ CajaController | ✅ caja.js |

### 3.6 Módulo Logística Extendido (5 tablas)

| Migración | Tabla BD | Modelo | Controller | Frontend |
|-----------|----------|--------|------------|----------|
| `06_11_000006` | `estados_ruta` | ✅ | (vía relación) | ⬜ |
| `06_11_000009` | `planificacion_rutas` | ✅ | ✅ CentroLogisticoController | ✅ planificador.js |
| `06_11_000015` | `asignaciones_vehiculo_repartidor` | ✅ | (vía relación) | ⬜ |
| `06_11_000016` | `control_ruta_paradas` | ✅ | ✅ CentroLogisticoController | ⬜ |

### 3.7 Tablas sin modelo

| Migración | Tabla | Nota |
|-----------|-------|------|
| `2014_10_12` | `password_resets` | Laravel default |
| `2019_08_19` | `failed_jobs` | Laravel default, sin colas |
| `2019_12_14` | `personal_access_tokens` | Sanctum |
| `06_11_000001` | ~~users~~ | **DROPPED** (reemplazada por `usuarios`) |

---

## 4. Rutas Orphaned (12 endpoints sin frontend)

### Grupo 1: RepartidorVehiculoController (3 rutas)

```php
GET    /repartidores/{repartidor}/asignacion-vehiculo
POST   /repartidores/{repartidor}/asignacion-vehiculo
DELETE /repartidores/{repartidor}/asignacion-vehiculo
```

**Causa:** El frontend asigna vehículos directamente en el formulario de repartidor enviando `id_vehiculo` en el payload. El `RepartidorVehiculoController` nunca se integró en frontend.

### Grupo 2: Evidencia show/update (2 rutas)

```php
GET  /despachos/{despacho}/evidencias/{evidencia}
PUT  /despachos/{despacho}/evidencias/{evidencia}
```

**Causa:** El frontend solo lista, crea y elimina evidencias. Nunca edita ni ve detalle individual.

### Grupo 3: CentroLogístico (2 rutas)

```php
POST /logistica/planificacion/borrador
GET  /logistica/rutas-activas/{id}
```

**Causa:** `guardarBorrador` nunca se implementó en frontend. `detalleRutaActiva` es innecesario porque la lista ya trae datos completos.

### Grupo 4: Catálogos endpoint duplicados (5 rutas)

```php
GET /catalogos/modelos-por-marca/{idMarca}
GET /catalogos/roles
GET /catalogos/modulos
GET /catalogos/acciones
GET /catalogos/tablas-sistema
```

**Causa:** El `CatalogoController` expone endpoints individuales, pero el frontend consume genéricamente via `/catalogos/{key}`. Algunos compiten con rutas dedicadas (`/roles`).

---

## 5. Modelos Sin Uso Directo en Controladores

### 5.1 Completamente huérfanos (2)

| Modelo | Tabla | Migración | Controlador | Propuesta |
|--------|-------|-----------|-------------|-----------|
| `Auditoria` | `auditorias` | `01_04_000005` | ❌ Ninguno | Eliminar |
| `SesionUsuario` | `sesiones_usuario` | `01_04_000006` | ❌ Ninguno | Eliminar |

### 5.2 Solo vía relación (4) — funcionales, sin cambios

| Modelo | Se accede mediante | En controlador |
|--------|-------------------|----------------|
| `EstadoDetalle` | `$detalle->estado()` | DetallePedidoController |
| `EstadoRuta` | `$control->estado()` | ControlRutaController |
| `EstadoCaja` | `$caja->estado()` | CajaController |
| `AsignacionVehiculoRepartidor` | `$repartidor->asignacionVehiculoActiva()` | CentroLogisticoController |

---

## 6. Propuesta de Normalización (Enfoque Conservador)

### 6.1 Fase 1: Eliminar tablas huérfanas

**Tablas a eliminar:**
- ~~`sesiones_usuario`~~ (modelo, migración)
- ~~`auditorias`~~ (modelo, migración)

**Acciones:**
1. Crear migración `drop_sesiones_usuario_and_auditorias_tables.php`
2. Eliminar modelos `SesionUsuario.php` y `Auditoria.php`
3. Eliminar relaciones `sesiones()` y `auditorias()` en `Usuario.php`

### 6.2 Fase 2: Limpiar rutas orphaned

**12 rutas a eliminar de `routes/api.php`:**
- 3 de `RepartidorVehiculoController`
- 2 de `EvidenciaController` (show, update)
- 2 de `CentroLogisticoController` (guardarBorrador, detalleRutaActiva)
- 5 de `CatalogoController` (modelos-por-marca, roles, modulos, acciones, tablas-sistema)

**Controladores a limpiar:**
- Eliminar `RepartidorVehiculoController` (completo)
- Eliminar métodos `show`/`update` de `EvidenciaController`
- Eliminar `guardarBorrador`/`detalleRutaActiva` de `CentroLogisticoController`
- Eliminar 5 métodos duplicados de `CatalogoController`

### 6.3 Fase 3: Mantener estructuras actuales

| Elemento | Decisión |
|----------|----------|
| 16 tablas catálogo | Mantener separadas (claridad por dominio) |
| 5 historiales `historial_estado_*` | Mantener separados |
| Tablas Laravel default | Requeridas por el framework |
| `AsignacionVehiculoRepartidor` | Mantener (funcional vía relación) |

---

## 7. Resumen de Carga por Tabla

### Alta carga (transaccionales diarias)

| Tabla | Tipo |
|-------|------|
| `pedidos` | Órdenes |
| `detalle_pedido` | Líneas de pedido |
| `despachos` | Despachos |
| `control_rutas` | Ejecución de rutas |
| `historial_estado_pedido` | Log de cambios |
| `incidencias` | Incidencias |
| `evidencias_entrega` | Evidencias |
| `caja_repartidor` | Cierre de caja |

### Carga cero (huérfanas)

| Tabla | Razón |
|-------|-------|
| ~~`auditorias`~~ | Sin controlador, sin frontend |
| ~~`sesiones_usuario`~~ | Sin controlador, sin frontend |

---

## 8. Conclusión

### Problemas identificados

1. **59 migraciones es alto** — 24 de ellas (16 catálogos + 8 historiales) siguen patrones casi idénticos
2. **2 tablas con datos cero** ocupan espacio sin beneficio
3. **12 endpoints orphaned** aumentan mantenimiento sin valor
4. **5 ADD migrations** por diseño inicial incompleto

### Beneficios de la limpieza

| Acción | Impacto |
|--------|---------|
| Eliminar sesiones + auditorías | -2 migraciones, -2 modelos, ~100 líneas |
| Eliminar rutas orphaned | -12 rutas, ~300 líneas |
| Eliminar métodos controller | ~200 líneas |
| **Total** | **~600 líneas menos** |

---

## Apéndice A: Lista Completa de Migraciones

### CREATE (52)

```
# Laravel Defaults
2014_10_12_100000_create_password_resets_table
2019_08_19_000000_create_failed_jobs_table
2019_12_14_000001_create_personal_access_tokens_table

# Catálogos (16)
2024_01_01_000001_create_estados_usuario_table
2024_01_01_000002_create_roles_table
2024_01_01_000003_create_modulos_table
2024_01_01_000004_create_acciones_table
2024_01_01_000005_create_tablas_sistema_table
2024_01_01_000006_create_estados_pedido_table
2024_01_01_000007_create_estados_repartidor_table
2024_01_01_000008_create_estados_vehiculo_table
2024_01_01_000009_create_estados_despacho_table
2024_01_01_000010_create_licencias_table
2024_01_01_000011_create_extensiones_ci_table
2024_01_01_000012_create_marcas_table
2024_01_01_000013_create_capacidades_table
2024_01_01_000014_create_tipos_incidencia_table
2024_01_01_000015_create_tipos_evidencia_table
2024_01_01_000016_create_cargos_table

# Entidades Core (6)
2024_01_02_000001_create_usuarios_table
2024_01_02_000002_create_farmacias_table
2024_01_02_000003_create_modelos_table
2024_01_02_000004_create_repartidores_table
2024_01_02_000005_create_vehiculos_table
2024_01_02_000006_create_rutas_table

# Transaccionales/Pivotes (9)
2024_01_03_000001_create_pedidos_table
2024_01_03_000002_create_contactos_farmacia_table
2024_01_03_000003_create_usuario_roles_table
2024_01_03_000004_create_permisos_table
2024_01_03_000005_create_rol_permiso_table
2024_01_03_000006_create_ruta_paradas_table
2024_01_03_000007_create_control_rutas_table
2024_01_03_000008_create_despachos_table

# Historial/Auditoría (8)
2024_01_04_000001_create_historial_estado_pedido_table
2024_01_04_000002_create_historial_estado_repartidor_table
2024_01_04_000003_create_historial_estado_vehiculo_table
2024_01_04_000004_create_historial_estado_despacho_table
2024_01_04_000005_create_auditorias_table
2024_01_04_000006_create_sesiones_usuario_table
2024_01_04_000007_create_incidencias_table
2024_01_04_000008_create_evidencias_entrega_table

# Phase 2 (11)
2024_06_11_000002_create_estados_detalle_table
2024_06_11_000003_create_productos_table
2024_06_11_000005_create_detalle_pedido_table
2024_06_11_000006_create_estados_ruta_table
2024_06_11_000008_create_historial_estado_ruta_table
2024_06_11_000009_create_planificacion_rutas_table
2024_06_11_000010_create_estados_caja_table
2024_06_11_000011_create_caja_repartidor_table
2024_06_11_000012_create_gastos_operativos_table
2024_06_11_000015_create_asignaciones_vehiculo_repartidor_table
2024_06_11_000016_create_control_ruta_paradas_table
```

### ADD (5)

```
2024_06_11_000004_add_subtotal_total_to_pedidos_table
2024_06_11_000007_add_estado_to_control_rutas_table
2024_06_11_000013_add_archivo_pedido_to_pedidos_table
2024_06_11_000014_add_firebase_uid_and_unique_user_to_repartidores_table
2024_06_11_000017_add_unique_id_pedido_to_despachos_table
```

### MAKE (1)

```
2024_06_11_000018_make_farmacia_coordinates_nullable
```

### DROP (1)

```
2024_06_11_000001_drop_users_table
```

---

## Apéndice B: Patrones Repetidos

### Patrón Catálogo (16 instancias)

```sql
CREATE TABLE catalogo_x (
    id     tinyIncrements PK,
    nombre string(50-100) UNIQUE
);
```

### Patrón Historial de Estado (5 instancias)

```sql
CREATE TABLE historial_estado_x (
    id_historial bigIncrements PK,
    fk_entidad   FK,
    fk_estado    FK,
    fecha_inicio timestamp,
    fecha_fin    timestamp NULL,
    UNIQUE(fk_entidad, fecha_inicio)
);
```

### Patrón Entidad-Estado-Historial (5 grupos)

| Entidad | Tabla estado | Tabla historial |
|---------|-------------|-----------------|
| `pedidos` | `estados_pedido` | `historial_estado_pedido` |
| `repartidores` | `estados_repartidor` | `historial_estado_repartidor` |
| `vehiculos` | `estados_vehiculo` | `historial_estado_vehiculo` |
| `despachos` | `estados_despacho` | `historial_estado_despacho` |
| `control_rutas` | `estados_ruta` | `historial_estado_ruta` |
