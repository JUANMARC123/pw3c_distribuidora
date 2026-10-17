# Módulo de Catálogos

## 1. Arquitectura Técnica (SDD)

### Archivos de Código Fuente
- `app/Http/Controllers/Farmacia/FarmaciaController.php` — CRUD farmacias
- `app/Http/Controllers/Farmacia/ContactoFarmaciaController.php` — CRUD contactos anidados
- `app/Http/Controllers/Producto/ProductoController.php` — CRUD productos
- `app/Models/Farmacia.php`
- `app/Models/ContactoFarmacia.php`
- `app/Models/Producto.php`

### Farmacias
- **Controlador:** `FarmaciaController`, `ContactoFarmaciaController`
- **Modelo:** `Farmacia`, `ContactoFarmacia`
- **Reglas:** email único nullable, coordenadas con precisión decimal(10,7), contactos en cascada al eliminar farmacia

### Productos
- **Controlador:** `ProductoController`
- **Modelo:** `Producto`
- **Reglas:** nombre único, precio_unitario obligatorio, unidad_medida enum (`CAJA`, `BLISTER`, `TUBO`, `FRASCO`, `AMPOLLA`, `KG`)

### Endpoints

| Método | Ruta | Controlador |
|--------|------|-------------|
| GET/POST/PUT/DELETE | `/api/farmacias[/{id}]` | `FarmaciaController` |
| GET/POST/PUT/DELETE | `/api/farmacias/{farmacia}/contactos[/{id}]` | `ContactoFarmaciaController` |
| GET/POST/PUT/DELETE | `/api/productos[/{id}]` | `ProductoController` |

---

## 2. Especificación de Comportamiento (BDD)

### Escenario C1: CRUD de farmacias con contactos
```
Dado un administrador autenticado
Cuando crea una farmacia con nombre, dirección, coordenadas y email
Entonces la farmacia se registra
Y puede gestionar contactos anidados bajo /api/farmacias/{id}/contactos
Y al eliminar la farmacia, sus contactos se eliminan en cascada
```
- **Test Feature:** `Catalogo/FarmaciaCRUDTest.php`
- **Test Unit:** `Catalogo/Models/FarmaciaTest.php`, `Catalogo/Models/ContactoFarmaciaTest.php`
- **Controlador:** `FarmaciaController`, `ContactoFarmaciaController`

### Escenario C2: CRUD de productos
```
Dado un administrador autenticado
Cuando crea un producto con nombre, precio_unitario y unidad_medida
Entonces el producto se registra
Y se valida que los campos requeridos estén presentes
```
- **Test Feature:** `Catalogo/ProductoCRUDTest.php`
- **Test Unit:** `Catalogo/Models/ProductoTest.php`
- **Controlador:** `ProductoController`

---

## 3. Trazabilidad de Tests

### Tests Feature
| Archivo de Test | Grupo Pest | Controlador |
|---|---|---|
| `tests/Feature/Catalogo/FarmaciaCRUDTest.php` | `farmacias, crud` | `FarmaciaController`, `ContactoFarmaciaController` |
| `tests/Feature/Catalogo/ProductoCRUDTest.php` | `productos, crud` | `ProductoController` |

### Tests Unit
| Archivo de Test | Namespace |
|---|---|
| `tests/Unit/Catalogo/Models/FarmaciaTest.php` | `Tests\Unit\Catalogo\Models` |
| `tests/Unit/Catalogo/Models/ContactoFarmaciaTest.php` | `Tests\Unit\Catalogo\Models` |
| `tests/Unit/Catalogo/Models/ProductoTest.php` | `Tests\Unit\Catalogo\Models` |
