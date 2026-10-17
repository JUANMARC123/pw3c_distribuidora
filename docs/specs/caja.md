# Módulo de Caja Diaria

## 1. Arquitectura Técnica (SDD)

### Archivos de Código Fuente
- `app/Http/Controllers/Caja/CajaController.php` — apertura, ingresos, gastos, cierre, consultas
- `app/Models/CajaRepartidor.php`
- `app/Models/GastoOperativo.php`

### Stack
- **Controlador:** `CajaController`
- **Modelos:** `CajaRepartidor`, `GastoOperativo`
- **Sin eventos:** CRUD directo vía Eloquent

### Endpoints

| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | `/api/caja/abrir` | Abrir caja con monto inicial |
| POST | `/api/caja/ingreso` | Registrar ingreso |
| POST | `/api/caja/gasto` | Registrar gasto operativo |
| PUT | `/api/caja/cerrar/{id}` | Cerrar caja con monto final |
| GET | `/api/caja/dia` | Consultar caja del día |
| GET | `/api/caja/resumen` | Resumen diario |

### Reglas de Negocio
1. **Una caja por ruta:** `id_hoja_ruta` es UNIQUE en `caja_repartidor`.
2. **Estados:** `abierta` → `cerrada` → `auditada`.
3. **Gastos:** Solo se pueden registrar en cajas abiertas.
4. **Monto final:** Se asigna al cerrar la caja.

---

## 2. Especificación de Comportamiento (BDD)

### Escenario J1: Ciclo de vida de caja
```
Dado un administrador autenticado
Y una hoja de ruta existente
Cuando abre una caja con monto inicial para esa ruta
Entonces la caja se crea en estado "abierta"
Y puede registrar ingresos y gastos
Y al cerrar la caja con monto final, pasa a estado "cerrada"
```
- **Test Feature:** `Caja/CajaDiariaTest.php`
- **Test Unit:** `Caja/Models/CajaRepartidorTest.php`, `Caja/Models/GastoOperativoTest.php`
- **Controlador:** `CajaController`

### Escenario J2: Consultas de caja
```
Dado un administrador autenticado
Cuando consulta GET /api/caja/dia
Entonces obtiene la caja abierta del día
Y puede obtener el resumen diario de todas las cajas
```
- **Test Feature:** `Caja/CajaDiariaTest.php`
- **Controlador:** `CajaController`

---

## 3. Trazabilidad de Tests

### Tests Feature
| Archivo de Test | Grupo Pest | Controlador |
|---|---|---|
| `tests/Feature/Caja/CajaDiariaTest.php` | `caja` | `CajaController` |

### Tests Unit
| Archivo de Test | Namespace |
|---|---|
| `tests/Unit/Caja/Models/CajaRepartidorTest.php` | `Tests\Unit\Caja\Models` |
| `tests/Unit/Caja/Models/GastoOperativoTest.php` | `Tests\Unit\Caja\Models` |
