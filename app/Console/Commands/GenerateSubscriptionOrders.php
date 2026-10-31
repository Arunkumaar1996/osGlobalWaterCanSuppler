<?php

namespace App\Console\Commands;

use App\Services\SubscriptionService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateSubscriptionOrders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'subscriptions:generate {--date= : Target delivery date (YYYY-MM-DD), defaults to tomorrow}';

    /**
     * The console command description.
     */
    protected $description = 'Automatically generate recurring water delivery orders for active customer subscriptions.';

    /**
     * Execute the console command.
     */
    public function handle(SubscriptionService $service): int
    {
        $targetDate = $this->option('date') ?: Carbon::tomorrow()->format('Y-m-d');

        $this->info("Scanning active subscriptions due on or before {$targetDate}...");

        $count = $service->generateScheduledOrders($targetDate);

        $this->info("Successfully generated {$count} recurring orders for {$targetDate}.");

        return Command::SUCCESS;
    }
}
