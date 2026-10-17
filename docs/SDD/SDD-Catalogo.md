# SDD-CATÁLOGO: Dominio Farmacias, Contactos y Productos

**ID:** `PW3C-DIST-SDD-CAT` | **Depende de:** SDD-Global

---

## 1. CONTROL DEL DOCUMENTO

| Versión | Fecha | Descripción |
|---|---|---|
| 1.0 | 2026-07-13 | Versión inicial |

## 2. DESCRIPCIÓN GENERAL

### 2.1 Propósito

Gestionar el catálogo maestro del sistema: farmacias (destinos de pedidos), sus contactos, y los productos farmacéuticos disponibles para la venta.

### 2.2 Actores

| Actor | Acción |
|---|---|
| Administrador | CRUD completo |
| Supervisor | CRUD sin eliminar |
| Operador | Solo lectura |

### 2.3 Alcance

| In Scope | Out of Scope |
|---|---|
| CRUD de farmacias con coordenadas | Mapa / geolocalización interactiva |
| Gestión de contactos por farmacia | Importación masiva desde Excel |
| CRUD de productos con unidad de medida | Sincronización con laboratorios |
| Validación de unicidad (email, placa) | |

---

## 3. ARQUITECTURA

Sin eventos ni servicios — CRUD directo vía Eloquent. Validación inline en controladores.

---

## 4. CONTRATOS

### 4.1 Base de Datos

#### `farmacias`

| Columna | Tipo | Restricciones |
|---|---|---|
| `id_farmacia` | bigint PK | |
| `nombre` | string | NOT NULL |
| `direccion` | text | NOT NULL |
| `telefono` | string | NOT NULL |
| `email` | string | UNIQUE, NOT NULL |
| `latitud` | decimal(10,7) | NOT NULL |
| `longitud` | decimal(10,7) | NOT NULL |
| `geocerca` | decimal(10,2) | DEFAULT 0 |

#### `contactos_farmacia`

| Columna | Tipo | FK |
|---|---|---|
| `id_contacto` | bigint PK | |
| `id_farmacia` | bigint | → `farmacias(id_farmacia)` CASCADE |
| `nombre_contacto` | string | |
| `cargo` | string | NULLABLE |
| `telefono` | string | |
| `email` | string | NULLABLE |

#### `productos`

| Columna | Tipo | Restricciones |
|---|---|---|
| `id_producto` | bigint PK | |
| `nombre` | string | NOT NULL |
| `precio_unitario` | decimal(10,2) | NOT NULL |
| `unidad_medida` | enum(`CAJA`,`BLISTER`,`TUBO`,`FRASCO`,`AMPOLLA`,`KG`) | DEFAULT `CAJA` |

### 4.2 API Endpoints

#### Farmacias (5 endpoints)

| Método | Ruta |
|---|---|
| GET | `/api/farmacias` |
| POST | `/api/farmacias` |
| GET | `/api/farmacias/{id}` |
| PUT | `/api/farmacias/{id}` |
| DELETE | `/api/farmacias/{id}` |

**Request POST/PUT:** `{ nombre, direccion, telefono, email, latitud, longitud, geocerca? }`

#### Contactos (5 endpoints, anidados)

| Método | Ruta |
|---|---|
| GET | `/api/farmacias/{id}/contactos` |
| POST | `/api/farmacias/{id}/contactos` |
| GET | `/api/farmacias/{id}/contactos/{id}` |
| PUT | `/api/farmacias/{id}/contactos/{id}` |
| DELETE | `/api/farmacias/{id}/contactos/{id}` |

**Request POST/PUT:** `{ nombre_contacto, cargo?, telefono, email? }`

#### Productos (5 endpoints)

| Método | Ruta |
|---|---|
| GET | `/api/productos` |
| POST | `/api/productos` |
| GET | `/api/productos/{id}` |
| PUT | `/api/productos/{id}` |
| DELETE | `/api/productos/{id}` |

**Request POST/PUT:** `{ nombre, precio_unitario, unidad_medida }`

---

## 5. REGLAS DE NEGOCIO

| # | Regla |
|---|---|
| 1 | Email de farmacia debe ser único |
| 2 | No se puede eliminar farmacia con pedidos asociados (409) |
| 3 | No se puede eliminar producto con detalles de pedido asociados (409) |
| 4 | Latitud debe ser decimal(10,7) — rango válido para Bolivia (-17 a -16) |
| 5 | Unidad de medida limitada a 6 valores: CAJA, BLISTER, TUBO, FRASCO, AMPOLLA, KG |

---

## 6. CRITERIOS DE ACEPTACIÓN

```gherkin
Feature: CRUD Farmacias
  Scenario: Crear farmacia válida
    When POST /api/farmacias con datos completos
    Then retorna 201 con la farmacia creada

  Scenario: Email duplicado
    Given una farmacia con email "x@y.com"
    When POST /api/farmacias con mismo email
    Then retorna 422

Feature: CRUD Productos
  Scenario: Crear producto con unidad válida
    When POST /api/productos con unidad_medida="CAJA"
    Then retorna 201

  Scenario: Unidad de medida inválida
    When POST /api/productos con unidad_medida="LITRO"
    Then retorna 422
```

---

## 7. ESTRATEGIA DE VALIDACIÓN

| Tipo | Archivo | Tests |
|---|---|---|
| Feature | `FarmaciaCRUDTest.php` | 7 |
| Feature | `ProductoCRUDTest.php` | 6 |
| Unit | `FarmaciaTest.php` (model) | 5 |
| Unit | `ContactoFarmaciaTest.php` (model) | 4 |
| Unit | `ProductoTest.php` (model) | 4 |
| **Total** | | **26** |

---

## 8. SEGURIDAD Y PERMISOS

| Endpoint | Método | Rol mínimo |
|---|---|---|
| `/api/farmacias` | GET | Operador+ |
| `/api/farmacias` | POST | Operador+ |
| `/api/farmacias/{id}` | PUT | Operador+ |
| `/api/farmacias/{id}` | DELETE | Administrador |
| `/api/farmacias/{id}/contactos/*` | * | Operador+ |
| `/api/productos` | GET | Operador+ |
| `/api/productos` | POST | Operador+ |
| `/api/productos/{id}` | PUT | Operador+ |
| `/api/productos/{id}` | DELETE | Administrador |

---

## 9. DEUDA TÉCNICA DEL DOMINIO

| # | Ítem | Prioridad |
|---|---|---|
| 1 | Sin tests de `geocerca` como radio de validación de coordenadas | 🟢 Baja |
| 2 | Los enum de `unidad_medida` están hardcodeados en validación y migración — si cambian, hay que actualizar en 2 lugares | 🟢 Baja |

---

*Fin del SDD-CATÁLOGO — PW3C-DIST-SDD-CAT v1.0*
