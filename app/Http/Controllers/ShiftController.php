<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Traits\HasRoleBasedRouting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    use HasRoleBasedRouting;

    /**
     * Resolve the role-aware POS entry point (falls back to the generic dashboard).
     */
    protected function posRoute(): string
    {
        $prefix = $this->getRoutePrefix();

        return $prefix !== '' ? route($prefix . 'sales.create') : route('dashboard');
    }

    /**
     * Show the Start Shift form for the incoming pharmacist on the shared terminal.
     */
    public function showStart(): View|RedirectResponse
    {
        $current = Shift::currentFor(auth()->user());

        if ($current) {
            return redirect()->to($this->posRoute())
                ->with('info', 'You already have an open shift. End it before starting a new one.');
        }

        return view('shifts.start');
    }

    /**
     * Open a new flexible shift session with the starting cash in the drawer.
     */
    public function start(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'starting_cash' => 'required|numeric|min:0',
        ]);

        // Guard against opening two concurrent shifts for the same user.
        if (Shift::currentFor(auth()->user())) {
            return redirect()->to($this->posRoute())
                ->with('info', 'You already have an open shift.');
        }

        Shift::create([
            'user_id' => auth()->id(),
            'start_time' => now(),
            'starting_cash' => $validated['starting_cash'],
            'status' => Shift::STATUS_OPEN,
        ]);

        return redirect()->to($this->posRoute())
            ->with('success', 'Shift started. You can now process sales at this terminal.');
    }

    /**
     * Show the End Shift form with the current session summary before handover.
     */
    public function showEnd(): View|RedirectResponse
    {
        $shift = Shift::currentFor(auth()->user());

        if (! $shift) {
            return redirect()->route('shifts.start')
                ->with('info', 'You do not have an open shift to end. Start one first.');
        }

        $shift->loadCount('sales');

        return view('shifts.end', compact('shift'));
    }

    /**
     * Close the active shift: record ending cash, end time and set status to closed.
     */
    public function end(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ending_cash' => 'required|numeric|min:0',
        ]);

        $shift = Shift::currentFor(auth()->user());

        if (! $shift) {
            return redirect()->route('shifts.start')
                ->with('info', 'You do not have an open shift to end.');
        }

        $shift->update([
            'end_time' => now(),
            'ending_cash' => $validated['ending_cash'],
            'status' => Shift::STATUS_CLOSED,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Shift closed. The terminal is ready for the next pharmacist.');
    }

    /**
     * Show the retroactive edit form for a shift (admin only).
     */
    public function edit(Shift $shift): View
    {
        $this->authorize('access-admin-dashboard');

        $shift->load('user');

        return view('shifts.edit', compact('shift'));
    }

    /**
     * Retroactively adjust a shift's times or cash values (admin only).
     *
     * Admins may correct start_time, end_time, starting_cash, ending_cash and
     * status to accommodate custom branch scenarios or human input errors.
     */
    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $this->authorize('access-admin-dashboard');

        $validated = $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'starting_cash' => 'required|numeric|min:0',
            'ending_cash' => 'nullable|numeric|min:0',
            'status' => 'required|in:open,closed',
        ]);

        if ($validated['status'] === Shift::STATUS_OPEN) {
            // An open shift has no closing values.
            $validated['end_time'] = null;
            $validated['ending_cash'] = null;
        } elseif (empty($validated['end_time'])) {
            // Closing without an explicit end time defaults to now.
            $validated['end_time'] = now();
        }

        $shift->update($validated);

        return redirect()->route('admin.reports.shift-reconciliation')
            ->with('success', 'Shift #' . $shift->id . ' updated successfully.');
    }
}
