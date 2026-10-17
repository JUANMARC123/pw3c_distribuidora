# SDD-CAJA: Dominio Caja Diaria

**ID:** `PW3C-DIST-SDD-CAJ` | **Depende de:** SDD-Global, SDD-Logistica (hojas de ruta, repartidores)

---

## 1. CONTROL DEL DOCUMENTO

| Versión | Fecha | Descripción |
|---|---|---|
| 1.0 | 2026-07-13 | Versión inicial |

## 2. DESCRIPCIÓN GENERAL

### 2.1 Propósito

Controlar el efectivo que maneja cada repartidor durante una ruta: apertura de caja con monto inicial, registro de ingresos y gastos operativos, cierre con monto final, y consulta de resumen diario.

### 2.2 Actores

| Actor | Acción |
|---|---|
| Administrador | CRUD completo + cierre |
| Supervisor | Apertura, registro de ingresos/gastos, cierre, consultas |
| Operador | Solo consulta |
| Repartidor | Sin acceso directo (lo gestiona el supervisor) |

### 2.3 Alcance

| In Scope | Out of Scope |
|---|---|
| Apertura de caja por ruta/repartidor/día | Integración con sistema contable |
| Registro de ingresos | Arqueo automático |
| Registro de gastos con comprobante | |
| Cierre de caja con monto final | |
| Consulta de caja del día | |
| Resumen diario (conteo y totales) | |

---

## 3. ARQUITECTURA

Sin eventos. CRUD directo vía controlador. Validación inline con reglas de unicidad.

---

## 4. CONTRATOS

### 4.1 Base de Datos

#### `caja_repartidor`

| Columna | Tipo | FK |
|---|---|---|
| `id_caja` | bigint PK | |
| `id_hoja_ruta` | bigint | → `hojas_ruta(id_hoja_ruta)` CASCADE, **UNIQUE** |
| `id_repartidor` | bigint | → `repartidores(id_repartidor)` CASCADE |
| `fecha` | date | |
| `ingresos` | decimal(10,2) | DEFAULT 0 |
| `monto_final` | decimal(10,2) | NULLABLE |
| `monto_inicial` | decimal(10,2) | DEFAULT 0 |
| `estado_caja` | enum(`abierta`,`cerrada`,`auditada`) | DEFAULT `abierta` |

#### `gastos_operativos`

| Columna | Tipo | FK |
|---|---|---|
| `id_gasto` | bigint PK | |
| `id_caja` | bigint | → `caja_repartidor(id_caja)` CASCADE |
| `tipo_gasto` | string (enum: COMBUSTIBLE, ALIMENTACION, PEAJE, MANTENIMIENTO, IMPLEMENTOS, OTRO) | |
| `descripcion` | string | NULLABLE |
| `monto` | decimal(10,2) | |
| `comprobante` | string | NULLABLE (ruta de archivo) |
| `fecha` | timestamp | DEFAULT CURRENT_TIMESTAMP |

### 4.2 API Endpoints (6)

| Método | Ruta | Request | Response |
|---|---|---|---|
| POST | `/api/caja/abrir` | `{ id_hoja_ruta, id_repartidor, fecha, monto_inicial }` | 201 |
| POST | `/api/caja/ingreso` | `{ id_caja, monto }` | 200 |
| POST | `/api/caja/gasto` | `{ id_caja, tipo_gasto, descripcion?, monto, comprobante? }` | 200 |
| PUT | `/api/caja/cerrar/{id}` | `{ monto_final }` | 200 |
| GET | `/api/caja/dia` | `?fecha=YYYY-MM-DD&id_repartidor=` | 200 |
| GET | `/api/caja/resumen` | `?fecha=YYYY-MM-DD` | 200 |

### 4.3 Errores Comunes

| Código | Causa |
|---|---|
| 422 | Caja ya abierta para el mismo repartidor + fecha |
| 422 | Monto inválido (negativo o cero en campos que no aceptan) |
| 404 | Caja no encontrada |

---

## 5. REGLAS DE NEGOCIO

| # | Regla |
|---|---|
| 1 | No se puede abrir una caja para el mismo `id_repartidor + fecha` si ya existe una abierta |
| 2 | `id_hoja_ruta` debe ser único — una ruta tiene una sola caja |
| 3 | Para cerrar una caja, `monto_final` es requerido y debe ser >= 0 |
| 4 | Los ingresos incrementan `caja_repartidor.ingresos` automáticamente |
| 5 | Los gastos se registran como registro independiente y no afectan `ingresos` |

### 5.1 Máquina de Estado

```
Caja: abierta ──→ cerrada ──→ auditada
```

---

## 6. CRITERIOS DE ACEPTACIÓN

```gherkin
Feature: Ciclo de Caja
  Scenario: Abrir caja exitosamente
    Given una hoja de ruta y un repartidor existentes
    When POST /api/caja/abrir con datos válidos
    Then retorna 201 y la caja queda "abierta"

  Scenario: Registrar gasto en caja abierta
    Given una caja "abierta"
    When POST /api/caja/gasto con monto y tipo_gasto
    Then retorna 200

  Scenario: Cerrar caja
    Given una caja "abierta"
    When PUT /api/caja/cerrar/{id} con monto_final
    Then retorna 200 y la caja queda "cerrada"

  Scenario: No duplicar caja mismo repartidor+fecha
    Given una caja ya abierta para repartidor X en fecha Y
    When POST /api/caja/abrir con mismo repartidor y fecha
    Then retorna 422
```

---

## 7. ESTRATEGIA DE VALIDACIÓN

| Tipo | Archivo | Tests |
|---|---|---|
| Feature | `CajaDiariaTest.php` | 6 |
| Unit | `CajaRepartidorTest.php` (model) | 6 |
| Unit | `GastoOperativoTest.php` (model) | 4 |
| **Total** | | **16** |

### Brechas

| Brecha | Impacto |
|---|---|
| Sin test de que `ingresos` se actualice correctamente al sumar múltiples ingresos | Bajo |
| Sin test de que no se pueda registrar ingreso/gasto en caja cerrada | Medio |

---

## 8. SEGURIDAD Y PERMISOS

| Endpoint | Método | Rol mínimo |
|---|---|---|
| `/api/caja/abrir` | POST | Supervisor+ |
| `/api/caja/ingreso` | POST | Supervisor+ |
| `/api/caja/gasto` | POST | Supervisor+ |
| `/api/caja/cerrar/{id}` | PUT | Supervisor+ |
| `/api/caja/dia` | GET | Operador+ |
| `/api/caja/resumen` | GET | Operador+ |

---

## 9. DEUDA TÉCNICA DEL DOMINIO

| # | Ítem | Prioridad |
|---|---|---|
| 1 | El cálculo de `ingresos` se hace manualmente (suma en controller) — podría encapsularse en un Service | 🟢 Baja |
| 2 | No hay validación de que no se registren gastos después de cerrar la caja | 🟡 Media |

---

*Fin del SDD-CAJA — PW3C-DIST-SDD-CAJ v1.0*
