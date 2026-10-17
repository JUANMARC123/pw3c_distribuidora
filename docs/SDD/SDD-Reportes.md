# SDD-REPORTES: Dominio Reportes

**ID:** `PW3C-DIST-SDD-RPT` | **Depende de:** SDD-Global (solo consultas a tablas de otros dominios)

---

## 1. CONTROL DEL DOCUMENTO

| Versión | Fecha | Descripción |
|---|---|---|
| 1.0 | 2026-07-13 | Versión inicial |

## 2. DESCRIPCIÓN GENERAL

### 2.1 Propósito

Proporcionar métricas operativas y estadísticas agregadas del sistema para la toma de decisiones: conteos globales, distribución de estados, tendencias temporales.

### 2.2 Actores

| Actor | Acceso |
|---|---|
| Administrador | Todos los reportes |
| Supervisor | Todos los reportes |
| Operador | Todos los reportes |
| Repartidor | Solo dashboard básico |

### 2.3 Alcance

| In Scope | Out of Scope |
|---|---|
| 7 endpoints de reportes agregados | Dashboard interactivo con gráficos |
| Conteos por estado de todas las entidades | Exportación a Excel/PDF |
| Tendencia de pedidos por día | Alertas automáticas |
| Agrupación de incidencias por tipo | Machine learning / predicción |

---

## 3. ARQUITECTURA

Endpoints de solo lectura (GET). Sin escritura en BD. Todos usan consultas Eloquent con `groupBy` y `selectRaw`.

---

## 4. CONTRATOS

### 4.1 API Endpoints (7)

| Método | Ruta | Descripción | Response |
|---|---|---|---|
| GET | `/api/reportes/resumen` | Conteos globales del sistema | `{ total_pedidos, total_despachos, total_farmacias, total_productos, ... }` |
| GET | `/api/reportes/pedidos-por-estado` | Pedidos agrupados por estado | `[{ estado, total }, ...]` |
| GET | `/api/reportes/despachos-por-estado` | Despachos agrupados por estado_despacho | `[{ estado_despacho, total }, ...]` |
| GET | `/api/reportes/pedidos-por-dia` | Pedidos por día (últimos N días, default 30) | `[{ fecha, total }, ...]` |
| GET | `/api/reportes/repartidores-por-estado` | Repartidores agrupados por estado | `[{ estado, total }, ...]` |
| GET | `/api/reportes/vehiculos-por-estado` | Vehículos agrupados por estado | `[{ estado, total }, ...]` |
| GET | `/api/reportes/incidencias-por-tipo` | Incidencias agrupadas por tipo_incidencia | `[{ tipo_incidencia, total }, ...]` |

### 4.2 Query Parameters Comunes

| Parámetro | Tipo | Endpoints aplicables |
|---|---|---|
| `?dias=N` | integer (default 30) | `pedidos-por-dia` |
| `?fecha_desde=` | date | Todos con filtro temporal |
| `?fecha_hasta=` | date | Todos con filtro temporal |

---

## 5. REGLAS DE NEGOCIO

| # | Regla |
|---|---|
| 1 | Todos los reportes son de solo lectura — no modifican datos |
| 2 | `pedidos-por-dia` usa `DATE(fecha_pedido)` para agrupar |
| 3 | `resumen` ejecuta 7 consultas `COUNT` + `GROUP BY` simultáneas |

---

## 6. CRITERIOS DE ACEPTACIÓN

```gherkin
Feature: Reportes
  Scenario: Obtener resumen del sistema
    Given existen pedidos, despachos, farmacias y productos en BD
    When GET /api/reportes/resumen
    Then retorna 200 con totales de cada entidad

  Scenario: Pedidos por día
    Given pedidos creados en diferentes fechas
    When GET /api/reportes/pedidos-por-dia?dias=7
    Then retorna 200 con pedidos agrupados por fecha
```

---

## 7. ESTRATEGIA DE VALIDACIÓN

| Tipo | Archivo | Tests |
|---|---|---|
| Feature | `ReportesTest.php` | 7 |
| **Total** | | **7** |

### Brechas

| Brecha | Impacto |
|---|---|
| Solo se verifica `assertOk` — no se valida la estructura ni los valores del response | Medio — los tests no detectan cambios en la lógica de agregación |
| Sin tests con datos específicos para verificar conteos exactos | Medio |

---

## 8. SEGURIDAD Y PERMISOS

| Endpoint | Método | Rol mínimo |
|---|---|---|
| `/api/reportes/resumen` | GET | Operador+ |
| `/api/reportes/pedidos-por-estado` | GET | Operador+ |
| `/api/reportes/despachos-por-estado` | GET | Operador+ |
| `/api/reportes/pedidos-por-dia` | GET | Operador+ |
| `/api/reportes/repartidores-por-estado` | GET | Operador+ |
| `/api/reportes/vehiculos-por-estado` | GET | Operador+ |
| `/api/reportes/incidencias-por-tipo` | GET | Operador+ |

---

## 9. DEUDA TÉCNICA DEL DOMINIO

| # | Ítem | Prioridad |
|---|---|---|
| 1 | Tests solo verifican `assertOk` — no validan estructura ni valores de los datos retornados | 🟡 Media |
| 2 | `resumen` ejecuta 7 consultas separadas — podría optimizarse a 1 consulta con múltiples subqueries | 🟢 Baja |

---

*Fin del SDD-REPORTES — PW3C-DIST-SDD-RPT v1.0*
