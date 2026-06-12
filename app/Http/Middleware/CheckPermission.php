<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    private array $actionMap = [
        'index' => 'listar',
        'show' => 'listar',
        'store' => 'crear',
        'update' => 'editar',
        'destroy' => 'eliminar',
    ];

    public function handle(Request $request, Closure $next, string $module, string $action = null)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'No autenticado.'], 401);
        }

        if ($action === null) {
            $method = $request->route()->getActionMethod();
            $action = $this->actionMap[$method] ?? null;

            if (!$action) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo determinar la acción requerida.',
                ], 403);
            }
        }

        if (!$user->hasPermission($module, $action)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para realizar esta acción.',
            ], 403);
        }

        return $next($request);
    }
}
