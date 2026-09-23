<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between px-6 py-4">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Temporary Branch Allocations') }}
            </h2>
            <span class="hidden sm:inline-flex items-center text-sm text-slate-500">
                <i class="fa-regular fa-calendar mr-2"></i>{{ now()->format('l, M j, Y') }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" x-data="allocationDashboard()">

        @if (session('success'))
            <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Create / Edit Allocation --}}
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-700" x-text="mode === 'edit' ? 'Edit Temporary Allocation' : 'New Temporary Allocation'"></h3>
                <button type="button" x-show="mode === 'edit'" x-on:click="resetForm()" class="text-xs font-medium text-slate-500 hover:text-slate-700">
                    <i class="fa-solid fa-xmark mr-1"></i>Cancel edit
                </button>
            </div>

            <form method="POST" x-bind:action="mode === 'edit' ? ('/branch-allocation-overrides/' + overrideId) : '{{ route('branch-allocation-overrides.store') }}'" class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @csrf
                <template x-if="mode === 'edit'">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                {{-- Searchable employee dropdown --}}
                <div class="relative" x-bind:class="mode === 'edit' ? 'opacity-60 pointer-events-none' : ''">
                    <label class="block text-sm font-medium text-slate-600 mb-1">Employee</label>
                    <input type="hidden" name="user_id" x-model="employeeId">
                    <input
                        type="text"
                        x-model="employeeQuery"
                        x-on:focus="employeeListOpen = true"
                        x-on:click.outside="employeeListOpen = false"
                        placeholder="Search employee by name or email..."
                        autocomplete="off"
                        class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    >
                    <div x-show="employeeListOpen && filteredEmployees().length > 0" x-cloak class="absolute z-10 mt-1 w-full bg-white border border-slate-200 rounded-md shadow-lg max-h-56 overflow-y-auto">
                        <template x-for="employee in filteredEmployees()" :key="employee.id">
                            <button
                                type="button"
                                x-on:click="selectEmployee(employee)"
                                class="w-full text-left px-3 py-2 text-sm hover:bg-slate-50 flex flex-col"
                            >
                                <span class="font-medium text-slate-800" x-text="employee.name"></span>
                                <span class="text-xs text-slate-400" x-text="employee.email"></span>
                            </button>
                        </template>
                    </div>
                    <p class="mt-1 text-xs text-slate-400" x-show="employeeId" x-cloak>Selected employee ID: <span x-text="employeeId"></span></p>
                </div>

                {{-- Target branch --}}
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Target Branch</label>
                    <select name="target_branch_id" x-model="targetBranchId" required class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="" disabled>Select branch...</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }} @if($branch->branch_type === \App\Models\Branch::TYPE_HQ) (HQ) @endif</option>
                        @endforeach
                    </select>
                </div>

                {{-- Reason --}}
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Reason <span class="text-slate-400 font-normal">(optional)</span></label>
                    <input type="text" name="reason" x-model="reason" maxlength="255" placeholder="e.g. Covering shortage at Branch B" class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                {{-- Start date --}}
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Start Date</label>
                    <input type="date" name="start_date" x-model="startDate" required class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                {{-- End date --}}
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">End Date</label>
                    <input type="date" name="end_date" x-model="endDate" required class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full h-10 px-4 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors">
                        <i class="fa-solid fa-check mr-1"></i>
                        <span x-text="mode === 'edit' ? 'Update Allocation' : 'Create Allocation'"></span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Active & Upcoming Allocations --}}
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-700">Branch Allocations</h3>
                <span class="text-xs text-slate-400">{{ $overrides->total() }} total</span>
            </div>
            <div class="p-2">
                @if ($overrides->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                                    <th class="px-3 py-2 font-medium">Employee</th>
                                    <th class="px-3 py-2 font-medium">Home Branch</th>
                                    <th class="px-3 py-2 font-medium">Target Branch</th>
                                    <th class="px-3 py-2 font-medium">Start</th>
                                    <th class="px-3 py-2 font-medium">End</th>
                                    <th class="px-3 py-2 font-medium">Status</th>
                                    <th class="px-3 py-2 font-medium">Reason</th>
                                    <th class="px-3 py-2 font-medium">Created By</th>
                                    <th class="px-3 py-2 font-medium text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($overrides as $override)
                                    @php
                                        $today = now()->startOfDay();
                                        $status = $today->lt($override->start_date) ? 'upcoming' : ($today->gt($override->end_date) ? 'expired' : 'active');
                                        $statusClasses = [
                                            'active' => 'bg-emerald-50 text-emerald-700',
                                            'upcoming' => 'bg-amber-50 text-amber-700',
                                            'expired' => 'bg-slate-100 text-slate-500',
                                        ];
                                    @endphp
                                    <tr>
                                        <td class="px-3 py-2.5">
                                            <p class="font-medium text-slate-900">{{ $override->user->name ?? '—' }}</p>
                                        </td>
                                        <td class="px-3 py-2.5 text-slate-500">{{ $override->user->branch->name ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-slate-700 font-medium">{{ $override->targetBranch->name ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-slate-500">{{ $override->start_date->format('M j, Y') }}</td>
                                        <td class="px-3 py-2.5 text-slate-500">{{ $override->end_date->format('M j, Y') }}</td>
                                        <td class="px-3 py-2.5">
                                            <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClasses[$status] }}">
                                                {{ ucfirst($status) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2.5 text-slate-500">{{ $override->reason ?: '—' }}</td>
                                        <td class="px-3 py-2.5 text-slate-500">{{ $override->creator->name ?? '—' }}</td>
                                        <td class="px-3 py-2.5 text-right whitespace-nowrap">
                                            <button
                                                type="button"
                                                x-on:click="openEdit(@js([
                                                    'id' => $override->id,
                                                    'employeeId' => $override->user_id,
                                                    'employeeName' => $override->user->name ?? '',
                                                    'employeeEmail' => $override->user->email ?? '',
                                                    'targetBranchId' => $override->target_branch_id,
                                                    'startDate' => $override->start_date->format('Y-m-d'),
                                                    'endDate' => $override->end_date->format('Y-m-d'),
                                                    'reason' => $override->reason,
                                                ]))"
                                                class="text-indigo-600 hover:text-indigo-900 font-medium mr-3"
                                            >
                                                Edit
                                            </button>
                                            <form method="POST" action="{{ route('branch-allocation-overrides.destroy', $override) }}" style="display:inline;" onsubmit="return confirm('Revoke this branch allocation?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Revoke</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 px-3">
                        {{ $overrides->links() }}
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-32 text-slate-400">
                        <i class="fa-solid fa-people-arrows text-3xl mb-2"></i>
                        <p class="text-sm">No branch allocations yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function allocationDashboard() {
            return {
                employees: @json($employees->map(fn ($e) => ['id' => $e->id, 'name' => $e->name, 'email' => $e->email])),
                mode: 'create',
                overrideId: null,
                employeeId: '',
                employeeQuery: '',
                employeeListOpen: false,
                targetBranchId: '',
                startDate: '',
                endDate: '',
                reason: '',

                filteredEmployees() {
                    const q = this.employeeQuery.trim().toLowerCase();
                    if (!q) {
                        return this.employees.slice(0, 20);
                    }
                    return this.employees.filter(function (e) {
                        return e.name.toLowerCase().includes(q) || e.email.toLowerCase().includes(q);
                    }).slice(0, 20);
                },

                selectEmployee(employee) {
                    this.employeeId = employee.id;
                    this.employeeQuery = employee.name + ' (' + employee.email + ')';
                    this.employeeListOpen = false;
                },

                openEdit(override) {
                    this.mode = 'edit';
                    this.overrideId = override.id;
                    this.employeeId = override.employeeId;
                    this.employeeQuery = override.employeeName + ' (' + override.employeeEmail + ')';
                    this.targetBranchId = override.targetBranchId;
                    this.startDate = override.startDate;
                    this.endDate = override.endDate;
                    this.reason = override.reason || '';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                resetForm() {
                    this.mode = 'create';
                    this.overrideId = null;
                    this.employeeId = '';
                    this.employeeQuery = '';
                    this.targetBranchId = '';
                    this.startDate = '';
                    this.endDate = '';
                    this.reason = '';
                },
            };
        }
    </script>
    @endpush
</x-app-layout>
