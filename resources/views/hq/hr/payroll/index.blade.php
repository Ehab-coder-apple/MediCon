<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between px-6 py-4">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                {{ __('Payroll — Employee Contracts') }}
            </h2>
            <a href="{{ route('hq-hr.payroll.slips') }}" class="inline-flex items-center px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium rounded-md transition-colors">
                <i class="fa-solid fa-file-invoice-dollar mr-2"></i>View Payslips
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" x-data="payrollContractForm()">

        @if (session('success'))
            <div class="p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="p-4 bg-amber-100 border border-amber-400 text-amber-700 rounded-lg">{{ session('warning') }}</div>
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

        {{-- Set / Revise Contract --}}
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <div class="px-5 py-3 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-700">Set / Revise Employee Contract</h3>
                <p class="text-xs text-slate-400 mt-0.5">Creating a new contract automatically closes any existing active one and records the change in the salary history ledger.</p>
            </div>

            <form method="POST" action="{{ route('hq-hr.payroll.contracts.store') }}" class="p-5 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Employee</label>
                    <select name="user_id" required class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="" disabled selected>Select employee...</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->branch->name ?? 'No branch' }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Pay Type</label>
                    <select name="pay_type" x-model="payType" required class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="hourly">Hourly Rate</option>
                        <option value="monthly">Monthly Salary</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Currency</label>
                    <input type="text" name="currency" value="IQD" maxlength="3" class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div x-show="payType === 'hourly'">
                    <label class="block text-sm font-medium text-slate-600 mb-1">Hourly Rate</label>
                    <input type="number" step="0.01" min="0" name="hourly_rate" class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div x-show="payType === 'monthly'" x-cloak>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Monthly Salary</label>
                    <input type="number" step="0.01" min="0" name="monthly_salary" class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Effective Date</label>
                    <input type="date" name="effective_date" value="{{ now()->toDateString() }}" required class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-slate-600 mb-1">Reason <span class="text-slate-400 font-normal">(optional)</span></label>
                    <input type="text" name="reason" maxlength="255" placeholder="e.g. Annual raise, promotion, initial hire" class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full h-10 px-4 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium transition-colors">
                        <i class="fa-solid fa-check mr-1"></i>Save Contract
                    </button>
                </div>
            </form>
        </div>

        {{-- Current Contracts --}}
        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
            <div class="flex items-center justify-between px-5 py-3 border-b border-slate-200">
                <h3 class="text-sm font-semibold text-slate-700">Employees &amp; Current Pay Basis</h3>
                <span class="text-xs text-slate-400">{{ $employees->count() }} active employees</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-400">
                            <th class="px-5 py-2 font-medium">Employee</th>
                            <th class="px-5 py-2 font-medium">Branch</th>
                            <th class="px-5 py-2 font-medium">Pay Type</th>
                            <th class="px-5 py-2 font-medium">Rate</th>
                            <th class="px-5 py-2 font-medium">Effective Since</th>
                            <th class="px-5 py-2 font-medium text-right">History</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($employees as $employee)
                            @php($contract = $activeContracts->get($employee->id))
                            <tr>
                                <td class="px-5 py-2.5 font-medium text-slate-900">{{ $employee->name }}</td>
                                <td class="px-5 py-2.5 text-slate-500">{{ $employee->branch->name ?? '—' }}</td>
                                <td class="px-5 py-2.5">
                                    @if ($contract)
                                        <span class="px-2 py-0.5 inline-flex text-xs leading-5 font-semibold rounded-full {{ $contract->isHourly() ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                            {{ ucfirst($contract->pay_type) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 italic">No contract</span>
                                    @endif
                                </td>
                                <td class="px-5 py-2.5 text-slate-700">
                                    @if ($contract)
                                        {{ number_format($contract->base_rate, 2) }} {{ $contract->currency }}
                                        <span class="text-slate-400">/ {{ $contract->isHourly() ? 'hr' : 'mo' }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-2.5 text-slate-500">{{ $contract?->effective_date?->format('M j, Y') ?? '—' }}</td>
                                <td class="px-5 py-2.5 text-right">
                                    <a href="{{ route('hq-hr.payroll.salary-increases', $employee) }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-xs">
                                        View History
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">No active employees found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function payrollContractForm() {
            return { payType: 'hourly' };
        }
    </script>
    @endpush
</x-app-layout>
