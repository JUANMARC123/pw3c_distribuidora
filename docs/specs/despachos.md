# Módulo de Despachos y Entregas

## 1. Arquitectura Técnica (SDD)

### Archivos de Código Fuente
- `app/Http/Controllers/Despacho/DespachoController.php` — CRUD despachos, cambiar estado
- `app/Http/Controllers/Despacho/EvidenciaController.php` — CRUD evidencias anidadas
- `app/Http/Controllers/Despacho/IncidenciaController.php` — CRUD incidencias anidadas
- `app/Models/Despacho.php`
- `app/Models/EvidenciaEntrega.php`
- `app/Models/Incidencia.php`
- `app/Events/EntregaCompletada.php`, `app/Events/EntregaFallida.php`, `app/Events/DevolucionProducto.php`
- `app/Listeners/ActualizarEstadoDespachoListener.php`

### Stack
- **Controladores:** `DespachoController`, `EvidenciaController`, `IncidenciaController`
- **Modelos:** `Despacho`, `EvidenciaEntrega`, `Incidencia`
- **Eventos:** Los despachos son modificados por `ActualizarEstadoDespachoListener` (eventos del dominio Logística)
- **Base Controller:** `ApiController` (hereda `jsonResponse`, `errorResponse`, `safePaginate`)

### Endpoints

| Método | Ruta | Controlador |
|--------|------|-------------|
| GET | `/api/despachos` | `DespachoController::index` |
| POST | `/api/despachos` | `DespachoController::store` |
| GET | `/api/despachos/{id}` | `DespachoController::show` |
| PUT | `/api/despachos/{id}` | `DespachoController::update` |
| DELETE | `/api/despachos/{id}` | `DespachoController::destroy` |
| POST | `/api/despachos/{id}/cambiar-estado` | `DespachoController::cambiarEstado` |
| GET/POST/PUT/DELETE | `/api/despachos/{id}/evidencias[/{id}]` | `EvidenciaController` |
| GET/POST/PUT/DELETE | `/api/despachos/{id}/incidencias[/{id}]` | `IncidenciaController` |

### Modelo de Datos

#### `despachos`
| Columna | Tipo | Detalle |
|---------|------|---------|
| `id_despacho` | bigint PK | |
| `id_pedido` | bigint FK→pedidos | UNIQUE, RESTRICT |
| `id_detalle_hoja_ruta` | bigint FK→hoja_ruta_detalles | RESTRICT |
| `fecha_hora_despacho` | timestamp | `useCurrent()` |
| `estado_despacho` | enum | `pendiente`, `entregado`, `incidencia` |
| `monto_recibido` | decimal(10,2) | nullable |

#### `evidencias_entrega`
| Columna | Tipo | Detalle |
|---------|------|---------|
| `id_evidencia` | bigint PK | |
| `id_despacho` | bigint FK→despachos | CASCADE |
| `tipo_evidencia` | enum | `firma`, `foto`, `sello` |
| `archivo` | string | Ruta en storage |

#### `incidencias`
| Columna | Tipo | Detalle |
|---------|------|---------|
| `id_incidencia` | bigint PK | |
| `id_despacho` | bigint FK→despachos | CASCADE |
| `tipo_incidencia` | string | `cliente_ausente`, `producto_danado`, etc. |
| `descripcion` | text | nullable |
| `archivo` | string | nullable |
| `fecha_incidencia` | timestamp | `useCurrent()` |

### Reglas de Negocio
1. **Evidencia obligatoria para "entregado":** No se puede cambiar a `entregado` si el despacho no tiene al menos una evidencia registrada.
2. **Transiciones por eventos:** `EntregaCompletada`→`entregado`, `EntregaFallida`→`incidencia`, `DevolucionProducto`→`incidencia` (vía listener).
3. **Cascada:** Eliminar despacho elimina evidencias e incidencias.
4. **Archivos de evidencia:** Solo jpg/jpeg/png/gif/pdf, máx 2MB.

---

## 2. Especificación de Comportamiento (BDD)

### Escenario D1: Bloqueo de finalización sin evidencias
```
Dado un repartidor autenticado
Y un despacho en estado "pendiente"
Y el despacho NO tiene ninguna evidencia registrada
Cuando envía POST /api/despachos/{id}/cambiar-estado con estado "entregado"
Entonces el sistema rechaza la transacción con HTTP 422
Y devuelve: "Debe subir al menos una foto como evidencia antes de finalizar la entrega."
```
- **Test Feature:** `Despacho/ValidarEvidenciaEntregaTest.php` → `it('Escenario 1: Bloquea la finalizacion si el despacho no tiene ninguna evidencia')`
- **Controlador:** `DespachoController::cambiarEstado`

