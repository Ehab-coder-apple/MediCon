<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class OptimizeForProduction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medicon:optimize {--force : Force optimization even if not in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize MediCon application for production deployment';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!app()->environment('production') && !$this->option('force')) {
            $this->error('This command should only be run in production environment.');
            $this->info('Use --force flag to run in other environments.');
            return 1;
        }

        $this->info('🚀 Optimizing MediCon for production...');
        $this->newLine();

        // Step 1: Clear all caches
        $this->task('Clearing all caches', function () {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            Artisan::call('event:clear');
            return true;
        });

        // Step 2: Cache configuration
        $this->task('Caching configuration', function () {
            Artisan::call('config:cache');
            return true;
        });

        // Step 3: Cache routes
        $this->task('Caching routes', function () {
            Artisan::call('route:cache');
            return true;
        });

        // Step 4: Cache views
        $this->task('Caching views', function () {
            Artisan::call('view:cache');
            return true;
        });

        // Step 5: Cache events
        $this->task('Caching events', function () {
            Artisan::call('event:cache');
            return true;
        });

        // Step 6: Optimize autoloader
        $this->task('Optimizing autoloader', function () {
            exec('composer dump-autoload --optimize --classmap-authoritative', $output, $returnCode);
            return $returnCode === 0;
        });

        // Step 7: Create storage link
        $this->task('Creating storage symlink', function () {
            if (!File::exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }
            return true;
        });

        // Step 8: Optimize database
        $this->task('Optimizing database', function () {
            // Run any pending migrations
            Artisan::call('migrate', ['--force' => true]);

            // Seed production data if needed
            if (app()->environment('production')) {
                // Only seed essential data in production
                Artisan::call('db:seed', ['--class' => 'RoleSeeder', '--force' => true]);
            }

            return true;
        });

        // Step 9: Set file permissions
        $this->task('Setting file permissions', function () {
            $directories = [
                storage_path(),
                base_path('bootstrap/cache'),
            ];

            foreach ($directories as $directory) {
                if (File::exists($directory)) {
                    chmod($directory, 0755);
                    exec("find {$directory} -type f -exec chmod 644 {} \;");
                    exec("find {$directory} -type d -exec chmod 755 {} \;");
                }
            }

            return true;
        });

        // Step 10: Generate application key if missing
        $this->task('Checking application key', function () {
            if (empty(config('app.key'))) {
                Artisan::call('key:generate', ['--force' => true]);
            }
            return true;
        });

        // Step 11: Queue optimization
        $this->task('Optimizing queues', function () {
            Artisan::call('queue:restart');
            return true;
        });

        // Step 12: Clear OPcache if available
        $this->task('Clearing OPcache', function () {
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            return true;
        });

        $this->newLine();
        $this->info('✅ Production optimization completed successfully!');
        $this->newLine();

        // Display optimization summary
        $this->displayOptimizationSummary();

        return 0;
    }

    /**
     * Display optimization summary
     */
    private function displayOptimizationSummary(): void
    {
        $this->info('📊 Optimization Summary:');
        $this->info('========================');

        $optimizations = [
            'Configuration cached' => File::exists(base_path('bootstrap/cache/config.php')),
            'Routes cached' => File::exists(base_path('bootstrap/cache/routes-v7.php')),
            'Views cached' => File::exists(storage_path('framework/views')),
            'Events cached' => File::exists(base_path('bootstrap/cache/events.php')),
            'Storage linked' => File::exists(public_path('storage')),
            'Application key set' => !empty(config('app.key')),
        ];

        foreach ($optimizations as $optimization => $status) {
            $icon = $status ? '✅' : '❌';
            $this->line("{$icon} {$optimization}");
        }

        $this->newLine();
        $this->info('🎯 Next Steps:');
        $this->info('==============');
        $this->line('1. Monitor application performance');
        $this->line('2. Check error logs regularly');
        $this->line('3. Set up monitoring and alerting');
        $this->line('4. Configure backup schedules');
        $this->line('5. Test all critical functionality');
    }
}
