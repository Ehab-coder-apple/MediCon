<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchAllocationOverride;
use App\Models\User;
use App\Services\BranchAllocationOverrideService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchAllocationOverrideController extends Controller
{
    public function __construct(private readonly BranchAllocationOverrideService $service)
    {
    }

    /**
     * List branch allocation overrides for the current tenant. Kept as a
     * plain JSON endpoint for now; the HQ HR dashboard view is wired up in
     * a later phase.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = $request->user()->tenant_id;

        $overrides = BranchAllocationOverride::where('tenant_id', $tenantId)
            ->with(['user', 'targetBranch', 'creator'])
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->get('user_id')))
            ->orderByDesc('start_date')
            ->paginate(20);

        return response()->json($overrides);
    }

    /**
     * Create a temporary branch allocation override. Restricted to users
     * with the 'manage_branch_allocations' permission (e.g. HQ HR
     * Manager). Overlap for the same user is guarded inside the service.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('manage-branch-allocations');

        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'target_branch_id' => 'required|integer|exists:branches,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $targetBranch = Branch::findOrFail($validated['target_branch_id']);

        if ($user->tenant_id !== $request->user()->tenant_id || $targetBranch->tenant_id !== $request->user()->tenant_id) {
            abort(403, 'You can only manage allocations within your own organization.');
        }

        try {
            $override = $this->service->createOverride(
                $user,
                $targetBranch,
                Carbon::parse($validated['start_date']),
                Carbon::parse($validated['end_date']),
                $validated['reason'] ?? null,
                $request->user()
            );
        } catch (\Exception $e) {
            return $this->fail($request, $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json($override, 201);
        }

        return back()->with('success', 'Branch allocation override created successfully.');
    }

    /**
     * Update (edit) an existing override's target branch / date range /
     * reason. Restricted to users with the 'manage_branch_allocations'
     * permission (e.g. HQ HR Manager).
     */
    public function update(Request $request, BranchAllocationOverride $branchAllocationOverride): RedirectResponse|JsonResponse
    {
        $this->authorize('manage-branch-allocations');

        if ($branchAllocationOverride->tenant_id !== $request->user()->tenant_id) {
            abort(403, 'Unauthorized access to this branch allocation override.');
        }

        $validated = $request->validate([
            'target_branch_id' => 'required|integer|exists:branches,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:255',
        ]);

        $targetBranch = Branch::findOrFail($validated['target_branch_id']);

        if ($targetBranch->tenant_id !== $request->user()->tenant_id) {
            abort(403, 'You can only manage allocations within your own organization.');
        }

        try {
            $override = $this->service->updateOverride(
                $branchAllocationOverride,
                $targetBranch,
                Carbon::parse($validated['start_date']),
                Carbon::parse($validated['end_date']),
                $validated['reason'] ?? null
            );
        } catch (\Exception $e) {
            return $this->fail($request, $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json($override);
        }

        return back()->with('success', 'Branch allocation override updated successfully.');
    }

    /**
     * Cancel/delete an override.
     */
    public function destroy(Request $request, BranchAllocationOverride $branchAllocationOverride): RedirectResponse|JsonResponse
    {
        $this->authorize('manage-branch-allocations');

        if ($branchAllocationOverride->tenant_id !== $request->user()->tenant_id) {
            abort(403, 'Unauthorized access to this branch allocation override.');
        }

        $this->service->deleteOverride($branchAllocationOverride);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Branch allocation override deleted.']);
        }

        return back()->with('success', 'Branch allocation override deleted.');
    }

    private function fail(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return back()->withErrors(['error' => $message])->withInput();
    }
}
