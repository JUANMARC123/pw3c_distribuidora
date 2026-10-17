# Módulo de Pedidos

## 1. Arquitectura Técnica (SDD)

### Archivos de Código Fuente
- `app/Http/Controllers/Pedido/PedidoController.php` — CRUD pedidos, cambio de estado, PDF
- `app/Http/Controllers/Pedido/DetallePedidoController.php` — CRUD detalles anidados a pedidos
- `app/Models/Pedido.php` (con `DetallePedido`)
- `app/Models/DetallePedido.php`
- `app/Services/Pedido/PedidoPdfExtractor.php` — extracción de datos desde PDF
- `app/Listeners/ActualizarEstadoPedidosListener.php` — modifica estados por eventos de logística

### Endpoints

| Método | Ruta | Acción | Controlador |
|--------|------|--------|-------------|
| GET | `/api/pedidos` | Listar (filtros: estado, farmacia, fechas) | `PedidoController::index` |
| POST | `/api/pedidos` | Crear con productos en detalles | `PedidoController::store` |
| GET | `/api/pedidos/{id}` | Mostrar con detalles | `PedidoController::show` |
| PUT | `/api/pedidos/{id}` | Actualizar (solo farmacia y observaciones) | `PedidoController::update` |
| DELETE | `/api/pedidos/{id}` | Eliminar (protegido si tiene despacho) | `PedidoController::destroy` |
| POST | `/api/pedidos/{id}/cambiar-estado` | Cambiar estado manualmente | `PedidoController::cambiarEstado` |
| POST | `/api/pedidos/{id}/detalles` | Agregar detalle | `DetallePedidoController::store` |
| GET/PUT/DELETE | `/api/pedidos/{id}/detalles/{detalle}` | Gestionar detalle | `DetallePedidoController` |
| PUT | `/api/pedidos/{id}/detalles/{detalle}/actualizar-entrega` | Actualizar entrega de detalle | `DetallePedidoController::actualizarEntrega` |
| POST | `/api/pedidos/{id}/detalles/recalcular` | Recalcular pedido desde detalles | `DetallePedidoController::recalcularPedido` |
| GET | `/api/pedidos/{id}/pdf` | Descargar PDF | `PedidoController::descargarPdf` |
| POST | `/api/pedidos/{id}/pdf` | Subir PDF | `PedidoController::subirPdf` |

### Reglas de Negocio
1. **Máquina de 5 estados:** pendiente, aprobado, entregado, cancelado (sin validación de transiciones manuales)
2. **Historial de estados:** Cada cambio crea un registro en `historial_estados_pedido`
3. **Protección de eliminación:** No se elimina si tiene despacho asociado (FK restrict + catch 23000)
4. **Un pedido = un despacho:** `unique:despachos,id_pedido` garantiza 1:1
5. **Fecha automática:** `fecha_pedido = now()` forzado desde controlador

---

## 2. Especificación de Comportamiento (BDD)

### Escenario P1: CRUD de pedidos
```
Dado un administrador autenticado
Cuando crea un pedido con farmacia, usuario, estado y total
Entonces el pedido se registra con fecha automática
Y puede listar, mostrar, actualizar y eliminar pedidos
Y la eliminación falla si el pedido ya tiene un despacho
```
- **Test Feature:** `Pedido/PedidoCRUDTest.php`
- **Test Unit:** `Pedido/Models/PedidoTest.php`, `Pedido/Models/DetallePedidoTest.php`
- **Controlador:** `PedidoController`

### Escenario P2: Gestión de detalles del pedido
```
Dado un pedido existente
Cuando se agrega un detalle con producto, cantidad y precio_unitario
Entonces el detalle se registra
Y el pedido muestra los detalles al consultarlo
```
- **Test Feature:** `Pedido/PedidoCRUDTest.php` → `it('gestiona detalles del pedido')`
- **Controlador:** `DetallePedidoController`

### Escenario P3: Cambio de estado manual
```
Dado un pedido existente
Cuando se envía POST /api/pedidos/{id}/cambiar-estado con un estado válido
Entonces el estado del pedido se actualiza
Y se registra en el historial de estados
```
- **Test Feature:** `Pedido/PedidoCRUDTest.php` → `it('cambia estado del pedido')`
- **Controlador:** `PedidoController::cambiarEstado`

### Escenario P4: Extracción de datos desde PDF
```
Dado un archivo PDF de pedido
Cuando el PedidoPdfExtractor procesa el PDF
Entonces normaliza texto, extrae productos con cantidades y montos
Y deduplica productos con el mismo ID acumulando cantidades
Y retorna estructura con confianza y advertencias
```
- **Test Unit:** `Pedido/Services/PedidoPdfExtractorTest.php`
- **Servicio:** `PedidoPdfExtractor`

---

## 3. Trazabilidad de Tests

### Tests Feature
| Archivo de Test | Grupo Pest | Controlador |
|---|---|---|
| `tests/Feature/Pedido/PedidoCRUDTest.php` | `pedidos, crud` | `PedidoController` |

### Tests Unit
| Archivo de Test | Namespace |
|---|---|
| `tests/Unit/Pedido/Models/PedidoTest.php` | `Tests\Unit\Pedido\Models` |
| `tests/Unit/Pedido/Models/DetallePedidoTest.php` | `Tests\Unit\Pedido\Models` |
| `tests/Unit/Pedido/Services/PedidoPdfExtractorTest.php` | `Tests\Unit\Pedido\Services` |
