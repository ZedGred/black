<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::paginate(15);
        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'guard_name' => 'nullable|string|max:255',
        ]);

        $validated['guard_name'] = $validated['guard_name'] ?? 'api';

        $permission = Permission::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Permission created',
            'data' => $permission
        ], 201);
    }

    public function show(Permission $permission)
    {
        return response()->json([
            'success' => true,
            'data' => $permission
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:permissions,name,' . $permission->id,
            'guard_name' => 'nullable|string|max:255',
        ]);

        $permission->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Permission updated',
            'data' => $permission
        ]);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json([
            'success' => true,
            'message' => 'Permission deleted'
        ]);
    }
}
