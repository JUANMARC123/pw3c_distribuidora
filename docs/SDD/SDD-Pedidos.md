# SDD-PEDIDOS: Dominio Pedidos

**ID:** `PW3C-DIST-SDD-PED` | **Depende de:** SDD-Global, SDD-Catalogo (farmacias, productos)

---

## 1. CONTROL DEL DOCUMENTO

| Versión | Fecha | Descripción |
|---|---|---|
| 1.0 | 2026-07-13 | Versión inicial |

## 2. DESCRIPCIÓN GENERAL

### 2.1 Propósito

Gestionar el ciclo de vida de los pedidos de farmacias: creación con productos, cálculo de totales, cambio de estados, extracción de datos desde PDF, y subida/descarga de archivos.

### 2.2 Actores

| Actor | Acción |
|---|---|
| Administrador | CRUD completo + cambio de estado |
| Supervisor | CRUD sin eliminar + cambio de estado |
| Operador | CRUD básico + cambio de estado |

### 2.3 Alcance

| In Scope | Out of Scope |
|---|---|
| Pedidos con múltiples productos (detalles) | Pagos en línea |
| Cálculo automático de subtotal y total | Integración con ERP |
| Extracción de datos desde PDF | Workflow de aprobación multi-nivel |
| Subida y descarga de archivo PDF del pedido | |
| Cambio de estado (pendiente/aprobado/entregado/cancelado) | |

---

## 3. ARQUITECTURA

Sin eventos propios. Los pedidos son modificados por listeners del dominio Logística (ver SDD-Logistica sección 4.3). El servicio `PedidoPdfExtractor` encapsula la lógica de parsing de PDF.

---

## 4. CONTRATOS

### 4.1 Base de Datos

#### `pedidos`

| Columna | Tipo | FK |
|---|---|---|
| `id_pedido` | bigint PK | |
| `id_farmacia` | bigint | → `farmacias(id_farmacia)` CASCADE |
| `id_usuario` | bigint | → `usuarios(id_usuario)` CASCADE |
| `estado` | enum(`pendiente`,`aprobado`,`entregado`,`cancelado`) | DEFAULT `pendiente` |
| `metodo_pago` | enum(`efectivo`,`qr`,`transferencia`,`credito`) | NULLABLE |
| `fecha_pedido` | timestamp | DEFAULT CURRENT_TIMESTAMP |
| `observaciones` | text | NULLABLE |
| `subtotal` | decimal(10,2) | DEFAULT 0 |
| `descuento` | decimal(10,2) | DEFAULT 0 |
| `total` | decimal(10,2) | DEFAULT 0 |
| `archivo_pedido` | string | NULLABLE |

#### `detalle_pedido`

| Columna | Tipo | FK |
|---|---|---|
| `id_detalle` | bigint PK | |
| `id_pedido` | bigint | → `pedidos(id_pedido)` CASCADE |
| `id_producto` | bigint | → `productos(id_producto)` CASCADE |
| `cantidad_solicitada` | integer | |
| `cantidad_entregada` | integer | DEFAULT 0 |
| `cantidad_rechazada` | integer | DEFAULT 0 |
| `motivo_rechazo` | string | NULLABLE |
| `precio_unitario` | decimal(10,2) | |
| `subtotal` | decimal(10,2) | DEFAULT 0 |

### 4.2 API Endpoints

#### Pedidos (9 endpoints)

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/pedidos` | Listar (paginado, filtros: estado, farmacia, fechas) |
| POST | `/api/pedidos` | Crear (asigna usuario autenticado, estado `pendiente`) |
| GET | `/api/pedidos/{id}` | Mostrar (con detalles, despacho, ruta) |
| PUT | `/api/pedidos/{id}` | Actualizar (incluye cambio de estado, método de pago) |
| DELETE | `/api/pedidos/{id}` | Eliminar |
| POST | `/api/pedidos/{id}/cambiar-estado` | Cambiar estado |
| POST | `/api/pedidos/{id}/subir-archivo` | Subir PDF (max 10MB) |
| GET | `/api/pedidos/{id}/descargar-archivo` | Descargar PDF |
| POST | `/api/pedidos/extraer-pdf` | Extraer datos desde PDF subido |

#### Detalles de pedido (5 endpoints, anidados)

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/pedidos/{id}/detalles` | Listar detalles con producto |
| POST | `/api/pedidos/{id}/detalles` | Agregar detalle (recalcula totales) |
| PUT | `/api/pedidos/{id}/detalles/{id}` | Actualizar detalle (recalcula totales) |
| DELETE | `/api/pedidos/{id}/detalles/{id}` | Eliminar detalle (recalcula totales) |
| PUT | `/api/pedidos/{id}/detalles/{id}/entrega` | Actualizar cantidades entregadas/rechazadas |

