<?php

namespace App\Http\Controllers;

use App\Models\Seguridad\EstadoUsuario;
use App\Models\Seguridad\Modulo;
use App\Models\Seguridad\Accion;
use App\Models\Seguridad\Rol;
use App\Models\Seguridad\TablaSistema;
use App\Models\Pedido\EstadoPedido;
use App\Models\Repartidor\EstadoRepartidor;
use App\Models\Repartidor\ExtensionCI;
use App\Models\Repartidor\Licencia;
use App\Models\Vehiculo\EstadoVehiculo;
use App\Models\Vehiculo\Marca;
use App\Models\Vehiculo\Modelo;
use App\Models\Vehiculo\Capacidad;
use App\Models\Despacho\EstadoDespacho;
use App\Models\Evidencia\TipoIncidencia;
use App\Models\Evidencia\TipoEvidencia;
use App\Models\Farmacia\Cargo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CatalogoController extends ApiController
{
    private array $catalogoMap = [
        'estados-usuario' => ['model' => EstadoUsuario::class, 'field' => 'nombre_estado', 'table' => 'estados_usuario', 'id' => 'id_estado_usuario', 'max' => 50],
        'roles'           => ['model' => Rol::class, 'field' => 'nombre', 'table' => 'roles', 'id' => 'id_rol', 'max' => 50],
        'modulos'         => ['model' => Modulo::class, 'field' => 'nombre', 'table' => 'modulos', 'id' => 'id_modulo', 'max' => 50],
        'acciones'        => ['model' => Accion::class, 'field' => 'nombre', 'table' => 'acciones', 'id' => 'id_accion', 'max' => 50],
        'tablas-sistema'  => ['model' => TablaSistema::class, 'field' => 'nombre', 'table' => 'tablas_sistema', 'id' => 'id_tabla', 'max' => 100],
        'estados-pedido'  => ['model' => EstadoPedido::class, 'field' => 'nombre_estado', 'table' => 'estados_pedido', 'id' => 'id_estado_pedido', 'max' => 50],
        'estados-repartidor' => ['model' => EstadoRepartidor::class, 'field' => 'nombre_estado', 'table' => 'estados_repartidor', 'id' => 'id_estado_repartidor', 'max' => 50],
        'estados-vehiculo' => ['model' => EstadoVehiculo::class, 'field' => 'nombre_estado', 'table' => 'estados_vehiculo', 'id' => 'id_estado_vehiculo', 'max' => 50],
        'estados-despacho' => ['model' => EstadoDespacho::class, 'field' => 'nombre_estado', 'table' => 'estados_despacho', 'id' => 'id_estado_despacho', 'max' => 50],
        'extensiones-ci'  => ['model' => ExtensionCI::class, 'field' => 'nombre_extension', 'table' => 'extensiones_ci', 'id' => 'id_extension_ci', 'max' => 10],
        'licencias'       => ['model' => Licencia::class, 'field' => 'categoria', 'table' => 'licencias', 'id' => 'id_licencia', 'max' => 20],
        'marcas'          => ['model' => Marca::class, 'field' => 'nombre_marca', 'table' => 'marcas', 'id' => 'id_marca', 'max' => 50],
        'modelos'         => ['model' => Modelo::class, 'field' => 'nombre_modelo', 'table' => 'modelos', 'id' => 'id_modelo', 'max' => 100, 'has_fk' => true],
        'capacidades'     => ['model' => Capacidad::class, 'field' => 'capacidad_kg', 'table' => 'capacidades', 'id' => 'id_capacidad', 'numeric' => true],
        'tipos-incidencia' => ['model' => TipoIncidencia::class, 'field' => 'nombre_tipo', 'table' => 'tipos_incidencia', 'id' => 'id_tipo_incidencia', 'max' => 100],
        'tipos-evidencia' => ['model' => TipoEvidencia::class, 'field' => 'nombre_tipo', 'table' => 'tipos_evidencia', 'id' => 'id_tipo_evidencia', 'max' => 100],
        'cargos'          => ['model' => Cargo::class, 'field' => 'nombre_cargo', 'table' => 'cargos', 'id' => 'id_cargo', 'max' => 100],
    ];

    public function store(Request $request, $catalogo)
    {
        $config = $this->catalogoMap[$catalogo] ?? null;
        if (!$config) {
            return $this->errorResponse('Catálogo no válido', 404);
        }

        if ($catalogo === 'modelos') {
            $data = $request->validate([
                'id_marca' => 'required|integer|exists:marcas,id_marca',
                'nombre_modelo' => [
                    'required', 'string', 'max:100',
                    Rule::unique('modelos', 'nombre_modelo')->where(fn($q) => $q->where('id_marca', $request->id_marca)),
                ],
            ]);
        } elseif (!empty($config['numeric'])) {
            $data = $request->validate([
                $config['field'] => 'required|numeric|min:0|unique:' . $config['table'] . ',' . $config['field'],
            ]);
        } else {
            $data = $request->validate([
                $config['field'] => 'required|string|max:' . $config['max'] . '|unique:' . $config['table'] . ',' . $config['field'],
            ]);
        }

        $model = $config['model']::create($data);

        return $this->jsonResponse($model, 'Registro creado exitosamente.', 201);
    }

    public function update(Request $request, $catalogo, $id)
    {
        $config = $this->catalogoMap[$catalogo] ?? null;
        if (!$config) {
            return $this->errorResponse('Catálogo no válido', 404);
        }

        $model = $config['model']::findOrFail($id);

        if ($catalogo === 'modelos') {
            $data = $request->validate([
                'id_marca' => 'sometimes|integer|exists:marcas,id_marca',
                'nombre_modelo' => [
                    'sometimes', 'string', 'max:100',
                    Rule::unique('modelos', 'nombre_modelo')
                        ->where(fn($q) => $q->where('id_marca', $request->id_marca ?? $model->id_marca))
                        ->ignore($id, 'id_modelo'),
                ],
            ]);
        } elseif (!empty($config['numeric'])) {
            $data = $request->validate([
                $config['field'] => "required|numeric|min:0|unique:{$config['table']},{$config['field']},{$id},{$config['id']}",
            ]);
        } else {
            $data = $request->validate([
                $config['field'] => "required|string|max:{$config['max']}|unique:{$config['table']},{$config['field']},{$id},{$config['id']}",
            ]);
        }

        $model->update($data);

        return $this->jsonResponse($model, 'Registro actualizado exitosamente.');
    }

    public function destroy($catalogo, $id)
    {
        $config = $this->catalogoMap[$catalogo] ?? null;
        if (!$config) {
            return $this->errorResponse('Catálogo no válido', 404);
        }

        $model = $config['model']::findOrFail($id);
        $model->delete();

        return $this->jsonResponse(null, 'Registro eliminado exitosamente.');
    }

    public function modelosPorMarca($idMarca)
    {
        return $this->jsonResponse(Modelo::where('id_marca', $idMarca)->get());
    }

    public function estadosUsuario()
    {
        return $this->jsonResponse(EstadoUsuario::all());
    }

    public function roles()
    {
        return $this->jsonResponse(Rol::all());
    }

    public function modulos()
    {
        return $this->jsonResponse(Modulo::all());
    }

    public function acciones()
    {
        return $this->jsonResponse(Accion::all());
    }

    public function tablasSistema()
    {
        return $this->jsonResponse(TablaSistema::all());
    }

    public function estadosPedido()
    {
        return $this->jsonResponse(EstadoPedido::all());
    }

    public function estadosRepartidor()
    {
        return $this->jsonResponse(EstadoRepartidor::all());
    }

    public function estadosVehiculo()
    {
        return $this->jsonResponse(EstadoVehiculo::all());
    }

    public function estadosDespacho()
    {
        return $this->jsonResponse(EstadoDespacho::all());
    }

    public function extensionesCi()
    {
        return $this->jsonResponse(ExtensionCI::all());
    }

    public function licencias()
    {
        return $this->jsonResponse(Licencia::all());
    }

    public function marcas()
    {
        return $this->jsonResponse(Marca::all());
    }

    public function modelos()
    {
        return $this->jsonResponse(Modelo::with('marca')->get());
    }

    public function capacidades()
    {
        return $this->jsonResponse(Capacidad::all());
    }

    public function tiposIncidencia()
    {
        return $this->jsonResponse(TipoIncidencia::all());
    }

    public function tiposEvidencia()
    {
        return $this->jsonResponse(TipoEvidencia::all());
    }

    public function cargos()
    {
        return $this->jsonResponse(Cargo::all());
    }
}
