# Introduction



<aside>
    <strong>Base URL</strong>: <code>http://127.0.0.1:8000</code>
</aside>

    This documentation aims to provide all the information you need to work with our API.

    <aside>As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
    You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).</aside>

    ---

    ## Testing

    El proyecto utiliza **Pest PHP** para testing con **88 tests** organizados en **15 archivos**.

    ### Ejecutar tests

    ```bash
    # Todos los tests
    ./vendor/bin/pest

    # Solo tests de feature (recomendado)
    ./vendor/bin/pest --testsuite=Feature

    # Un archivo específico
    ./vendor/bin/pest tests/Feature/Pedido/PedidoCRUDTest.php

    # Tests de un módulo específico
    ./vendor/bin/pest --group=pedido
    ```

    ### Estructura

    | Módulo | Archivo | Tests |
    |---|---|---|
    | Auth | `tests/Feature/Auth/LoginTest.php` | login, register, logout, usuario autenticado |
    | Auth | `tests/Feature/Auth/UserTest.php` | usuario actual, permisos, autenticación |
    | Caja | `tests/Feature/Caja/CajaDiariaTest.php` | abrir, ingreso, gasto, cerrar, consulta, resumen |
    | Despacho | `tests/Feature/Despacho/CambiarEstadoDespachoTest.php` | cambios de estado, validaciones, 404 |
    | Despacho | `tests/Feature/Despacho/CicloCompletoDespachoTest.php` | flujo completo, incidencias, devolución |
    | Despacho | `tests/Feature/Despacho/IncidenciaEvidenciaTest.php` | incidencias y evidencias |
    | Farmacia | `tests/Feature/Farmacia/FarmaciaCRUDTest.php` | CRUD + contactos |
    | Hoja Ruta | `tests/Feature/HojaRuta/HojaRutaTest.php` | CRUD, iniciar ruta, pedidos pendientes, rutas activas |
    | Pedido | `tests/Feature/Pedido/PedidoCRUDTest.php` | CRUD, detalles, cambios de estado |
    | Producto | `tests/Feature/Producto/ProductoCRUDTest.php` | CRUD |
    | Reporte | `tests/Feature/Reporte/ReportesTest.php` | todos los reportes (7 endpoints) |
    | Repartidor | `tests/Feature/Repartidor/RepartidorCRUDTest.php` | CRUD, cambio estado, asignación vehículo |
    | Usuario | `tests/Feature/Usuario/UsuarioCRUDTest.php` | CRUD, bloquear |
    | Vehículo | `tests/Feature/Vehiculo/VehiculoCRUDTest.php` | CRUD, cambio estado, placa única |

    ### Configuración

    - **Base de datos**: SQLite en memoria (`:memory:`) — cada test comienza con un esquema limpio
    - **Traits**: `RefreshDatabase` — ejecuta migraciones y semillas, envuelto en transacción
    - **Autenticación**: helper global `actingAsAdmin()` crea un admin autenticado antes de cada test
    - **Datos de prueba**: 14 model factories generan datos realistas
    - **Aislamiento**: cada test es independiente — no persiste nada entre tests

    ### Agregar un nuevo test

    ```php
    <?php

    use App\Models\MiModelo;

    uses()->group('mi-modulo');

    beforeEach(fn () => actingAsAdmin());

    it('lista recursos', function () {
        MiModelo::factory()->count(3)->create();

        $response = $this->getJson('/api/mi-recurso');

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(3);
    });
    ```