### 4.3 Fórmulas

| Campo | Cálculo |
|---|---|
| `detalle_pedido.subtotal` | `cantidad_solicitada × precio_unitario` |
| `pedido.subtotal` | `SUM(detalle_pedido.subtotal)` (recalculado automáticamente) |
| `pedido.total` | `subtotal - descuento` |

---

## 5. REGLAS DE NEGOCIO

| # | Regla |
|---|---|
| 1 | Al crear un pedido, el `id_usuario` se asigna del token autenticado |
| 2 | Al crear un pedido, el estado inicial es `pendiente` |
| 3 | Cada vez que se agrega/modifica/elimina un detalle, se recalculan `subtotal` y `total` del pedido |
| 4 | El archivo PDF se almacena en `storage/app/public/pedidos/` |
| 5 | Los pedidos son modificados externamente por eventos de Logística (ver SDD-Logistica) |

### 5.1 Máquina de Estado

```
Pedido: pendiente ──→ aprobado ──→ entregado
               │                    ↑
               └──→ cancelado       │
                              (vía DevolucionProducto)
```

---

## 6. CRITERIOS DE ACEPTACIÓN

```gherkin
Feature: CRUD Pedidos
  Scenario: Crear pedido con detalles
    Given una farmacia y un producto existentes
    When POST /api/pedidos con id_farmacia y 2 detalles
    Then retorna 201 con subtotal calculado

  Scenario: Cambiar estado
    Given un pedido "pendiente"
    When POST /api/pedidos/{id}/cambiar-estado con estado="aprobado"
    Then retorna 200 y el pedido queda "aprobado"

Feature: Extracción PDF
  Scenario: Subir PDF válido
    When POST /api/pedidos/extraer-pdf con archivo PDF
    Then retorna 200 con datos extraídos
```

---

## 7. ESTRATEGIA DE VALIDACIÓN

| Tipo | Archivo | Tests |
|---|---|---|
| Feature | `PedidoCRUDTest.php` | 8 |
| Unit | `PedidoTest.php` (model) | 8 |
| Unit | `DetallePedidoTest.php` (model) | 5 |
| Unit | `DespachoTest.php` (model) | 7 |
| Unit | `IncidenciaTest.php` (model) | 4 |
| Unit | `EvidenciaEntregaTest.php` (model) | 4 |
| Unit | `PedidoPdfExtractorTest.php` (service) | 7 |
| **Total** | | **43** |

---

## 8. SEGURIDAD Y PERMISOS

| Endpoint | Método | Rol mínimo |
|---|---|---|
| `/api/pedidos` | GET | Operador+ |
| `/api/pedidos` | POST | Operador+ |
| `/api/pedidos/{id}` | GET | Operador+ |
| `/api/pedidos/{id}` | PUT | Operador+ |
| `/api/pedidos/{id}` | DELETE | Administrador |
| `/api/pedidos/{id}/cambiar-estado` | POST | Operador+ |
| `/api/pedidos/{id}/subir-archivo` | POST | Operador+ |
| `/api/pedidos/{id}/descargar-archivo` | GET | Operador+ |
| `/api/pedidos/extraer-pdf` | POST | Operador+ |
| `/api/pedidos/{id}/detalles/*` | * | Operador+ |

---

## 9. DEUDA TÉCNICA DEL DOMINIO

| # | Ítem | Prioridad |
|---|---|---|
| 1 | `extraerPdf` usa reflection para métodos privados — el service tiene 0 coverage de integración real con PDF | 🟡 Media |
| 2 | Sin validación de que `cantidad_entregada + cantidad_rechazada <= cantidad_solicitada` | 🟡 Media |

---

*Fin del SDD-PEDIDOS — PW3C-DIST-SDD-PED v1.0*
