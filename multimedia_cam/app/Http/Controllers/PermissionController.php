<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller 
{
    public function __construct()
    {
        $this->middleware('permission:permissions.index')->only('index');
        $this->middleware('permission:permissions.edit')->only('edit');
        $this->middleware('permission:permissions.create')->only('create');
        $this->middleware('permission:permissions.delete')->only('destroy');
    }

    public function index()
    {
        $permissions = Permission::orderBy('created_at', 'DESC')->paginate(10);
        return response()->json([
            'status' => true,
            'data' => $permissions
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:permissions|min:3'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $permission = Permission::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Permiso creado exitosamente',
            'data' => $permission
        ]);
    }

    public function show(Permission $permission)
    {
        return response()->json([
            'status' => true,
            'data' => $permission
        ]);
    }

    public function edit($id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json([
                'status' => false,
                'message' => 'Permiso no encontrado'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $permission
        ]);
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::find($id);

        if (!$permission) {
            return response()->json([
                'status' => false,
                'message' => 'Permiso no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|unique:permissions,name,' . $id
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $permission->update([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Permiso actualizado exitosamente',
            'data' => $permission
        ]);
    }

    public function destroy(Request $request)
    {
        $permission = Permission::find($request->id);

        if (!$permission) {
            return response()->json([
                'status' => false,
                'message' => 'Permiso no encontrado'
            ], 404);
        }

        $permission->delete();

        return response()->json([
            'status' => true,
            'message' => 'Permiso eliminado exitosamente'
        ]);
    }
}
