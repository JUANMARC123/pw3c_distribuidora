# SDD-LOGÍSTICA: Dominio Repartidores, Vehículos, Rutas y Despachos

**ID:** `PW3C-DIST-SDD-LOG` | **Depende de:** SDD-Global, SDD-Pedidos (pedidos para rutas)

---

## 1. CONTROL DEL DOCUMENTO

| Versión | Fecha | Descripción |
|---|---|---|
| 1.0 | 2026-07-13 | Versión inicial |

## 2. DESCRIPCIÓN GENERAL

### 2.1 Propósito

Gestionar el núcleo operativo de la distribución: administración de la flota (repartidores y vehículos), planificación y ejecución de hojas de ruta, registro de despachos con evidencias e incidencias.

### 2.2 Actores

| Actor | Acción |
|---|---|
| Administrador | CRUD completo de flota, rutas y despachos |
| Supervisor | Planificación y monitoreo de rutas |
| Repartidor | Ejecución de rutas: iniciar, registrar llegada, entregar, reportar incidencias, subir evidencias, regresar |

### 2.3 Alcance

| In Scope | Out of Scope |
|---|---|
| CRUD de repartidores con asignación vehicular | Geolocalización en tiempo real |
| CRUD de vehículos con estados | Optimización automática de rutas |
| Asignación y liberación de vehículos | |
| Hojas de ruta con paradas ordenadas | |
| Workflow completo: iniciar ruta → registrar llegada → entregar/fallar/devolver → regresar | |
| Despachos con cambio de estado manual y por eventos | |
| Incidencias con tipos predefinidos | |
| Evidencias (foto/firma/sello) | |
| 5 eventos de dominio con listeners | |

---

## 3. ARQUITECTURA

### 3.1 Patrones

- **Event-Driven:** 5 eventos de dominio orquestan cambios de estado en múltiples entidades
- **Observer / Subscriber:** 5 listeners agrupados como subscribers
- **State Pattern:** Máquinas de estado en Repartidor, Vehículo, HojaRuta, Despacho

### 3.2 Diagrama de Flujo de Eventos

```
                    RutaIniciada
                      /    |    \     \
                     /     |     \     `-- DespachoListener (idempotente)
                    /      |      \
                   /       |       `--- VehiculoListener (→ en_ruta)
                  /        |
    PedidosListener      RepartidorListener
    (pedidos →           (repartidor → 'en_ruta')
     'aprobado')

          En cada parada de la ruta:
          +---------------------------------------------+
          |  EntregaCompletada   EntregaFallida         |
          |    /    |    \        /    |    \           |
          | Pedidos|Desp.  |    Pedidos|Desp.  |       |
          | →'en-  |→'en-  |    →'pen-|→'inci-|       |
          | tregado'|tregado'|   diente'|dencia'|      |
          |         | Llegada|          | Llegada|      |
          |         |Parada  |          |Parada  |      |
          |         |(hora)  |          |(hora)  |      |
          |                                               |
          |  DevolucionProducto                           |
          |    /    |    \                               |
          | Pedidos|Desp.  |  LlegadaParada               |
          | →'en-  |→'inci-|  (hora)                     |
          | tregado'|dencia'|                            |
          +---------------------------------------------+

                    RepartidorRegresa
                      /        \
                     /          \
        RepartidorListener    VehiculoListener
        (→ 'activo')           (→ 'disponible')
```

---

## 4. CONTRATOS

### 4.1 Base de Datos

#### `repartidores`

| Columna | Tipo | FK / Restricciones |
|---|---|---|
| `id_repartidor` | bigint PK | |
| `id_usuario` | bigint | → `usuarios(id_usuario)` CASCADE, **UNIQUE** |
| `ci` | string | **UNIQUE** |
| `categoria_licencia` | enum(`A`,`B`,`C`) | DEFAULT `B` |
| `estado` | enum(`activo`,`inactivo`,`en_ruta`) | DEFAULT `activo` |
| `firebase_uid` | string | NULLABLE, **UNIQUE** |

#### `vehiculos`

| Columna | Tipo | Restricciones |
|---|---|---|
| `id_vehiculo` | bigint PK | |
| `placa` | string | **UNIQUE** |
| `marca` | string | |
| `modelo` | string | |
| `estado` | enum(`disponible`,`taller`,`en_ruta`) | DEFAULT `disponible` |

