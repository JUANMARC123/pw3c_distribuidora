<?php

namespace App\Console\Commands;

use App\Models\Farmacia\Farmacia;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportFarmaciasGeoJson extends Command
{
    protected $signature = 'farmacias:import-geojson
                            {--file= : Ruta del archivo GeoJSON (default: app/console/Commands/export.geojson)}
                            {--truncate : Truncar la tabla farmacias antes de importar}
                            {--dry-run : Solo mostrar qué se importaría sin escribir en BD}';

    protected $description = 'Importa farmacias desde un archivo GeoJSON a la base de datos';

    public function handle()
    {
        $filePath = $this->option('file') ?: base_path('app/console/Commands/export.geojson');

        if (!File::exists($filePath)) {
            $this->error("Archivo no encontrado: {$filePath}");
            return 1;
        }

        $json = json_decode(File::get($filePath), true);
        if (!$json || !isset($json['features'])) {
            $this->error('El archivo GeoJSON no tiene un formato válido (falta "features").');
            return 1;
        }

        if ($this->option('truncate')) {
            if ($this->option('dry-run')) {
                $this->warn('[DRY-RUN] Se truncaría la tabla farmacias.');
            } else {
                Farmacia::truncate();
                $this->info('Tabla farmacias truncada.');
            }
        }

        $total = count($json['features']);
        $importadas = 0;
        $saltadas = 0;
        $errores = 0;

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($json['features'] as $feature) {
            try {
                if ($this->importFeature($feature)) {
                    $importadas++;
                } else {
                    $saltadas++;
                }
            } catch (\Exception $e) {
                $errores++;
                $this->newLine();
                $this->warn("Error: " . $e->getMessage());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Resultado', 'Cantidad'],
            [
                ['Importadas', $importadas],
                ['Saltadas (sin amenity=pharmacy)', $saltadas],
                ['Errores', $errores],
                ['Total procesadas', $total],
            ]
        );

        return 0;
    }

    private function importFeature(array $feature): bool
    {
        $props = $feature['properties'] ?? [];
        $geometry = $feature['geometry'] ?? [];

        if (empty($props) || ($props['amenity'] ?? '') !== 'pharmacy') {
            return false;
        }

        $centro = $this->getCentroid($geometry);
        if (!$centro) {
            return false;
        }

        $nombre = $props['name'] ?? $props['name:es'] ?? 'Farmacia';
        if ($nombre === 'pharmacy' || !trim($nombre)) {
            $nombre = 'Farmacia';
        }

        $direccionParts = [];
        if (!empty($props['addr:street'])) {
            $direccionParts[] = $props['addr:street'];
            if (!empty($props['addr:housenumber'])) {
                $direccionParts[] = '#' . $props['addr:housenumber'];
            }
        }
        if (!empty($props['addr:city'])) {
            $direccionParts[] = ' - ' . $props['addr:city'];
        }
        $direccion = !empty($direccionParts) ? implode(' ', $direccionParts) : 'Sin dirección';

        $telefono = $props['phone'] ?? 'Sin teléfono';
        $telefono = preg_replace('/[^0-9+]/', '', $telefono);
        if (empty($telefono)) {
            $telefono = 'Sin teléfono';
        } elseif (strlen($telefono) > 20) {
            $telefono = substr($telefono, 0, 20);
        }

        $es24Horas = ($props['opening_hours'] ?? '') === '24/7';

        $data = [
            'nombre'      => substr($nombre, 0, 150),
            'direccion'   => $direccion,
            'telefono'    => $telefono,
            'email'       => null,
            'latitud'     => $centro['lat'],
            'longitud'    => $centro['lng'],
            'id_estado_farmacia' => 1,
            'es_24_horas' => $es24Horas,
        ];

        if ($this->option('dry-run')) {
            $this->line("[DRY-RUN] Se importaría: {$data['nombre']} ({$data['latitud']}, {$data['longitud']})");
            return true;
        }

        Farmacia::create($data);
        return true;
    }

    private function getCentroid(array $geometry): ?array
    {
        $type = $geometry['type'] ?? '';
        $coords = $geometry['coordinates'] ?? [];

        if (empty($coords)) {
            return null;
        }

        if ($type === 'Point') {
            return [
                'lat' => $coords[1],
                'lng' => $coords[0],
            ];
        }

        if ($type === 'Polygon' && !empty($coords[0])) {
            $ring = $coords[0];
            $count = count($ring) - 1;
            if ($count <= 0) return null;

            $sumLat = 0;
            $sumLng = 0;
            for ($i = 0; $i < $count; $i++) {
                $sumLng += $ring[$i][0];
                $sumLat += $ring[$i][1];
            }

            return [
                'lat' => $sumLat / $count,
                'lng' => $sumLng / $count,
            ];
        }

        if ($type === 'MultiPolygon' && !empty($coords[0]) && !empty($coords[0][0])) {
            $ring = $coords[0][0];
            $count = count($ring) - 1;
            if ($count <= 0) return null;

            $sumLat = 0;
            $sumLng = 0;
            for ($i = 0; $i < $count; $i++) {
                $sumLng += $ring[$i][0];
                $sumLat += $ring[$i][1];
            }

            return [
                'lat' => $sumLat / $count,
                'lng' => $sumLng / $count,
            ];
        }

        return null;
    }
}
