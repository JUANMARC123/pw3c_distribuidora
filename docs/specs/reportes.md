# Módulo de Reportes

## 1. Arquitectura Técnica (SDD)

### Archivos de Código Fuente
- `app/Http/Controllers/Reportes/ReporteController.php` — todos los endpoints de reportes

### Stack
- **Controlador:** `ReporteController`
- **Solo lectura:** Todos los endpoints son GET, no modifican datos
- **Consultas:** Eloquent con `groupBy` y `selectRaw`

### Endpoints

| Método | Ruta | Descripción | Permiso |
|--------|------|-------------|---------|
| GET | `/api/reportes/resumen` | Conteos globales del sistema | `Reportes,acceder` |
| GET | `/api/reportes/pedidos-por-estado` | Pedidos agrupados por estado | `Reportes,listar` |
| GET | `/api/reportes/despachos-por-estado` | Despachos agrupados por estado_despacho | `Reportes,listar` |
| GET | `/api/reportes/pedidos-por-dia` | Pedidos por día (últimos N, default 30) | `Reportes,listar` |
| GET | `/api/reportes/repartidores-por-estado` | Repartidores agrupados por estado | `Reportes,listar` |
| GET | `/api/reportes/vehiculos-por-estado` | Vehículos agrupados por estado | `Reportes,listar` |
| GET | `/api/reportes/incidencias-por-tipo` | Incidencias agrupadas por tipo_incidencia | `Reportes,listar` |

### Parámetros Comunes
| Parámetro | Tipo | Endpoints |
|-----------|------|-----------|
| `?dias=N` | integer (default 30) | `pedidos-por-dia` |
| `?fecha_desde=` | date | Todos |
| `?fecha_hasta=` | date | Todos |

---

## 2. Especificación de Comportamiento (BDD)

### Escenario R1: Resumen del sistema
```
Dado un usuario autenticado con permiso "Reportes,acceder"
Cuando consulta GET /api/reportes/resumen
Entonces obtiene un objeto con conteos de todas las entidades
```
- **Test Feature:** `Reporte/ReportesTest.php` → `it('obtiene resumen del sistema')`
- **Controlador:** `ReporteController`

### Escenario R2: Reportes agrupados por estado
```
Dado un usuario autenticado con permiso "Reportes,listar"
Cuando consulta GET /api/reportes/pedidos-por-estado
Entonces obtiene una lista de { estado, total }
Y lo mismo aplica para despachos, repartidores y vehículos
```
- **Test Feature:** `Reporte/ReportesTest.php`
- **Controlador:** `ReporteController`

### Escenario R3: Reporte de incidencias por tipo
```
Dado un usuario autenticado
Cuando consulta GET /api/reportes/incidencias-por-tipo
Entonces obtiene una lista de { tipo_incidencia, total }
```
- **Test Feature:** `Reporte/ReportesTest.php` → `it('obtiene incidencias por tipo')`
- **Controlador:** `ReporteController`

---

## 3. Trazabilidad de Tests

### Tests Feature
| Archivo de Test | Grupo Pest | Controlador |
|---|---|---|
| `tests/Feature/Reporte/ReportesTest.php` | `reportes` | `ReporteController` |

### Tests Unit
No hay tests unitarios específicos para el módulo de reportes (los reportes son consultas GET de solo lectura sin lógica de dominio compleja).