#### `asignaciones_vehiculo_repartidor`

| Columna | Tipo | FK |
|---|---|---|
| `id_asignacion` | bigint PK | |
| `id_repartidor` | bigint | → `repartidores(id_repartidor)` CASCADE |
| `id_vehiculo` | bigint | → `vehiculos(id_vehiculo)` CASCADE |
| `fecha_asignacion` | timestamp | DEFAULT CURRENT_TIMESTAMP |
| `fecha_devolucion` | timestamp | NULLABLE |

#### `hojas_ruta`

| Columna | Tipo | FK |
|---|---|---|
| `id_hoja_ruta` | bigint PK | |
| `fecha` | date | |
| `hora_salida` | time | NULLABLE |
| `hora_llegada` | time | NULLABLE |
| `id_repartidor` | bigint | → `repartidores(id_repartidor)` CASCADE |
| `id_vehiculo` | bigint | → `vehiculos(id_vehiculo)` CASCADE |
| `estado_ruta` | enum(`planificado`,`en_progreso`,`completado`,`cancelado`) | DEFAULT `planificado` |

#### `hoja_ruta_detalles`

| Columna | Tipo | FK |
|---|---|---|
| `id_detalle` | bigint PK | |
| `id_hoja_ruta` | bigint | → `hojas_ruta(id_hoja_ruta)` CASCADE |
| `id_pedido` | bigint | → `pedidos(id_pedido)` CASCADE |
| `orden_visita` | integer | |
| `hora_llegada` | time | NULLABLE |
| `hora_salida` | time | NULLABLE |

#### `despachos`

| Columna | Tipo | FK |
|---|---|---|
| `id_despacho` | bigint PK | |
| `id_pedido` | bigint | → `pedidos(id_pedido)` CASCADE, **UNIQUE** |
| `id_detalle_hoja_ruta` | bigint | → `hoja_ruta_detalles(id_detalle)` CASCADE |
| `fecha_hora_despacho` | timestamp | DEFAULT CURRENT_TIMESTAMP |
| `estado_despacho` | enum(`pendiente`,`entregado`,`incidencia`) | DEFAULT `pendiente` |
| `monto_recibido` | decimal(10,2) | NULLABLE |

#### `evidencias_entrega`

| Columna | Tipo | FK |
|---|---|---|
| `id_evidencia` | bigint PK | |
| `id_despacho` | bigint | → `despachos(id_despacho)` CASCADE |
| `tipo_evidencia` | enum(`firma`,`foto`,`sello`) | |
| `archivo` | string | Ruta en disco `public/evidencias/` |

#### `incidencias`

| Columna | Tipo | FK |
|---|---|---|
| `id_incidencia` | bigint PK | |
| `id_despacho` | bigint | → `despachos(id_despacho)` CASCADE |
| `tipo_incidencia` | enum(`cliente_ausente`,`producto_danado`,`retraso_trafico`,`devolucion_parcial`,`otro`) | |
| `descripcion` | text | NULLABLE |
| `archivo` | string | NULLABLE (ruta en disco) |
| `fecha_incidencia` | timestamp | DEFAULT CURRENT_TIMESTAMP |

### 4.2 API Endpoints (~45)

#### Repartidores (6 endpoints)

| Método | Ruta |
|---|---|
| GET | `/api/repartidores` |
| POST | `/api/repartidores` |
| GET | `/api/repartidores/{id}` |
| PUT | `/api/repartidores/{id}` |
| DELETE | `/api/repartidores/{id}` |
| POST | `/api/repartidores/{id}/cambiar-estado` |

#### Asignación Vehicular (3 endpoints, anidados)

| Método | Ruta |
|---|---|
| GET | `/api/repartidores/{id}/asignacion-vehiculo` |
| POST | `/api/repartidores/{id}/asignacion-vehiculo` |
| DELETE | `/api/repartidores/{id}/asignacion-vehiculo` |

#### Vehículos (6 endpoints)

| Método | Ruta |
|---|---|
| GET | `/api/vehiculos` |
| POST | `/api/vehiculos` |
| GET | `/api/vehiculos/{id}` |
| PUT | `/api/vehiculos/{id}` |
| DELETE | `/api/vehiculos/{id}` |
| POST | `/api/vehiculos/{id}/cambiar-estado` |

