<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\BranchContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceApiController extends Controller
{
    /**
     * Check-in endpoint for mobile app
     * POST /api/attendance/check-in
     */
    public function checkIn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'branch_id' => 'required|exists:branches,id',
            'device_info' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = auth()->user();
            $branch = Branch::findOrFail($request->branch_id);

            // Verify user belongs to this branch
            // - Super admins can access all branches
            // - Active branch context (permanent branch_id, or a temporary
            //   HR allocation override that is active today)
            // - Legacy many-to-many assignment via branch_user pivot
            // - Option A: Any user can access branches that belong to their tenant
            $activeBranch = BranchContextService::getActiveUserBranchContext($user);
            $userBelongsToBranch = $user->is_super_admin ||
                                   ($activeBranch && $activeBranch->id === $branch->id) ||
                                   $user->branches()->where('branch_id', $branch->id)->exists() ||
                                   ($user->tenant_id && $branch->tenant_id === $user->tenant_id);

            if (!$userBelongsToBranch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: You are not assigned to this branch',
                ], 403);
            }

            $result = AttendanceService::checkIn(
                $user,
                $branch,
                (float)$request->latitude,
                (float)$request->longitude,
                $request->device_info,
                $request->notes
            );

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check-out endpoint for mobile app
     * POST /api/attendance/check-out
     */
    public function checkOut(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'device_info' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = auth()->user();

            $result = AttendanceService::checkOut(
                $user,
                (float)$request->latitude,
                (float)$request->longitude,
                $request->device_info,
                $request->notes
            );

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get today's attendance status
     * GET /api/attendance/today
     */
    public function getTodayStatus(): JsonResponse
    {
        try {
            $user = auth()->user();
            $attendance = $user->attendances()
                ->whereDate('attendance_date', today())
                ->first();

            if (!$attendance) {
                return response()->json([
                    'status' => 'not_checked_in',
                    'message' => 'No attendance record for today',
                ]);
            }

            return response()->json([
                'status' => $attendance->status,
                'check_in_time' => $attendance->check_in_time,
                'check_out_time' => $attendance->check_out_time,
                'check_in_within_geofence' => $attendance->check_in_within_geofence,
                'check_out_within_geofence' => $attendance->check_out_within_geofence,
                'duration' => $attendance->formatted_duration,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the branch the user is actually operating under right now (their
     * active temporary HR allocation override, if any, otherwise their
     * permanent branch_id assignment). Used by the mobile app to geofence-
     * check the correct location for a floated worker.
     * GET /api/attendance/branch
     */
    public function getBranch(): JsonResponse
    {
        try {
            $user = auth()->user();
            $branch = BranchContextService::getActiveUserBranchContext($user);

            if (!$branch) {
                return response()->json([
                    'success' => false,
                    'message' => 'No branch assigned to user',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'branch' => [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'latitude' => $branch->latitude,
                    'longitude' => $branch->longitude,
                    'geofence_radius' => $branch->geofence_radius,
                    'address' => $branch->full_address,
                ],
                'is_temporary_override' => BranchContextService::isUnderOverride($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all branches assigned to the user
     * GET /api/attendance/my-branches
     */
    public function getMyBranches(): JsonResponse
    {
        try {
            $user = auth()->user();

            // Get branches from many-to-many relationship
            $branches = $user->branches()->get();

            // If no branches in many-to-many, fall back to legacy single branch
            if ($branches->isEmpty() && $user->branch) {
                $branches = collect([$user->branch]);
            }

            // Option A: If still no branches, fall back to all active branches for the user's tenant
            if ($branches->isEmpty() && $user->tenant_id) {
                $branches = Branch::query()
                    ->where('tenant_id', $user->tenant_id)
                    ->active()
                    ->orderBy('name')
                    ->get();
            }

            // If the user is currently floated to another branch via a
            // temporary HR allocation override, make sure that branch is
            // included (and listed first) so the mobile app can geofence
            // check-in against the branch they're actually working at today.
            $activeBranch = BranchContextService::getActiveUserBranchContext($user);
            if ($activeBranch && ! $branches->contains('id', $activeBranch->id)) {
                $branches = $branches->prepend($activeBranch);
            } elseif ($activeBranch) {
                $branches = $branches->sortByDesc(fn ($b) => $b->id === $activeBranch->id)->values();
            }

            if ($branches->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No branches assigned to user',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'branches' => $branches->map(function ($branch) {
                    return [
                        'id' => $branch->id,
                        'name' => $branch->name,
                        'latitude' => $branch->latitude,
                        'longitude' => $branch->longitude,
                        'geofence_radius' => $branch->geofence_radius,
                        'address' => $branch->full_address,
                        'code' => $branch->code,
                    ];
                })->values(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start break endpoint for mobile app
     * POST /api/attendance/break-start
     */
    public function breakStart(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = auth()->user();
            $today = now()->toDateString();

            // Get today's attendance record (using date comparison)
            $attendance = $user->attendances()
                ->whereDate('attendance_date', $today)
                ->where('branch_id', $request->branch_id)
                ->first();

            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'No check-in record found for today',
                ], 404);
            }

            if (!$attendance->check_in_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must check in before taking a break',
                ], 400);
            }

            if ($attendance->break_start_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Break already started. End the current break first.',
                ], 400);
            }

            // Start the break
            $attendance->update([
                'break_start_time' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Break started',
                'break_start_time' => $attendance->break_start_time,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * End break endpoint for mobile app
     * POST /api/attendance/break-end
     */
    public function breakEnd(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = auth()->user();
            $today = now()->toDateString();

            // Get today's attendance record (using date comparison)
            $attendance = $user->attendances()
                ->whereDate('attendance_date', $today)
                ->where('branch_id', $request->branch_id)
                ->first();

            if (!$attendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'No check-in record found for today',
                ], 404);
            }

            if (!$attendance->break_start_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active break. Start a break first.',
                ], 400);
            }

            if ($attendance->break_end_time) {
                return response()->json([
                    'success' => false,
                    'message' => 'Break already ended',
                ], 400);
            }

            // Calculate break duration in minutes
            $breakDuration = (int) round($attendance->break_start_time->diffInMinutes(now()));

            // End the break
            $attendance->update([
                'break_end_time' => now(),
                'break_duration_minutes' => $breakDuration,
                'total_break_count' => ($attendance->total_break_count ?? 0) + 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Break ended',
                'break_end_time' => $attendance->break_end_time,
                'break_duration_minutes' => $breakDuration,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
}

