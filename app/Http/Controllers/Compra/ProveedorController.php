<?php

namespace App\Http\Controllers\Compra;

use App\Http\Controllers\ApiController;
use App\Models\Compra\Proveedor;
use App\Models\Compra\ContactoProveedor;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class ProveedorController extends ApiController
{
    public function index(Request $request)
    {
        $query = Proveedor::withCount('contactos');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre_proveedor', 'like', "%{$search}%")
                  ->orWhere('nit', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        return $this->paginatedResponse(
            $query->orderBy('nombre_proveedor')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre_proveedor' => 'required|string|max:150',
            'nit' => 'nullable|string|max:50|unique:proveedores,nit',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:180|unique:proveedores,email',
            'direccion' => 'nullable|string',
        ]);

        $proveedor = Proveedor::create($data);

        AuditService::log(auth()->id(), 'crear', 'proveedores', $proveedor->id_proveedor);

        return $this->jsonResponse($proveedor, 'Proveedor creado exitosamente.', 201);
    }

    public function show($id)
    {
        $proveedor = Proveedor::with('contactos')->findOrFail($id);
        return $this->jsonResponse($proveedor);
    }

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);

        $data = $request->validate([
            'nombre_proveedor' => 'sometimes|string|max:150',
            'nit' => 'nullable|string|max:50|unique:proveedores,nit,' . $id . ',id_proveedor',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:180|unique:proveedores,email,' . $id . ',id_proveedor',
            'direccion' => 'nullable|string',
        ]);

        $proveedor->update($data);

        AuditService::log(auth()->id(), 'editar', 'proveedores', $proveedor->id_proveedor);

        return $this->jsonResponse($proveedor, 'Proveedor actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);

        try {
            $proveedor->delete();

            AuditService::log(auth()->id(), 'eliminar', 'proveedores', $id);

            return $this->jsonResponse(null, 'Proveedor eliminado exitosamente.');
        } catch (QueryException $e) {
            if ($e->getCode() == 23000) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el proveedor porque tiene órdenes de compra registradas.'
                ], 409);
            }
            throw $e;
        }
    }

    public function contactos($id)
    {
        $proveedor = Proveedor::findOrFail($id);

        return $this->jsonResponse(
            ContactoProveedor::where('id_proveedor', $id)->orderBy('nombre_contacto')->get()
        );
    }

    public function storeContacto(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);

        $data = $request->validate([
            'nombre_contacto' => 'required|string|max:150',
            'cargo' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:180',
        ]);

        $data['id_proveedor'] = $proveedor->id_proveedor;

        $contacto = ContactoProveedor::create($data);

        return $this->jsonResponse($contacto, 'Contacto agregado exitosamente.', 201);
    }

    public function updateContacto(Request $request, $id, $contactoId)
    {
        $proveedor = Proveedor::findOrFail($id);
        $contacto = ContactoProveedor::where('id_proveedor', $id)->findOrFail($contactoId);

        $data = $request->validate([
            'nombre_contacto' => 'sometimes|string|max:150',
            'cargo' => 'nullable|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:180',
        ]);

        $contacto->update($data);

        return $this->jsonResponse($contacto, 'Contacto actualizado exitosamente.');
    }

    public function destroyContacto($id, $contactoId)
    {
        $proveedor = Proveedor::findOrFail($id);
        $contacto = ContactoProveedor::where('id_proveedor', $id)->findOrFail($contactoId);

        $contacto->delete();

        return $this->jsonResponse(null, 'Contacto eliminado exitosamente.');
    }
}
