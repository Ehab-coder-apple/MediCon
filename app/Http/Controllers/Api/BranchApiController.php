<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BranchApiController extends Controller
{
    /**
     * List branches for the logged-in tenant user.
     * GET /api/branches
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();

        if (!$user || !$user->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'User does not belong to a tenant.',
            ], 403);
        }

        $branches = Branch::query()
            ->where('tenant_id', $user->tenant_id)
            ->active()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'code',
                'latitude',
                'longitude',
                'geofence_radius',
                'requires_geofencing',
                'is_active',
            ]);

        return response()->json([
            'success' => true,
            'data' => $branches,
        ]);
    }

    /**
     * Get a single branch with all geofence settings.
     * GET /api/branches/{branch}
     */
    public function show(Branch $branch): JsonResponse
    {
        $user = auth()->user();

        if (!$user || !$user->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'User does not belong to a tenant.',
            ], 403);
        }

        // Ensure branch belongs to the same tenant
        if ($branch->tenant_id !== $user->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'This branch does not belong to your tenant.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
                'latitude' => $branch->latitude,
                'longitude' => $branch->longitude,
                'geofence_radius' => $branch->geofence_radius,
                'requires_geofencing' => $branch->requires_geofencing,
                'is_active' => $branch->is_active,
                'address' => $branch->full_address,
            ],
        ]);
    }

    /**
     * Set or update GPS location and geofence radius for a branch.
     * POST /api/branches/{branch}/set-location
     */
    public function setLocation(Request $request, Branch $branch): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Only tenant admins or super admins can update branch GPS
        if (!$user->isSuperAdmin() && !$user->isTenantAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to update branch GPS settings.',
            ], 403);
        }

        // Ensure branch belongs to the same tenant (for non-super admins)
        if (!$user->isSuperAdmin() && $branch->tenant_id !== $user->tenant_id) {
            return response()->json([
                'success' => false,
                'message' => 'This branch does not belong to your tenant.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'geofence_radius' => 'nullable|integer|min:50|max:5000',
            'requires_geofencing' => 'nullable|boolean',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $branch->latitude = (float) $request->input('latitude');
        $branch->longitude = (float) $request->input('longitude');

        if ($request->filled('geofence_radius')) {
            $branch->geofence_radius = (int) $request->input('geofence_radius');
        }

        if ($request->has('requires_geofencing')) {
            $branch->requires_geofencing = (bool) $request->boolean('requires_geofencing');
        }

        if ($request->filled('name')) {
            $branch->name = (string) $request->input('name');
        }

        $branch->updated_by = $user->id;
        $branch->save();

        return response()->json([
            'success' => true,
            'message' => 'Branch location updated successfully.',
            'data' => [
                'id' => $branch->id,
                'name' => $branch->name,
                'code' => $branch->code,
                'latitude' => $branch->latitude,
                'longitude' => $branch->longitude,
                'geofence_radius' => $branch->geofence_radius,
                'requires_geofencing' => $branch->requires_geofencing,
            ],
        ]);
    }
}

