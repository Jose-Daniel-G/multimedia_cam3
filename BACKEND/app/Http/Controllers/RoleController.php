<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:roles.index')->only('index');
        $this->middleware('permission:roles.edit')->only('update');
        $this->middleware('permission:roles.create')->only('store');
        $this->middleware('permission:roles.delete')->only('destroy');
    }

    public function index()
    {
        // $roles = Role::orderBy('created_at', 'ASC')->paginate(10);
        $roles = Role::with('permissions')
            ->orderBy('created_at', 'ASC')
            ->paginate(10); // Use paginate() for paginated results
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles|min:3',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        if (!empty($request->permission)) {
            foreach ($request->permission as $name) {
                $role->givePermissionTo($name);
            }
        }

        return response()->json([
            'message' => 'Rol creado exitosamente',
            'data' => $role
        ], 201);
    }

    public function show($id)
    {
        $role = Role::with('permissions')->find($id);

        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        return response()->json($role);
    }

    public function update(Request $request, $id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $role->name = $request->name;
        $role->save();

        if (!empty($request->permission)) {
            $role->syncPermissions($request->permission);
        } else {
            $role->syncPermissions([]);
        }

        return response()->json([
            'message' => 'Rol actualizado exitosamente',
            'data' => $role
        ]);
    }

    public function destroy($id)
    {
        $role = Role::find($id);

        if (!$role) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        $role->delete();

        return response()->json(['message' => 'Rol eliminado exitosamente']);
    }

    public function permissions()
    {
        $permissions = Permission::orderBy('name', 'ASC')->get();
        return response()->json($permissions);
    }
}