### Escenario D2: Finalización exitosa con evidencias
```
Dado un repartidor autenticado
Y un despacho en estado "pendiente"
Y el despacho TIENE al menos una evidencia registrada
Cuando envía POST /api/despachos/{id}/cambiar-estado con estado "entregado"
Entonces el sistema actualiza el estado a "entregado"
Y devuelve HTTP 200
```
- **Test Feature:** `Despacho/ValidarEvidenciaEntregaTest.php` → `it('Escenario 2: Permite completar la entrega si el despacho ya tiene una evidencia registrada')`
- **Controlador:** `DespachoController::cambiarEstado`

### Escenario D3: Transiciones de estado
```
Dado un administrador autenticado
Cuando cambia el estado de un despacho a "entregado" o "incidencia"
Entonces el estado se actualiza en la base de datos
Y se rechazan estados inválidos (HTTP 422)
Y se requiere el campo estado_despacho
Y 404 si el despacho no existe
```
- **Test Feature:** `Despacho/CambiarEstadoDespachoTest.php`
- **Test Unit:** `Despacho/Models/DespachoTest.php`
- **Controlador:** `DespachoController::cambiarEstado`

### Escenario D4: Gestión de evidencias e incidencias
```
Dado un administrador autenticado
Cuando sube una evidencia (tipo + archivo) a un despacho
Entonces la evidencia se registra
Y puede listar las evidencias del despacho
Y puede crear y listar incidencias del despacho
```
- **Test Feature:** `Despacho/IncidenciaEvidenciaTest.php`
- **Test Unit:** `Despacho/Models/EvidenciaEntregaTest.php`, `Despacho/Models/IncidenciaTest.php`
- **Controlador:** `EvidenciaController`, `IncidenciaController`

### Escenario D5: Ciclo completo vía eventos
```
Dado un flujo logístico completo (ruta → llegada → entrega)
Cuando se completa la entrega vía HojaRutaController
Entonces el despacho cambia a "entregado" automáticamente
Y una entrega fallida cambia a "incidencia"
Y una devolución cambia a "incidencia"
```
- **Test Feature:** `Despacho/CicloCompletoDespachoTest.php`
- **Test Unit:** `Despacho/Events/EntregaCompletadaTest.php`, `Despacho/Events/EntregaFallidaTest.php`, `Despacho/Events/DevolucionProductoTest.php`
- **Test Unit:** `Despacho/Listeners/ActualizarEstadoDespachoListenerTest.php`
- **Controlador:** `HojaRutaController` + `ActualizarEstadoDespachoListener`

---

## 3. Trazabilidad de Tests

### Tests Feature
| Archivo de Test | Grupo Pest | Controlador |
|---|---|---|
| `tests/Feature/Despacho/CambiarEstadoDespachoTest.php` | `despachos, estados` | `DespachoController::cambiarEstado` |
| `tests/Feature/Despacho/ValidarEvidenciaEntregaTest.php` | `despacho, evidencia, bdd` | `DespachoController::cambiarEstado` |
| `tests/Feature/Despacho/IncidenciaEvidenciaTest.php` | `despachos, incidencias, evidencias` | `EvidenciaController`, `IncidenciaController` |
| `tests/Feature/Despacho/CicloCompletoDespachoTest.php` | `despachos, ciclo-completo` | `HojaRutaController`, listeners |

### Tests Unit
| Archivo de Test | Namespace |
|---|---|
| `tests/Unit/Despacho/Models/DespachoTest.php` | `Tests\Unit\Despacho\Models` |
| `tests/Unit/Despacho/Models/EvidenciaEntregaTest.php` | `Tests\Unit\Despacho\Models` |
| `tests/Unit/Despacho/Models/IncidenciaTest.php` | `Tests\Unit\Despacho\Models` |
| `tests/Unit/Despacho/Events/EntregaCompletadaTest.php` | `Tests\Unit\Despacho\Events` |
| `tests/Unit/Despacho/Events/EntregaFallidaTest.php` | `Tests\Unit\Despacho\Events` |
| `tests/Unit/Despacho/Events/DevolucionProductoTest.php` | `Tests\Unit\Despacho\Events` |
| `tests/Unit/Despacho/Listeners/ActualizarEstadoDespachoListenerTest.php` | `Tests\Unit\Despacho\Listeners` |
