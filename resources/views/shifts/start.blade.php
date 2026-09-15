<x-app-layout>
    <x-slot name="header">
        <div class="px-8 py-4">
            <h2 class="text-xl font-semibold leading-tight text-slate-800">Start Shift</h2>
        </div>
    </x-slot>

    <div class="w-full px-2 pb-8">
        <div class="mx-auto max-w-lg">
            @if (session('info'))
                <div class="mb-4 rounded-md border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-800">{{ session('info') }}</div>
            @endif

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-slate-200 bg-slate-50 text-slate-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">Open a new shift session</h3>
                        <p class="text-xs text-slate-500">Enter the cash currently in the drawer to begin selling at this terminal.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('shifts.start.store') }}" class="p-5">
                    @csrf

                    <div class="mb-2 text-sm text-slate-600">
                        <span class="font-medium text-slate-800">Pharmacist:</span> {{ auth()->user()->name }}
                    </div>

                    <label for="starting_cash" class="block text-sm font-medium text-slate-700">Opening Cash in Drawer</label>
                    <div class="relative mt-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">$</span>
                        <input type="number" step="0.01" min="0" name="starting_cash" id="starting_cash"
                            value="{{ old('starting_cash', '0.00') }}" required autofocus
                            class="block w-full rounded-md border-slate-300 pl-7 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                    @error('starting_cash')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="mt-6 flex items-center justify-end gap-2">
                        <a href="{{ route('dashboard') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">Cancel</a>
                        <button type="submit" class="rounded-md bg-slate-800 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-900">Start Shift</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
