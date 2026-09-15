<x-app-layout>
    <x-slot name="header">
        <div class="px-8 py-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-800">End Shift</h2>
        </div>
    </x-slot>

    <div class="w-full px-2 pb-8">
        <div class="mx-auto max-w-lg">
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-slate-200 bg-slate-50 text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/></svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Close this shift before handover</h3>
                        <p class="text-xs text-slate-500">Count the drawer and enter the closing cash to end the session.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-px bg-slate-200 text-sm">
                    <div class="bg-white px-5 py-3">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Pharmacist</div>
                        <div class="mt-0.5 font-medium text-slate-900">{{ $shift->user->name ?? auth()->user()->name }}</div>
                    </div>
                    <div class="bg-white px-5 py-3">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Started</div>
                        <div class="mt-0.5 font-medium text-slate-900">{{ $shift->start_time->format('M d, Y H:i') }}</div>
                    </div>
                    <div class="bg-white px-5 py-3">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Transactions</div>
                        <div class="mt-0.5 font-medium text-slate-900">{{ number_format($shift->sales_count) }}</div>
                    </div>
                    <div class="bg-white px-5 py-3">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Expected System Sales</div>
                        <div class="mt-0.5 font-medium text-slate-900">${{ number_format($shift->expected_sales, 2) }}</div>
                    </div>
                    <div class="bg-white px-5 py-3">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Opening Cash</div>
                        <div class="mt-0.5 font-medium text-slate-900">${{ number_format($shift->starting_cash, 2) }}</div>
                    </div>
                    <div class="bg-white px-5 py-3">
                        <div class="text-xs uppercase tracking-wide text-slate-500">Expected Drawer</div>
                        <div class="mt-0.5 font-medium text-slate-900">${{ number_format($shift->starting_cash + $shift->expected_sales, 2) }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('shifts.end.store') }}" class="p-5">
                    @csrf

                    <label for="ending_cash" class="block text-sm font-medium text-slate-700">Closing Cash in Drawer</label>
                    <div class="relative mt-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">$</span>
                        <input type="number" step="0.01" min="0" name="ending_cash" id="ending_cash"
                            value="{{ old('ending_cash') }}" required autofocus
                            class="block w-full rounded-md border-slate-300 pl-7 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                    @error('ending_cash')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="mt-6 flex items-center justify-end gap-2">
                        <a href="{{ route('dashboard') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Cancel</a>
                        <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-900">Close Shift</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