#### Hojas de Ruta (14 endpoints)

| Método | Ruta |
|---|---|
| GET | `/api/logistica/pedidos-pendientes` | Auxiliar |
| GET | `/api/logistica/repartidores-disponibles` | Auxiliar |
| GET | `/api/logistica/rutas-activas` | Auxiliar |
| GET | `/api/hojas-ruta` |
| POST | `/api/hojas-ruta` |
| GET | `/api/hojas-ruta/{id}` |
| PUT | `/api/hojas-ruta/{id}` |
| DELETE | `/api/hojas-ruta/{id}` |
| POST | `/api/hojas-ruta/{id}/iniciar` | Dispara `RutaIniciada` |
| POST | `/api/hojas-ruta/{id}/registrar-llegada` |
| POST | `/api/hojas-ruta/{id}/entrega-completada` | Dispara `EntregaCompletada` |
| POST | `/api/hojas-ruta/{id}/entrega-fallida` | Dispara `EntregaFallida` |
| POST | `/api/hojas-ruta/{id}/devolucion` | Dispara `DevolucionProducto` |
| POST | `/api/hojas-ruta/{id}/regreso-almacen` | Dispara `RepartidorRegresa` |

#### Despachos (6 endpoints)

| Método | Ruta |
|---|---|
| GET | `/api/despachos` |
| POST | `/api/despachos` |
| GET | `/api/despachos/{id}` |
| PUT | `/api/despachos/{id}` |
| DELETE | `/api/despachos/{id}` |
| POST | `/api/despachos/{id}/cambiar-estado` |

#### Incidencias (5 endpoints, anidados en despachos)

| Método | Ruta |
|---|---|
| GET | `/api/despachos/{id}/incidencias` |
| POST | `/api/despachos/{id}/incidencias` |
| GET | `/api/despachos/{id}/incidencias/{id}` |
| PUT | `/api/despachos/{id}/incidencias/{id}` |
| DELETE | `/api/despachos/{id}/incidencias/{id}` |

#### Evidencias (5 endpoints, anidados en despachos)

| Método | Ruta |
|---|---|
| GET | `/api/despachos/{id}/evidencias` |
| POST | `/api/despachos/{id}/evidencias` |
| GET | `/api/despachos/{id}/evidencias/{id}` |
| PUT | `/api/despachos/{id}/evidencias/{id}` |
| DELETE | `/api/despachos/{id}/evidencias/{id}` |

### 4.3 Eventos de Dominio

| Evento | Disparado por | Propiedades |
|---|---|---|
| `RutaIniciada` | POST `/hojas-ruta/{id}/iniciar` | `$hojaRuta` (HojaRuta), `$pedidoIds` (array), `$idVehiculo` (int) |
| `EntregaCompletada` | POST `/hojas-ruta/{id}/entrega-completada` | `$detalle` (HojaRutaDetalle), `$idDespacho`, `$idPedido` |
| `EntregaFallida` | POST `/hojas-ruta/{id}/entrega-fallida` | `$detalle`, `$idDespacho`, `$idPedido`, `$motivo` (string, default '') |
| `DevolucionProducto` | POST `/hojas-ruta/{id}/devolucion` | `$detalle`, `$idDespacho`, `$idPedido` |
| `RepartidorRegresa` | POST `/hojas-ruta/{id}/regreso-almacen` | `$hojaRuta`, `$idRepartidor`, `$idVehiculo` |

#### Matriz de Listeners

| Evento | ActualizarEstado PedidosListener | ActualizarEstado RepartidorListener | ActualizarEstado VehiculoListener | ActualizarEstado DespachoListener | RegistrarLlegada ParadaListener |
|---|---|---|---|---|---|
| **RutaIniciada** | pedidos → `aprobado` | repartidor → `en_ruta` | vehículo → `en_ruta` | (idempotente) | — |
| **EntregaCompletada** | pedido → `entregado` | — | — | despacho → `entregado` | registra `hora_llegada` |
| **EntregaFallida** | pedido → `pendiente` | — | — | despacho → `incidencia` | registra `hora_llegada` |
| **DevolucionProducto** | pedido → `entregado` | — | — | despacho → `incidencia` | registra `hora_llegada` |
| **RepartidorRegresa** | — | repartidor → `activo` | vehículo → `disponible` | — | — |

