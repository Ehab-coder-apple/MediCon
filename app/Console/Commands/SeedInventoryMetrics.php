<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\InventoryMetricsTestSeeder;

class SeedInventoryMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:inventory-metrics {--fresh : Truncate tables before seeding}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed inventory metrics test data (expired, nearly expired, low stock, out of stock products)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧪 Seeding Inventory Metrics Test Data...');
        $this->newLine();

        if ($this->option('fresh')) {
            $this->info('🗑️  Truncating products and batches tables...');
            
            // Truncate batches first (foreign key constraint)
            \DB::table('batches')->truncate();
            \DB::table('products')->truncate();
            
            $this->info('✓ Tables truncated');
            $this->newLine();
        }

        try {
            $this->call('db:seed', [
                '--class' => InventoryMetricsTestSeeder::class,
            ]);

            $this->newLine();
            $this->info('✅ Inventory metrics test data seeded successfully!');
            $this->newLine();
            $this->displaySummary();

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error seeding inventory metrics: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Display summary of created test data
     */
    private function displaySummary(): void
    {
        $this->line('📊 Test Data Summary:');
        $this->line('');
        $this->line('  <fg=red>📛 Expired Products (3)</> - Products with batches past expiry date');
        $this->line('     • Expired Antibiotic Syrup (EXP-ANT-001)');
        $this->line('     • Expired Cough Syrup (EXP-COUGH-001)');
        $this->line('     • Expired Vitamin C Tablets (EXP-VIT-001)');
        $this->line('');
        $this->line('  <fg=yellow>⏰ Nearly Expired Products (3)</> - Products expiring within 30 days');
        $this->line('     • Nearly Expired Pain Relief (NEAR-PAIN-001)');
        $this->line('     • Nearly Expired Digestive Aid (NEAR-DIG-001)');
        $this->line('     • Nearly Expired Allergy Relief (NEAR-ALLERGY-001)');
        $this->line('');
        $this->line('  <fg=bright-red>📉 Low Stock Products (3)</> - Quantity below alert threshold');
        $this->line('     • Low Stock Insulin Injection (LOW-INS-001)');
        $this->line('     • Low Stock Blood Pressure Monitor (LOW-BP-001)');
        $this->line('     • Low Stock Antibiotic Cream (LOW-CREAM-001)');
        $this->line('');
        $this->line('  <fg=red>🚫 Out of Stock Products (3)</> - Zero quantity');
        $this->line('     • Out of Stock Cardiac Medicine (OOS-CARD-001)');
        $this->line('     • Out of Stock Migraine Relief (OOS-MIGR-001)');
        $this->line('     • Out of Stock Sleep Aid (OOS-SLEEP-001)');
        $this->line('');
        $this->line('🔗 View the dashboard at: <fg=cyan>http://127.0.0.1:8000/admin/dashboard</>');
    }
}

