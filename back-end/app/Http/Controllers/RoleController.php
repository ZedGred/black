<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // GET /api/roles
    public function index()
    {
        $roles = Role::paginate(10);
        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    // GET /api/roles/{id}
    public function show(Role $role)
    {
        return response()->json([
            'success' => true,
            'data' => $role
        ]);
    }

    // POST /api/roles
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'description' => 'nullable|string'
        ]);

        $role = Role::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Role created',
            'data' => $role
        ], 201);
    }

    // PUT /api/roles/{id}
    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:roles,name,' . $role->id,
            'description' => 'nullable|string'
        ]);

        $role->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Role updated',
            'data' => $role
        ]);
    }

    // DELETE /api/roles/{id}
    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted'
        ]);
    }
}
