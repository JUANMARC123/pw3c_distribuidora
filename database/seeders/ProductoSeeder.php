<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run()
    {
        $productos = [
            ['nombre' => 'Paracetamol 500mg', 'precio_unitario' => 15.50, 'unidad_medida' => 'BLISTER'],
            ['nombre' => 'Ibuprofeno 400mg', 'precio_unitario' => 22.00, 'unidad_medida' => 'BLISTER'],
            ['nombre' => 'Amoxicilina 500mg', 'precio_unitario' => 35.00, 'unidad_medida' => 'CAJA'],
            ['nombre' => 'Omeprazol 20mg', 'precio_unitario' => 18.75, 'unidad_medida' => 'BLISTER'],
            ['nombre' => 'Loratadina 10mg', 'precio_unitario' => 12.00, 'unidad_medida' => 'BLISTER'],
            ['nombre' => 'Salbutamol Inhalador', 'precio_unitario' => 45.00, 'unidad_medida' => 'FRASCO'],
            ['nombre' => 'Diclofenaco 50mg', 'precio_unitario' => 8.50, 'unidad_medida' => 'BLISTER'],
            ['nombre' => 'Cetirizina 10mg', 'precio_unitario' => 14.00, 'unidad_medida' => 'BLISTER'],
            ['nombre' => 'Metformina 850mg', 'precio_unitario' => 20.00, 'unidad_medida' => 'CAJA'],
            ['nombre' => 'Enalapril 10mg', 'precio_unitario' => 16.00, 'unidad_medida' => 'BLISTER'],
            ['nombre' => 'Losartán 50mg', 'precio_unitario' => 19.50, 'unidad_medida' => 'BLISTER'],
            ['nombre' => 'Atorvastatina 20mg', 'precio_unitario' => 28.00, 'unidad_medida' => 'CAJA'],
            ['nombre' => 'Azitromicina 500mg', 'precio_unitario' => 42.00, 'unidad_medida' => 'CAJA'],
            ['nombre' => 'Ciprofloxacino 500mg', 'precio_unitario' => 25.00, 'unidad_medida' => 'BLISTER'],
            ['nombre' => 'Dexametasona 4mg', 'precio_unitario' => 10.00, 'unidad_medida' => 'BLISTER'],
            ['nombre' => 'Vitamina C 1000mg', 'precio_unitario' => 32.00, 'unidad_medida' => 'FRASCO'],
            ['nombre' => 'Suplemento Multivitamínico', 'precio_unitario' => 85.00, 'unidad_medida' => 'FRASCO'],
            ['nombre' => 'Jarabe Expectorante', 'precio_unitario' => 28.00, 'unidad_medida' => 'FRASCO'],
            ['nombre' => 'Alcohol en Gel 500ml', 'precio_unitario' => 22.00, 'unidad_medida' => 'FRASCO'],
            ['nombre' => 'Gasas Estériles 10x10', 'precio_unitario' => 6.50, 'unidad_medida' => 'CAJA'],
            ['nombre' => 'Vendas Elásticas 5cm', 'precio_unitario' => 5.00, 'unidad_medida' => 'CAJA'],
            ['nombre' => 'Antiinflamatorio en Gel', 'precio_unitario' => 38.00, 'unidad_medida' => 'TUBO'],
            ['nombre' => 'Colirio Lubricante', 'precio_unitario' => 25.00, 'unidad_medida' => 'AMPOLLA'],
            ['nombre' => 'Insulina NPH 100UI', 'precio_unitario' => 120.00, 'unidad_medida' => 'CAJA'],
            ['nombre' => 'Solución Salina 500ml', 'precio_unitario' => 15.00, 'unidad_medida' => 'FRASCO'],
            ['nombre' => 'Algodón Hidrófilo 100gr', 'precio_unitario' => 12.00, 'unidad_medida' => 'CAJA'],
            ['nombre' => 'Bisturí N°11 Estéril', 'precio_unitario' => 3.50, 'unidad_medida' => 'CAJA'],
            ['nombre' => 'Guantes Quirúrgicos Talla M', 'precio_unitario' => 45.00, 'unidad_medida' => 'CAJA'],
        ];

        foreach ($productos as $data) {
            Producto::create($data);
        }
    }
}
