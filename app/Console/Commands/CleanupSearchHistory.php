<?php
// app/Console/Commands/CleanupSearchHistory.php

namespace App\Console\Commands;

use App\Models\SearchHistory;
use Illuminate\Console\Command;

class CleanupSearchHistory extends Command
{
    protected $signature = 'search:cleanup {--days=30 : Number of days to keep}';
    protected $description = 'Clean up old search history entries';

    public function handle()
    {
        $days = (int) $this->option('days');
        
        $this->info("Cleaning up search history older than {$days} days...");
        
        $cutoffDate = now()->subDays($days);
        
        $deletedCount = SearchHistory::where('last_searched_at', '<', $cutoffDate)
            ->delete();
        
        $this->info("✅ Deleted {$deletedCount} old search entries");
        
        // Also clean up search histories with no associated user (orphaned records)
        $orphanedCount = SearchHistory::whereDoesntHave('user')->delete();
        
        if ($orphanedCount > 0) {
            $this->info("✅ Deleted {$orphanedCount} orphaned search entries");
        }
        
        $this->info('Search history cleanup completed!');
        
        return 0;
    }
}