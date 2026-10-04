<?php

namespace App\Http\Controllers;

use App\Services\BranchContextService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BranchContextController extends Controller
{
    /**
     * Switch (or clear) the authenticated global-role user's temporary
     * dashboard viewing context, as selected from the header context
     * dropdown. Branch-scoped roles (pharmacist, sales_staff, worker) are
     * rejected by the 'switch-branch-viewing-context' Gate below - they are
     * always locked to their own assigned branch.
     */
    public function setActiveViewingContext(Request $request): RedirectResponse
    {
        $this->authorize('switch-branch-viewing-context');

        $validated = $request->validate([
            // Nullable so the dropdown can also offer a "back to my own
            // branch" option that clears the override.
            'branch_id' => 'nullable|integer|exists:branches,id',
        ]);

        $switched = BranchContextService::setViewingBranch(
            $request->user(),
            $validated['branch_id'] ?? null
        );

        if (! $switched) {
            return back()->withErrors([
                'branch_id' => 'You cannot switch to that branch.',
            ]);
        }

        return back()->with('success', 'Dashboard viewing context updated.');
    }
}
