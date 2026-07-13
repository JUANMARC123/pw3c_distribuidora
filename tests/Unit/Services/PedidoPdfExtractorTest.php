<?php

use App\Models\Producto;
use App\Services\Pedido\PedidoPdfExtractor;

beforeEach(function () {
    $this->extractor = new PedidoPdfExtractor();
});

it('normalizar convierte a minusculas y quita acentos', function () {
        // Preparar
        $method = new ReflectionMethod($this->extractor, 'normalizar');
        $method->setAccessible(true);

        // Actuar & Verificar
        $result = $method->invoke($this->extractor, 'Á é Í ó Ú');
        expect($result)->toMatch('/^[a-z ]+$/');
        expect($result)->toContain('e');
        expect($result)->toContain('o');

        $result = $method->invoke($this->extractor, 'Hello World');
        expect($result)->toEqual('hello world');

        expect($method->invoke($this->extractor, ''))->toEqual('');
        expect($method->invoke($this->extractor, null))->toEqual('');
    });

    it('esLineaNoProducto detecta encabezados', function () {
        // Preparar
        $method = new ReflectionMethod($this->extractor, 'esLineaNoProducto');
        $method->setAccessible(true);

        // Actuar & Verificar
        expect($method->invoke($this->extractor, 'FECHA: 01/01/2025'))->toBeTrue();
        expect($method->invoke($this->extractor, 'TOTAL BS 150.00'))->toBeTrue();
        expect($method->invoke($this->extractor, 'Cliente: Juan Perez'))->toBeTrue();
        expect($method->invoke($this->extractor, 'FORMA DE PAGO: contado'))->toBeTrue();
        expect($method->invoke($this->extractor, 'RECIBI CONFORME'))->toBeTrue();

        expect($method->invoke($this->extractor, 'Paracetamol 500mg 2 UD 5.00 10.00'))->toBeFalse();
        expect($method->invoke($this->extractor, 'Ibuprofeno 400mg x 20 tabletas'))->toBeFalse();
    });

    it('extraerCantidad detecta numero antes de unidad', function () {
        // Preparar
        $method = new ReflectionMethod($this->extractor, 'extraerCantidad');
        $method->setAccessible(true);

        // Actuar & Verificar
        expect($method->invoke($this->extractor, '2 UD Paracetamol'))->toEqual(2.0);
        expect($method->invoke($this->extractor, '3 CAJA Ibuprofeno'))->toEqual(3.0);
        expect($method->invoke($this->extractor, '1.5 pack Gasas'))->toEqual(1.5);
        expect($method->invoke($this->extractor, '5 und Vendas'))->toEqual(5.0);
        expect($method->invoke($this->extractor, 'Producto sin cantidad'))->toBeNull();
    });

    it('extraerMontos extrae valores decimales', function () {
        // Preparar
        $method = new ReflectionMethod($this->extractor, 'extraerMontos');
        $method->setAccessible(true);

        // Actuar & Verificar
        $result = $method->invoke($this->extractor, '2 UD Paracetamol 5.00 10.00');
        expect($result)->toContain(5.0);
        expect($result)->toContain(10.0);

        expect($method->invoke($this->extractor, 'Solo texto sin numeros'))->toBeEmpty();
    });

    it('deduplicar acumula cantidades del mismo producto', function () {
        // Preparar
        $method = new ReflectionMethod($this->extractor, 'deduplicar');
        $method->setAccessible(true);

        $rows = [
            ['id_producto' => 1, 'nombre' => 'Producto A', 'cantidad_solicitada' => 2, 'confianza' => 92, 'advertencias' => []],
            ['id_producto' => 1, 'nombre' => 'Producto A', 'cantidad_solicitada' => 3, 'confianza' => 92, 'advertencias' => []],
            ['id_producto' => 2, 'nombre' => 'Producto B', 'cantidad_solicitada' => 1, 'confianza' => 92, 'advertencias' => []],
        ];

        // Actuar
        $result = $method->invoke($this->extractor, $rows);

        // Verificar
        expect($result)->toHaveCount(2);
        expect($result[0]['cantidad_solicitada'])->toEqual(5.0);
        expect($result[1]['cantidad_solicitada'])->toEqual(1.0);
    });

    it('buscarProducto encuentra coincidencia exacta', function () {
        // Preparar
        Producto::factory()->create(['nombre' => 'Paracetamol 500mg']);
        $productos = Producto::all();

        $method = new ReflectionMethod($this->extractor, 'buscarProducto');
        $method->setAccessible(true);

        // Actuar
        $result = $method->invoke($this->extractor, 'Paracetamol 500mg 2 UD 5.00 10.00', $productos);

        // Verificar
        expect($result)->not->toBeNull();
        expect($result->nombre)->toEqual('Paracetamol 500mg');
    });

    it('buscarProducto retorna null sin coincidencia', function () {
        // Preparar
        Producto::factory()->create(['nombre' => 'Paracetamol 500mg']);
        $productos = Producto::all();

        $method = new ReflectionMethod($this->extractor, 'buscarProducto');
        $method->setAccessible(true);

        // Actuar
        $result = $method->invoke($this->extractor, 'Linea sin producto conocido 99.99', $productos);

        // Verificar
        expect($result)->toBeNull();
    });