---

## 5. REGLAS DE NEGOCIO

### 5.1 Máquinas de Estado

```
Repartidor: activo ──→ en_ruta ──→ activo
               │
               └──→ inactivo

Vehiculo: disponible ──→ en_ruta ──→ disponible
                │
                └──→ taller

HojaRuta: planificado ──→ en_progreso ──→ completado
                                   │
                                   └──→ cancelado

Despacho: pendiente ──→ entregado
                │
                └──→ incidencia
```

### 5.2 Restricciones

| # | Regla |
|---|---|
| 1 | `id_pedido` en `despachos` es UNIQUE — un pedido no puede tener dos despachos |
| 2 | `id_usuario` en `repartidores` es UNIQUE — un usuario es un solo repartidor |
| 3 | `ci` en `repartidores` es UNIQUE |
| 4 | `placa` en `vehiculos` es UNIQUE |
| 5 | Al crear hoja de ruta: el repartidor debe estar `activo` (si no → 422) |
| 6 | Al iniciar ruta: `estado_ruta` debe ser `planificado` (si no → 422) |
| 7 | Despacho `cambiarEstado`: solo permite `pendiente→entregado`, `pendiente→incidencia`, `entregado→incidencia` |
| 8 | Al asignar vehículo: `AsignacionVehiculoService` cierra automáticamente la asignación anterior (fecha_devolucion = now) |
| 9 | Evidencia requerida antes de entregar: validación planificada (ver deuda técnica) |

---

## 6. CRITERIOS DE ACEPTACIÓN

```gherkin
Feature: Ciclo Completo de Despacho
  Scenario: Entrega exitosa
    Given un pedido "aprobado" en una ruta "planificado"
    When el repartidor inicia la ruta
    Then repartidor → "en_ruta", vehículo → "en_ruta", pedido → "aprobado"
    When registra llegada a la parada
    Then la parada tiene hora_llegada
    When completa la entrega
    Then despacho → "entregado", pedido → "entregado"
    When regresa a almacén
    Then ruta → "completado", repartidor → "activo", vehículo → "disponible"

  Scenario: Entrega fallida
    Given un despacho "pendiente"
    When marca entrega como fallida con motivo "cliente ausente"
    Then despacho → "incidencia", pedido revierte a "pendiente"

  Scenario: Devolución de producto
    Given un despacho "pendiente"
    When registra devolución
    Then despacho → "incidencia", pedido → "entregado"

Feature: Incidencias y Evidencias
  Scenario: Subir evidencia foto
    Given un despacho existente
    When POST /api/despachos/{id}/evidencias con archivo y tipo "foto"
    Then retorna 201 y la evidencia queda registrada

Feature: Asignación vehicular
  Scenario: Asignar vehículo a repartidor
    Given un repartidor sin asignación activa
    When POST /api/repartidores/{id}/asignacion-vehiculo
    Then retorna 201 y se crea la asignación

  Scenario: Reasignar vehículo cierra asignación anterior
    Given un repartidor con asignación activa
    When se asigna un nuevo vehículo
    Then la asignación anterior queda con fecha_devolucion not null
```

---

## 7. ESTRATEGIA DE VALIDACIÓN

### 7.1 Tests del dominio

