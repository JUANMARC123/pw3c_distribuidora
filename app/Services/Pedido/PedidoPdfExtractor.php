<?php

namespace App\Services\Pedido;

use App\Models\Producto;
use Smalot\PdfParser\Parser;

class PedidoPdfExtractor
{
    public function extraer(string $path): array
    {
        if (!class_exists(Parser::class)) {
            throw new \RuntimeException('La dependencia smalot/pdfparser no esta disponible en el autoload. Ejecute composer dump-autoload y reinicie Laravel.');
        }

        $parser = new Parser();
        $text = $parser->parseFile($path)->getText();
        $lines = preg_split('/\R+/', $text) ?: [];
        $productos = Producto::where('estado', true)->get();
        $rows = [];
        $buffer = '';

        foreach ($lines as $line) {
            $line = trim(preg_replace('/\s+/', ' ', $line));
            if ($line === '' || $this->esLineaNoProducto($line)) {
                continue;
            }

            $candidate = trim($buffer . ' ' . $line);
            $parsed = $this->parsearLineaProducto($candidate, $productos);

            if (!$parsed) {
                $buffer = mb_strlen($candidate) > 220 ? '' : $candidate;
                continue;
            }

            $rows[] = $parsed;
            $buffer = '';
        }

        return $this->deduplicar($rows);
    }

    private function parsearLineaProducto(string $line, $productos): ?array
    {
        $match = $this->buscarProducto($line, $productos);
        $advertencias = [];
        $cantidad = $this->extraerCantidad($line);
        $montos = $this->extraerMontos($line);
        $precioUnitario = count($montos) >= 2 ? $montos[count($montos) - 2] : null;
        $total = count($montos) >= 1 ? $montos[count($montos) - 1] : null;
        $nombreDetectado = $match?->nombre ?? $this->extraerNombreProducto($line);

        if (!$nombreDetectado) {
            return null;
        }

        if ($cantidad === null) {
            $cantidad = 1;
            $advertencias[] = 'No se detecto cantidad; se uso cantidad 1.';
        }

        if ($cantidad > 0 && $total !== null) {
            $precioCalculado = round($total / $cantidad, 2);
            $diffDetectado = $precioUnitario !== null ? abs(($cantidad * $precioUnitario) - $total) : INF;
            $diffCalculado = abs(($cantidad * $precioCalculado) - $total);
            if ($precioUnitario === null || $diffDetectado > max(0.25, $diffCalculado + 0.1)) {
                $precioUnitario = $precioCalculado;
            }
        }

        if ($precioUnitario === null) {
            $precioUnitario = $match ? (float) $match->precio_unitario : 0;
            $advertencias[] = $match
                ? 'No se detecto precio unitario; se uso precio de catalogo.'
                : 'No se detecto precio unitario.';
        }

        if (!$match) {
            $advertencias[] = 'Producto detectado sin coincidencia en catalogo; puede crearse al guardar.';
        }

        if ($total !== null && abs(($cantidad * $precioUnitario) - $total) > 0.15) {
            $advertencias[] = 'El total detectado no coincide exactamente con cantidad por precio unitario.';
        }

        return [
            'id_producto' => $match?->id_producto,
            'nombre' => $nombreDetectado,
            'cantidad_solicitada' => max(0.01, (float) $cantidad),
            'precio_unitario' => max(0, (float) $precioUnitario),
            'confianza' => $match ? (count($advertencias) ? 75 : 92) : 68,
            'advertencias' => $advertencias,
            'linea_origen' => $line,
        ];
    }

    private function extraerNombreProducto(string $line): ?string
    {
        $cantidadPos = null;
        if (preg_match('/(?:^|\s)(\d+(?:[.,]\d+)?)\s*[-–—]?\s*(?:ud|und|unidad|unid|blt|cja|caja|pack)\b/i', $line, $match, PREG_OFFSET_CAPTURE)) {
            $cantidadPos = $match[0][1];
        }

        $segment = $cantidadPos !== null ? substr($line, 0, $cantidadPos) : $line;
        $segment = preg_replace('/^\d{1,2}\s+/', '', $segment);
        $segment = preg_replace('/[/\(]?\s*cod\.?\s*\d+.*/i', '', $segment);
        $segment = preg_replace('/\b(?:COFAR|BAGO|LAFAR|IFARBO|ALFA)\s*S\.?A\.?.*/i', '', $segment);
        $segment = preg_replace('/\s+\d+[.,]\d{1,2}\s+\d+[.,]\d{1,2}.*$/', '', $segment);
        $segment = trim(preg_replace('/\s+/', ' ', (string) $segment), " \t\n\r\0\x0B|;:,/");

        return $segment !== '' ? $segment : null;
    }

    private function buscarProducto(string $line, $productos): ?Producto
    {
        $lineNorm = $this->normalizar($line);
        $best = null;
        $bestScore = 0;

        foreach ($productos as $producto) {
            $nombreNorm = $this->normalizar($producto->nombre);
            if ($nombreNorm !== '' && str_contains($lineNorm, $nombreNorm)) {
                return $producto;
            }

            similar_text($nombreNorm, $lineNorm, $score);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $producto;
            }
        }

        return $bestScore >= 55 ? $best : null;
    }

    private function extraerCantidad(string $line): ?float
    {
        if (preg_match('/(?:^|\s)(\d+(?:[.,]\d+)?)\s*[-–—]?\s*(?:ud|und|unidad|unid|blt|cja|caja|pack)\b/i', $line, $match)) {
            return (float) str_replace(',', '.', $match[1]);
        }

        if (preg_match('/\bcantidad\s*:?\s*(\d+(?:[.,]\d+)?)/i', $line, $match)) {
            return (float) str_replace(',', '.', $match[1]);
        }

        return null;
    }

    private function extraerMontos(string $line): array
    {
        preg_match_all('/(?<![A-Za-z])\d+[.,]\d{1,2}(?![A-Za-z])/', $line, $matches);

        return collect($matches[0] ?? [])
            ->map(fn ($value) => (float) str_replace(',', '.', $value))
            ->filter(fn ($value) => $value > 0)
            ->values()
            ->all();
    }

    private function esLineaNoProducto(string $line): bool
    {
        return (bool) preg_match('/^(venta de productos|fecha|sucursal|cliente|dir\.|telf\.|cod\. cliente|moneda|n[°o]|descripcion|serie|cantidad|p\/u|total|forma de pago|glosa|almacen|contabilidad|recibi conforme|total bs)/i', $line);
    }

    private function deduplicar(array $rows): array
    {
        $merged = [];

        foreach ($rows as $row) {
            $key = $row['id_producto'] ? (string) $row['id_producto'] : $this->normalizar($row['nombre']);
            if (!isset($merged[$key])) {
                $merged[$key] = $row;
                continue;
            }

            $merged[$key]['cantidad_solicitada'] += $row['cantidad_solicitada'];
            $merged[$key]['advertencias'] = array_values(array_unique(array_merge(
                $merged[$key]['advertencias'],
                ['Producto detectado en mas de una linea; cantidades acumuladas.'],
                $row['advertencias']
            )));
            $merged[$key]['confianza'] = min($merged[$key]['confianza'], 75);
        }

        return array_values($merged);
    }

    private function normalizar(?string $value): string
    {
        $value = strtolower((string) $value);
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $value));
    }
}
