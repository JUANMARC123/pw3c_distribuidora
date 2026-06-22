<?php

namespace Database\Seeders;

use App\Models\Compra\Proveedor;
use App\Models\Compra\ContactoProveedor;
use App\Models\Compra\OrdenCompra;
use App\Models\Compra\DetalleCompra;
use App\Models\Medicamento\Producto;
use App\Models\Medicamento\Lote;
use App\Models\Inventario\Almacen;
use App\Models\Inventario\UbicacionAlmacen;
use App\Models\Inventario\Inventario;
use App\Models\Inventario\MovimientoInventario;
use App\Models\Devolucion\Devolucion;
use App\Models\Devolucion\DetalleDevolucion;
use App\Models\Promocion\Promocion;
use App\Models\Promocion\ProductoPromocion;
use App\Models\Venta\Venta;
use App\Models\Venta\DetalleVenta;
use App\Models\Venta\Pago;
use App\Models\Farmacia\Farmacia;
use App\Models\Seguridad\Usuario;
use App\Models\Pedido\Pedido;
use Illuminate\Database\Seeder;

class DemoDataNewSeeder extends Seeder
{
    public function run()
    {
        $farmacias = Farmacia::all();
        $usuarios = Usuario::all();
        $pedidos = Pedido::all();

        if ($farmacias->isEmpty() || $usuarios->isEmpty()) {
            $this->command->warn('Faltan datos base (farmacias/usuarios). Ejecuta primero DemoDataSeeder.');
            return;
        }

        // ============================================================
        // 1. PROVEEDORES (10)
        // ============================================================
        $proveedoresData = [
            ['nombre_proveedor' => 'Distribuidora Farmacéutica Boliviana S.A.', 'nit' => '1024567021', 'telefono' => '22456789', 'email' => 'ventas@dfb.com.bo', 'direccion' => 'Av. Montes 123, La Paz'],
            ['nombre_proveedor' => 'Laboratorios INTI S.R.L.', 'nit' => '1045678012', 'telefono' => '22567890', 'email' => 'ventas@inti.com.bo', 'direccion' => 'Calle Potosí 456, La Paz'],
            ['nombre_proveedor' => 'Farmacorp S.A.', 'nit' => '1067890123', 'telefono' => '22678901', 'email' => 'compras@farmacorp.com', 'direccion' => 'Av. Ballivián 789, Santa Cruz'],
            ['nombre_proveedor' => 'Disprofarma Ltda.', 'nit' => '1089012345', 'telefono' => '22789012', 'email' => 'pedidos@disprofarma.com', 'direccion' => 'Calle Colombia 321, Cochabamba'],
            ['nombre_proveedor' => 'Química Suiza S.R.L.', 'nit' => '1012345678', 'telefono' => '22890123', 'email' => 'ventas@quimicasuiza.com', 'direccion' => 'Av. 6 de Agosto 654, La Paz'],
            ['nombre_proveedor' => 'Medicamentos del Sur E.I.R.L.', 'nit' => '1034567890', 'telefono' => '22901234', 'email' => 'compras@medsur.com', 'direccion' => 'Calle Lanza 987, Tarija'],
            ['nombre_proveedor' => 'Prosalud Distribuciones', 'nit' => '1056789012', 'telefono' => '23012345', 'email' => 'ventas@prosalud.com', 'direccion' => 'Av. Blanco Galindo 147, Cochabamba'],
            ['nombre_proveedor' => 'Droguería Central S.A.', 'nit' => '1078901234', 'telefono' => '23123456', 'email' => 'pedidos@drogueriacentral.com', 'direccion' => 'Calle Comercio 258, Santa Cruz'],
            ['nombre_proveedor' => 'Fármacos del Altiplano S.R.L.', 'nit' => '1090123456', 'telefono' => '23234567', 'email' => 'ventas@altiplano.com', 'direccion' => 'Av. Manco Kapac 369, El Alto'],
            ['nombre_proveedor' => 'Biolife Boliviana S.R.L.', 'nit' => '1001234567', 'telefono' => '23345678', 'email' => 'compras@biolife.com.bo', 'direccion' => 'Calle Chuquisaca 741, La Paz'],
        ];
        foreach ($proveedoresData as $data) {
            Proveedor::firstOrCreate(['nit' => $data['nit']], $data);
        }
        $proveedores = Proveedor::all();
        $this->command->info('10 proveedores creados.');

        // ============================================================
        // 2. CONTACTOS PROVEEDOR (10+)
        // ============================================================
        $contactosProv = [
            ['id_proveedor' => 1, 'nombre_contacto' => 'Carlos Mendoza', 'cargo' => 'Gerente de Ventas', 'telefono' => '71234567', 'email' => 'cmendoza@dfb.com.bo'],
            ['id_proveedor' => 1, 'nombre_contacto' => 'Ana Patricia López', 'cargo' => 'Ejecutiva de Cuentas', 'telefono' => '71234568', 'email' => 'aplopez@dfb.com.bo'],
            ['id_proveedor' => 2, 'nombre_contacto' => 'Roberto Quispe', 'cargo' => 'Jefe Comercial', 'telefono' => '71234569', 'email' => 'rquispe@inti.com.bo'],
            ['id_proveedor' => 3, 'nombre_contacto' => 'María Soliz', 'cargo' => 'Administradora', 'telefono' => '71234570', 'email' => 'msoliz@farmacorp.com'],
            ['id_proveedor' => 3, 'nombre_contacto' => 'Jorge Camacho', 'cargo' => 'Vendedor', 'telefono' => '71234571', 'email' => 'jcamacho@farmacorp.com'],
            ['id_proveedor' => 4, 'nombre_contacto' => 'Lucía Fernández', 'cargo' => 'Gerente General', 'telefono' => '71234572', 'email' => 'lfernandez@disprofarma.com'],
            ['id_proveedor' => 5, 'nombre_contacto' => 'Pedro Vargas', 'cargo' => 'Representante', 'telefono' => '71234573', 'email' => 'pvargas@quimicasuiza.com'],
            ['id_proveedor' => 6, 'nombre_contacto' => 'Sonia Rojas', 'cargo' => 'Coordinadora', 'telefono' => '71234574', 'email' => 'srojas@medsur.com'],
            ['id_proveedor' => 7, 'nombre_contacto' => 'Diego Zambrana', 'cargo' => 'Ejecutivo', 'telefono' => '71234575', 'email' => 'dzambrana@prosalud.com'],
            ['id_proveedor' => 8, 'nombre_contacto' => 'Carmen Vega', 'cargo' => 'Jefa de Ventas', 'telefono' => '71234576', 'email' => 'cvega@drogueriacentral.com'],
            ['id_proveedor' => 9, 'nombre_contacto' => 'Felipe Choque', 'cargo' => 'Encargado', 'telefono' => '71234577', 'email' => 'fchoque@altiplano.com'],
            ['id_proveedor' => 10, 'nombre_contacto' => 'Gabriela Paredes', 'cargo' => 'Asesora', 'telefono' => '71234578', 'email' => 'gparedes@biolife.com.bo'],
        ];
        foreach ($contactosProv as $data) {
            ContactoProveedor::create($data);
        }
        $this->command->info('12 contactos de proveedor creados.');

        // ============================================================
        // 3. PRODUCTOS (10+)
        // ============================================================
        $productosData = [
            ['codigo_producto' => 'PROD-001', 'nombre_producto' => 'Paracetamol 500mg', 'id_categoria' => 1, 'id_laboratorio' => 1, 'id_presentacion' => 1, 'id_unidad_medida' => 1, 'concentracion' => '500mg', 'precio_unitario' => 5.50, 'requiere_receta' => false, 'activo' => true],
            ['codigo_producto' => 'PROD-002', 'nombre_producto' => 'Ibuprofeno 400mg', 'id_categoria' => 3, 'id_laboratorio' => 2, 'id_presentacion' => 2, 'id_unidad_medida' => 1, 'concentracion' => '400mg', 'precio_unitario' => 8.00, 'requiere_receta' => false, 'activo' => true],
            ['codigo_producto' => 'PROD-003', 'nombre_producto' => 'Amoxicilina 500mg', 'id_categoria' => 2, 'id_laboratorio' => 3, 'id_presentacion' => 3, 'id_unidad_medida' => 2, 'concentracion' => '500mg', 'precio_unitario' => 15.00, 'requiere_receta' => true, 'activo' => true],
            ['codigo_producto' => 'PROD-004', 'nombre_producto' => 'Loratadina 10mg', 'id_categoria' => 4, 'id_laboratorio' => 1, 'id_presentacion' => 1, 'id_unidad_medida' => 1, 'concentracion' => '10mg', 'precio_unitario' => 12.00, 'requiere_receta' => false, 'activo' => true],
            ['codigo_producto' => 'PROD-005', 'nombre_producto' => 'Omeprazol 20mg', 'id_categoria' => 6, 'id_laboratorio' => 4, 'id_presentacion' => 2, 'id_unidad_medida' => 2, 'concentracion' => '20mg', 'precio_unitario' => 18.00, 'requiere_receta' => false, 'activo' => true],
            ['codigo_producto' => 'PROD-006', 'nombre_producto' => 'Enalapril 10mg', 'id_categoria' => 5, 'id_laboratorio' => 5, 'id_presentacion' => 3, 'id_unidad_medida' => 1, 'concentracion' => '10mg', 'precio_unitario' => 14.00, 'requiere_receta' => true, 'activo' => true],
            ['codigo_producto' => 'PROD-007', 'nombre_producto' => 'Vitamina C 1000mg', 'id_categoria' => 7, 'id_laboratorio' => 2, 'id_presentacion' => 5, 'id_unidad_medida' => 3, 'concentracion' => '1000mg/5ml', 'precio_unitario' => 25.00, 'requiere_receta' => false, 'activo' => true],
            ['codigo_producto' => 'PROD-008', 'nombre_producto' => 'Salbutamol Inhalador', 'id_categoria' => 4, 'id_laboratorio' => 3, 'id_presentacion' => 6, 'id_unidad_medida' => 3, 'concentracion' => '100mcg/dosis', 'precio_unitario' => 45.00, 'requiere_receta' => true, 'activo' => true],
            ['codigo_producto' => 'PROD-009', 'nombre_producto' => 'Diclofenaco 75mg', 'id_categoria' => 3, 'id_laboratorio' => 4, 'id_presentacion' => 7, 'id_unidad_medida' => 6, 'concentracion' => '75mg/ml', 'precio_unitario' => 6.50, 'requiere_receta' => false, 'activo' => true],
            ['codigo_producto' => 'PROD-010', 'nombre_producto' => 'Metformina 850mg', 'id_categoria' => 5, 'id_laboratorio' => 5, 'id_presentacion' => 1, 'id_unidad_medida' => 1, 'concentracion' => '850mg', 'precio_unitario' => 10.00, 'requiere_receta' => true, 'activo' => true],
            ['codigo_producto' => 'PROD-011', 'nombre_producto' => 'Ibuprofeno Infantil 100mg/5ml', 'id_categoria' => 3, 'id_laboratorio' => 1, 'id_presentacion' => 5, 'id_unidad_medida' => 3, 'concentracion' => '100mg/5ml', 'precio_unitario' => 22.00, 'requiere_receta' => false, 'activo' => true],
            ['codigo_producto' => 'PROD-012', 'nombre_producto' => 'Complejo B Inyectable', 'id_categoria' => 7, 'id_laboratorio' => 2, 'id_presentacion' => 7, 'id_unidad_medida' => 6, 'concentracion' => '100mg/ml', 'precio_unitario' => 35.00, 'requiere_receta' => false, 'activo' => true],
        ];
        foreach ($productosData as $data) {
            Producto::firstOrCreate(['codigo_producto' => $data['codigo_producto']], $data);
        }
        $productos = Producto::all();
        $this->command->info(count($productosData) . ' productos creados.');

        // ============================================================
        // 4. LOTES (10+)
        // ============================================================
        $lotesData = [
            ['id_producto' => 1, 'codigo_lote' => 'LOT-PAR-001', 'fecha_fabricacion' => '2025-06-01', 'fecha_vencimiento' => '2027-06-01', 'precio_compra' => 3.50],
            ['id_producto' => 1, 'codigo_lote' => 'LOT-PAR-002', 'fecha_fabricacion' => '2025-12-01', 'fecha_vencimiento' => '2027-12-01', 'precio_compra' => 3.80],
            ['id_producto' => 2, 'codigo_lote' => 'LOT-IBU-001', 'fecha_fabricacion' => '2025-05-15', 'fecha_vencimiento' => '2027-05-15', 'precio_compra' => 5.00],
            ['id_producto' => 3, 'codigo_lote' => 'LOT-AMO-001', 'fecha_fabricacion' => '2025-04-10', 'fecha_vencimiento' => '2027-04-10', 'precio_compra' => 9.50],
            ['id_producto' => 3, 'codigo_lote' => 'LOT-AMO-002', 'fecha_fabricacion' => '2026-01-15', 'fecha_vencimiento' => '2028-01-15', 'precio_compra' => 10.00],
            ['id_producto' => 4, 'codigo_lote' => 'LOT-LOR-001', 'fecha_fabricacion' => '2025-07-20', 'fecha_vencimiento' => '2027-07-20', 'precio_compra' => 7.50],
            ['id_producto' => 5, 'codigo_lote' => 'LOT-OME-001', 'fecha_fabricacion' => '2025-03-01', 'fecha_vencimiento' => '2027-03-01', 'precio_compra' => 11.00],
            ['id_producto' => 6, 'codigo_lote' => 'LOT-ENA-001', 'fecha_fabricacion' => '2025-08-10', 'fecha_vencimiento' => '2027-08-10', 'precio_compra' => 8.50],
            ['id_producto' => 7, 'codigo_lote' => 'LOT-VITC-001', 'fecha_fabricacion' => '2026-02-01', 'fecha_vencimiento' => '2028-02-01', 'precio_compra' => 15.00],
            ['id_producto' => 8, 'codigo_lote' => 'LOT-SAL-001', 'fecha_fabricacion' => '2025-09-15', 'fecha_vencimiento' => '2027-09-15', 'precio_compra' => 28.00],
            ['id_producto' => 9, 'codigo_lote' => 'LOT-DIC-001', 'fecha_fabricacion' => '2025-10-01', 'fecha_vencimiento' => '2027-10-01', 'precio_compra' => 3.80],
            ['id_producto' => 10, 'codigo_lote' => 'LOT-MET-001', 'fecha_fabricacion' => '2025-11-20', 'fecha_vencimiento' => '2027-11-20', 'precio_compra' => 6.00],
        ];
        foreach ($lotesData as $data) {
            Lote::firstOrCreate(['codigo_lote' => $data['codigo_lote']], $data);
        }
        $lotes = Lote::all();
        $this->command->info(count($lotesData) . ' lotes creados.');

        // ============================================================
        // 5. ALMACENES (10+, at least one per farmacia)
        // ============================================================
        $almacenIdx = 0;
        foreach ($farmacias as $farmacia) {
            Almacen::firstOrCreate(
                ['id_farmacia' => $farmacia->id_farmacia, 'nombre' => 'Almacén Principal'],
                ['id_farmacia' => $farmacia->id_farmacia, 'nombre' => 'Almacén Principal']
            );
            $almacenIdx++;
        }
        // Add extra almacenes for some farmacias
        Almacen::firstOrCreate(
            ['id_farmacia' => $farmacias[0]->id_farmacia, 'nombre' => 'Almacén Secundario'],
            ['id_farmacia' => $farmacias[0]->id_farmacia, 'nombre' => 'Almacén Secundario']
        );
        Almacen::firstOrCreate(
            ['id_farmacia' => $farmacias[1]->id_farmacia, 'nombre' => 'Almacén de Frío'],
            ['id_farmacia' => $farmacias[1]->id_farmacia, 'nombre' => 'Almacén de Frío']
        );
        $almacenes = Almacen::all();
        $this->command->info($almacenes->count() . ' almacenes creados.');

        // ============================================================
        // 6. UBICACIONES ALMACÉN (10+)
        // ============================================================
        $ubicaciones = [];
        $estantes = ['A', 'B', 'C', 'D'];
        foreach ($almacenes as $almacen) {
            for ($pasillo = 1; $pasillo <= 3; $pasillo++) {
                foreach ($estantes as $estante) {
                    if (count($ubicaciones) >= 15) break 3;
                    UbicacionAlmacen::firstOrCreate(
                        ['id_almacen' => $almacen->id_almacen, 'pasillo' => "P-{$pasillo}", 'estante' => $estante],
                        ['id_almacen' => $almacen->id_almacen, 'pasillo' => "P-{$pasillo}", 'estante' => $estante]
                    );
                    $ubicaciones[] = true;
                }
            }
        }
        $this->command->info(count($ubicaciones) . ' ubicaciones creadas.');

        // ============================================================
        // 7. INVENTARIO (10+)
        // ============================================================
        $ubicacionesList = UbicacionAlmacen::all();
        $inventarioCreado = 0;
        foreach ($lotes as $i => $lote) {
            if ($inventarioCreado >= 12) break;
            $ubicacion = $ubicacionesList[$i % $ubicacionesList->count()] ?? null;
            Inventario::firstOrCreate(
                ['id_producto' => $lote->id_producto, 'id_lote' => $lote->id_lote],
                [
                    'id_producto' => $lote->id_producto,
                    'id_lote' => $lote->id_lote,
                    'id_ubicacion' => $ubicacion?->id_ubicacion,
                    'stock_actual' => rand(20, 200),
                    'stock_minimo' => rand(5, 30),
                    'precio_venta' => $lote->producto->precio_unitario ?? 0,
                    'fecha_actualizacion' => now(),
                ]
            );
            $inventarioCreado++;
        }
        $this->command->info($inventarioCreado . ' registros de inventario creados.');
        $inventarios = Inventario::all();

        // ============================================================
        // 8. MOVIMIENTOS INVENTARIO (10+)
        // ============================================================
        $movCount = 0;
        foreach ($inventarios as $inv) {
            if ($movCount >= 12) break;
            MovimientoInventario::create([
                'id_inventario' => $inv->id_inventario,
                'id_tipo_movimiento' => 1,
                'id_usuario' => $usuarios->random()->id_usuario,
                'cantidad' => $inv->stock_actual,
                'stock_anterior' => 0,
                'stock_posterior' => $inv->stock_actual,
                'referencia' => 'INV-INICIAL-' . $inv->id_inventario,
                'observaciones' => 'Inventario inicial',
                'created_at' => now()->subDays(rand(1, 30)),
            ]);
            $movCount++;
        }
        $this->command->info($movCount . ' movimientos de inventario creados.');

        // ============================================================
        // 9. ÓRDENES DE COMPRA (10+)
        // ============================================================
        $ordenesCreadas = 0;
        for ($i = 1; $i <= 10; $i++) {
            $proveedor = $proveedores[($i - 1) % $proveedores->count()];
            $estadoOC = rand(1, 5);

            OrdenCompra::create([
                'codigo_orden' => 'OC-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'id_proveedor' => $proveedor->id_proveedor,
                'id_usuario' => $usuarios->random()->id_usuario,
                'id_estado_orden_compra' => $estadoOC,
                'fecha_orden' => now()->subDays(rand(1, 60)),
                'fecha_estimada_recibido' => now()->addDays(rand(5, 30)),
                'observaciones' => 'Orden #' . $i,
            ]);
            $ordenesCreadas++;
        }
        $ordenes = OrdenCompra::all();
        $this->command->info($ordenesCreadas . ' órdenes de compra creadas.');

        // ============================================================
        // 10. DETALLES COMPRA (10+)
        // ============================================================
        $detalleCreados = 0;
        foreach ($ordenes as $orden) {
            $numDetalles = rand(1, 3);
            $productosUsados = [];
            for ($d = 0; $d < $numDetalles; $d++) {
                $producto = $productos->random();
                if (in_array($producto->id_producto, $productosUsados)) continue;
                $productosUsados[] = $producto->id_producto;
                $cantidad = rand(10, 100);
                $precio = $producto->precio_unitario * 0.7;
                DetalleCompra::create([
                    'id_orden_compra' => $orden->id_orden_compra,
                    'id_producto' => $producto->id_producto,
                    'cantidad' => $cantidad,
                    'precio_unitario' => round($precio, 2),
                    'subtotal' => round($cantidad * $precio, 2),
                ]);
                $detalleCreados++;
                if ($detalleCreados >= 15) break;
            }
            if ($detalleCreados >= 15) break;
        }
        $this->command->info($detalleCreados . ' detalles de compra creados.');

        // ============================================================
        // 11. DEVOLUCIONES (10+)
        // ============================================================
        $devolucionesCreadas = 0;
        for ($i = 0; $i < 10 && $i < $pedidos->count(); $i++) {
            $pedido = $pedidos[$i];
            $estadoDev = rand(1, 4);
            $dev = Devolucion::create([
                'id_pedido' => $pedido->id_pedido,
                'id_usuario' => $usuarios->random()->id_usuario,
                'id_tipo_devolucion' => rand(1, 5),
                'id_estado_devolucion' => $estadoDev,
                'motivo' => 'Devolución generada automáticamente #' . ($i + 1),
                'fecha_devolucion' => now()->subDays(rand(1, 15)),
            ]);

            // Historial
            \App\Models\Devolucion\HistorialEstadoDevolucion::create([
                'id_devolucion' => $dev->id_devolucion,
                'id_estado_devolucion' => $estadoDev,
                'fecha_inicio' => $dev->fecha_devolucion,
            ]);

            $devolucionesCreadas++;
        }
        $devolucionesList = Devolucion::all();
        $this->command->info($devolucionesCreadas . ' devoluciones creadas.');

        // ============================================================
        // 12. DETALLES DEVOLUCIÓN (10+)
        // ============================================================
        $detDevCreados = 0;
        foreach ($devolucionesList as $dev) {
            $numDet = rand(1, 2);
            for ($d = 0; $d < $numDet; $d++) {
                $prod = $productos->random();
                $cant = rand(1, 5);
                $precio = $prod->precio_unitario;
                DetalleDevolucion::create([
                    'id_devolucion' => $dev->id_devolucion,
                    'id_producto' => $prod->id_producto,
                    'cantidad' => $cant,
                    'precio_unitario' => $precio,
                    'subtotal' => round($cant * $precio, 2),
                    'motivo_detalle' => 'Devolución de producto',
                ]);
                $detDevCreados++;
                if ($detDevCreados >= 12) break;
            }
            if ($detDevCreados >= 12) break;
        }
        $this->command->info($detDevCreados . ' detalles de devolución creados.');

        // ============================================================
        // 13. PROMOCIONES (10)
        // ============================================================
        $promocionesData = [
            ['nombre_promocion' => 'Descuento Primavera', 'descripcion' => '20% en todos los antihistamínicos', 'id_tipo_promocion' => 1, 'descuento' => 20, 'es_porcentual' => true, 'fecha_inicio' => now()->subDays(5), 'fecha_fin' => now()->addDays(25), 'activo' => true, 'id_usuario' => $usuarios->first()->id_usuario],
            ['nombre_promocion' => 'Oferta Vitamina C', 'descripcion' => 'Bs 5 de descuento en Vitamina C', 'id_tipo_promocion' => 2, 'descuento' => 5, 'es_porcentual' => false, 'fecha_inicio' => now()->subDays(10), 'fecha_fin' => now()->addDays(20), 'activo' => true, 'id_usuario' => $usuarios->first()->id_usuario],
            ['nombre_promocion' => '2x1 en Paracetamol', 'descripcion' => 'Lleve 2 pague 1', 'id_tipo_promocion' => 3, 'descuento' => 100, 'es_porcentual' => true, 'fecha_inicio' => now()->subDays(3), 'fecha_fin' => now()->addDays(27), 'activo' => true, 'id_usuario' => $usuarios->first()->id_usuario],
            ['nombre_promocion' => 'Bonificación Ibuprofeno', 'descripcion' => 'Compre 3 y lleve 1 gratis', 'id_tipo_promocion' => 4, 'descuento' => 25, 'es_porcentual' => true, 'fecha_inicio' => now()->subDays(7), 'fecha_fin' => now()->addDays(23), 'activo' => true, 'id_usuario' => $usuarios->first()->id_usuario],
            ['nombre_promocion' => 'Compra Mínima Antibióticos', 'descripcion' => '10% off en compras mayores a Bs 100', 'id_tipo_promocion' => 5, 'descuento' => 10, 'es_porcentual' => true, 'fecha_inicio' => now()->subDays(1), 'fecha_fin' => now()->addDays(29), 'activo' => true, 'id_usuario' => $usuarios->first()->id_usuario],
            ['nombre_promocion' => 'Descuento Analgésicos', 'descripcion' => '15% en toda la línea', 'id_tipo_promocion' => 1, 'descuento' => 15, 'es_porcentual' => true, 'fecha_inicio' => now()->subDays(15), 'fecha_fin' => now()->addDays(15), 'activo' => true, 'id_usuario' => $usuarios->first()->id_usuario],
            ['nombre_promocion' => 'Oferta Omeprazol', 'descripcion' => 'Descuento fijo de Bs 8', 'id_tipo_promocion' => 2, 'descuento' => 8, 'es_porcentual' => false, 'fecha_inicio' => now()->subDays(2), 'fecha_fin' => now()->addDays(28), 'activo' => true, 'id_usuario' => $usuarios->first()->id_usuario],
            ['nombre_promocion' => '2x1 Vitaminas', 'descripcion' => 'Toda la línea de vitaminas', 'id_tipo_promocion' => 3, 'descuento' => 100, 'es_porcentual' => true, 'fecha_inicio' => now()->subDays(20), 'fecha_fin' => now()->addDays(10), 'activo' => true, 'id_usuario' => $usuarios->first()->id_usuario],
            ['nombre_promocion' => 'Descuento Fin de Mes', 'descripcion' => '25% off en antiinflamatorios', 'id_tipo_promocion' => 1, 'descuento' => 25, 'es_porcentual' => true, 'fecha_inicio' => now()->addDays(5), 'fecha_fin' => now()->addDays(35), 'activo' => true, 'id_usuario' => $usuarios->first()->id_usuario],
            ['nombre_promocion' => 'Promoción Especial Cardio', 'descripcion' => 'Compra mínima Bs 150, 20% descuento', 'id_tipo_promocion' => 5, 'descuento' => 20, 'es_porcentual' => true, 'fecha_inicio' => now()->subDays(8), 'fecha_fin' => now()->addDays(22), 'activo' => true, 'id_usuario' => $usuarios->first()->id_usuario],
        ];
        $promoIds = [];
        foreach ($promocionesData as $data) {
            $promo = Promocion::create($data);
            $promoIds[] = $promo->id_promocion;
        }
        $this->command->info(count($promocionesData) . ' promociones creadas.');

        // ============================================================
        // 14. PRODUCTOS PROMOCIÓN (10+)
        // ============================================================
        $prodPromoCreados = 0;
        $promocionesList = Promocion::all();
        foreach ($promocionesList as $promo) {
            $numProds = rand(1, 3);
            $usados = [];
            for ($p = 0; $p < $numProds; $p++) {
                $prod = $productos->random();
                if (in_array($prod->id_producto, $usados)) continue;
                $usados[] = $prod->id_producto;
                ProductoPromocion::create([
                    'id_promocion' => $promo->id_promocion,
                    'id_producto' => $prod->id_producto,
                    'cantidad_minima' => 1,
                ]);
                $prodPromoCreados++;
                if ($prodPromoCreados >= 15) break;
            }
            if ($prodPromoCreados >= 15) break;
        }
        $this->command->info($prodPromoCreados . ' productos en promoción creados.');

        // ============================================================
        // 15. VENTAS (10+)
        // ============================================================
        $ventasCreadas = 0;
        for ($i = 0; $i < 10 && $i < $pedidos->count(); $i++) {
            $pedido = $pedidos[$i];
            $estadoVenta = rand(1, 4);

            $total = 0;
            $detallesVenta = [];

            // Pick 1-3 random lotes
            $lotesUsados = $lotes->random(min(3, $lotes->count()));
            if (!$lotesUsados instanceof \Illuminate\Support\Collection) {
                $lotesUsados = collect([$lotesUsados]);
            }
            foreach ($lotesUsados as $lote) {
                $cant = rand(1, 10);
                $precio = $lote->producto->precio_unitario ?? 10;
                $subtotal = round($cant * $precio, 2);
                $total += $subtotal;
                $detallesVenta[] = [
                    'id_lote' => $lote->id_lote,
                    'cantidad' => $cant,
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotal,
                ];
            }

            $venta = Venta::create([
                'id_pedido' => $pedido->id_pedido,
                'id_usuario' => $usuarios->random()->id_usuario,
                'id_estado_venta' => $estadoVenta,
                'fecha_venta' => now()->subDays(rand(1, 30)),
                'total' => round($total, 2),
            ]);

            foreach ($detallesVenta as $det) {
                $det['id_venta'] = $venta->id_venta;
                DetalleVenta::create($det);
            }

            $ventasCreadas++;
        }
        $this->command->info($ventasCreadas . ' ventas creadas.');
        $ventasList = Venta::all();

        // ============================================================
        // 16. PAGOS (10+)
        // ============================================================
        $pagosCreados = 0;
        foreach ($ventasList as $venta) {
            if ($venta->id_estado_venta == 4) continue;
            $numPagos = rand(1, 2);
            $montoRestante = $venta->total;
            for ($p = 0; $p < $numPagos; $p++) {
                $monto = $p == $numPagos - 1 ? round($montoRestante, 2) : round($venta->total * rand(30, 70) / 100, 2);
                Pago::create([
                    'id_venta' => $venta->id_venta,
                    'id_metodo_pago' => rand(1, 5),
                    'monto' => $monto,
                    'fecha_pago' => $venta->fecha_venta->copy()->addHours(rand(1, 48)),
                    'referencia' => rand(0, 1) ? 'REF-PAGO-' . $venta->id_venta . '-' . ($p + 1) : null,
                ]);
                $montoRestante -= $monto;
                $pagosCreados++;
                if ($pagosCreados >= 12) break;
            }
            if ($pagosCreados >= 12) break;
        }
        $this->command->info($pagosCreados . ' pagos creados.');
    }
}