| Tipo | Archivo | Tests |
|---|---|---|
| Feature | `CambiarEstadoDespachoTest.php` | 6 |
| Feature | `CicloCompletoDespachoTest.php` | 3 |
| Feature | `IncidenciaEvidenciaTest.php` | 2 |
| Feature | `HojaRutaTest.php` | 7 |
| Feature | `RepartidorCRUDTest.php` | 8 |
| Feature | `VehiculoCRUDTest.php` | 7 |
| Unit | `RepartidorTest.php` (model) | 9 |
| Unit | `VehiculoTest.php` (model) | 5 |
| Unit | `AsignacionVehiculoRepartidorTest.php` (model) | 6 |
| Unit | `HojaRutaTest.php` (model) | 7 |
| Unit | `HojaRutaDetalleTest.php` (model) | 6 |
| Unit | `DespachoTest.php` (model) | 7 |
| Unit | `AsignacionVehiculoServiceTest.php` (service) | 4 |
| Unit | `ActualizarEstadoDespachoListenerTest.php` | 3 |
| Unit | `ActualizarEstadoPedidosListenerTest.php` | 4 |
| Unit | `ActualizarEstadoRepartidorListenerTest.php` | 2 |
| Unit | `ActualizarEstadoVehiculoListenerTest.php` | 2 |
| Unit | `RegistrarLlegadaParadaListenerTest.php` | 4 |
| Unit | `RutaIniciadaTest.php` (event) | 2 |
| Unit | `EntregaCompletadaTest.php` (event) | 2 |
| Unit | `EntregaFallidaTest.php` (event) | 3 |
| Unit | `DevolucionProductoTest.php` (event) | 2 |
| Unit | `RepartidorRegresaTest.php` (event) | 2 |
| **Total** | | **103** |

### 7.2 Brechas

| Brecha | Impacto |
|---|---|
| Validación de evidencia antes de entregar (implementación pendiente en controller) | Medio |
| Sin tests de concurrencia en eventos (race conditions) | Medio |
| Sin test de que repartidor no pueda iniciar ruta de otro repartidor | Alto (autorización) |
| `onRutaIniciada` en DespachoListener es operación idempotente sin efecto real | Muy bajo |

---

## 8. SEGURIDAD Y PERMISOS

| Endpoint | Método | Rol mínimo |
|---|---|---|
| `/api/repartidores` | GET | Administrador |
| `/api/repartidores` | POST | Administrador |
| `/api/repartidores/{id}` | DELETE | Administrador |
| `/api/repartidores/{id}/asignacion-vehiculo/*` | * | Administrador |
| `/api/vehiculos` | * | Administrador |
| `/api/hojas-ruta` | GET | Supervisor+ |
| `/api/hojas-ruta` | POST | Supervisor+ |
| `/api/hojas-ruta/{id}` | DELETE | Administrador |
| `/api/hojas-ruta/{id}/iniciar` | POST | Repartidor (propietario) |
| `/api/hojas-ruta/{id}/registrar-llegada` | POST | Repartidor (propietario) |
| `/api/hojas-ruta/{id}/entrega-completada` | POST | Repartidor (propietario) |
| `/api/hojas-ruta/{id}/entrega-fallida` | POST | Repartidor (propietario) |
| `/api/hojas-ruta/{id}/devolucion` | POST | Repartidor (propietario) |
| `/api/hojas-ruta/{id}/regreso-almacen` | POST | Repartidor (propietario) |
| `/api/logistica/*` | GET | Supervisor+ |
| `/api/despachos` | GET/POST/PUT | Supervisor+ |
| `/api/despachos/{id}` | DELETE | Administrador |
| `/api/despachos/{id}/cambiar-estado` | POST | Repartidor+ |
| `/api/despachos/{id}/incidencias/*` | * | Repartidor+ |
| `/api/despachos/{id}/evidencias/*` | * | Repartidor+ |

---

## 9. DEUDA TÉCNICA DEL DOMINIO

| # | Ítem | Prioridad |
|---|---|---|
| 1 | **Validación de evidencia pendiente** — Los tests `ValidarEvidenciaEntregaTest` están planificados pero `DespachoController::cambiarEstado()` no verifica `$despacho->evidencias()->count() === 0` | 🟡 Media |
| 2 | **Eventos sin transacciones** — Si un listener falla, cambios de listeners previos no se rollbackean. Ej: en RutaIniciada, si falla VehiculoListener, Repartidor ya quedó `en_ruta` pero Vehículo no | 🟡 Media |
| 3 | **onRutaIniciada en DespachoListener es idempotente** — UPDATE despachos SET estado='pendiente' WHERE estado='pendiente' — sin efecto real, posible legacy | 🟢 Baja |
| 4 | **Sin verificación de propiedad del recurso** — Un repartidor podría (en teoría) marcar entrega de una ruta que no le pertenece | 🔴 Alta (vinculado a #1 global de permisos) |

---

*Fin del SDD-LOGÍSTICA — PW3C-DIST-SDD-LOG v1.0*
