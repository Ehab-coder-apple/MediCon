<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class UpdateProductDaysOnHand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:update-doh {--force : Force update even if DOH is already set}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Days On Hand (DOH) for all products based on sales data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Days On Hand calculation for all products...');

        $force = $this->option('force');
        $query = Product::where('is_active', true);

        if (!$force) {
            $query->whereNull('days_on_hand');
        }

        $products = $query->get();
        $updated = 0;
        $skipped = 0;

        $progressBar = $this->output->createProgressBar($products->count());
        $progressBar->start();

        foreach ($products as $product) {
            try {
                $oldDOH = $product->days_on_hand;
                $newDOH = $product->calculateDaysOnHand();

                if ($oldDOH !== $newDOH || $force) {
                    $product->updateDaysOnHand();
                    $updated++;

                    if ($this->output->isVerbose()) {
                        $this->line("\n{$product->name}: {$oldDOH} → {$newDOH} days");
                    }
                } else {
                    $skipped++;
                }

            } catch (\Exception $e) {
                $this->error("\nError updating {$product->name}: " . $e->getMessage());
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        $this->newLine(2);
        $this->info("DOH Update Complete!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Products Updated', $updated],
                ['Products Skipped', $skipped],
                ['Total Processed', $products->count()],
            ]
        );

        // Show products with critical DOH
        $criticalProducts = Product::where('is_active', true)
            ->where('days_on_hand', '<=', 7)
            ->get();

        if ($criticalProducts->count() > 0) {
            $this->warn("\nProducts with Critical DOH (≤7 days):");
            $this->table(
                ['Product', 'Current Stock', 'DOH', 'Status'],
                $criticalProducts->map(function ($product) {
                    return [
                        $product->name,
                        $product->active_quantity,
                        $product->days_on_hand . ' days',
                        $product->doh_status_text
                    ];
                })->toArray()
            );
        }

        return Command::SUCCESS;
    }
}
