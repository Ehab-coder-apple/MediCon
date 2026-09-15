<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 px-8 py-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-xl font-semibold leading-tight text-slate-800">Adjust Shift #{{ $shift->id }}</h2>
            <a href="{{ route('admin.reports.shift-reconciliation') }}" class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
                <span>Back to Ledger</span>
            </a>
        </div>
    </x-slot>

    <div class="w-full px-2 pb-8">
        <div class="mx-auto max-w-2xl">
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h3 class="text-sm font-semibold text-slate-800">Retroactive adjustment</h3>
                    <p class="text-xs text-slate-500">Correct the times or cash values for {{ $shift->user->name ?? 'this pharmacist' }} to fix input errors or custom branch scenarios.</p>
                </div>

                <form method="POST" action="{{ route('admin.shifts.update', $shift) }}" class="p-5">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="start_time" class="block text-sm font-medium text-slate-700">Start Time</label>
                            <input type="datetime-local" name="start_time" id="start_time" required
                                value="{{ old('start_time', optional($shift->start_time)->format('Y-m-d\TH:i')) }}"
                                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                            @error('start_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="end_time" class="block text-sm font-medium text-slate-700">End Time <span class="text-slate-400">(blank if open)</span></label>
                            <input type="datetime-local" name="end_time" id="end_time"
                                value="{{ old('end_time', optional($shift->end_time)->format('Y-m-d\TH:i')) }}"
                                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                            @error('end_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="starting_cash" class="block text-sm font-medium text-slate-700">Opening Cash</label>
                            <div class="relative mt-1">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">$</span>
                                <input type="number" step="0.01" min="0" name="starting_cash" id="starting_cash" required
                                    value="{{ old('starting_cash', number_format((float) $shift->starting_cash, 2, '.', '')) }}"
                                    class="block w-full rounded-md border-slate-300 pl-7 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                            </div>
                            @error('starting_cash')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="ending_cash" class="block text-sm font-medium text-slate-700">Closing Cash <span class="text-slate-400">(blank if open)</span></label>
                            <div class="relative mt-1">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">$</span>
                                <input type="number" step="0.01" min="0" name="ending_cash" id="ending_cash"
                                    value="{{ old('ending_cash', $shift->ending_cash !== null ? number_format((float) $shift->ending_cash, 2, '.', '') : '') }}"
                                    class="block w-full rounded-md border-slate-300 pl-7 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                            </div>
                            @error('ending_cash')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="status" class="block text-sm font-medium text-slate-700">Status</label>
                            <select name="status" id="status" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                                <option value="open" {{ old('status', $shift->status) === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="closed" {{ old('status', $shift->status) === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2">
                        <a href="{{ route('admin.reports.shift-reconciliation') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Cancel</a>
                        <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-900">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
