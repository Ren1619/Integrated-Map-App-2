<?php

namespace App\Console\Commands;

use App\Models\SearchHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupDuplicateSearches extends Command
{
    protected $signature = 'search:cleanup-duplicates';
    protected $description = 'Remove duplicate geolocation search entries that were created at the same time';

    public function handle()
    {
        $this->info('🔍 Scanning for duplicate geolocation entries...');

        // Find duplicate geolocation entries (same user, location, and close timestamps)
        $duplicates = SearchHistory::query()
            ->select('user_id', 'location_name', 'latitude', 'longitude', DB::raw('DATE(last_searched_at) as search_date'))
            ->where('search_type', 'geolocation')
            ->groupBy('user_id', 'location_name', 'latitude', 'longitude', 'search_date')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($duplicates->isEmpty()) {
            $this->info('✅ No duplicate entries found!');
            return 0;
        }

        $this->info("Found {$duplicates->count()} sets of duplicates");
        $totalDeleted = 0;

        foreach ($duplicates as $duplicate) {
            // Get all entries for this duplicate set
            $entries = SearchHistory::where('user_id', $duplicate->user_id)
                ->where('latitude', $duplicate->latitude)
                ->where('longitude', $duplicate->longitude)
                ->where('search_type', 'geolocation')
                ->whereDate('last_searched_at', $duplicate->search_date)
                ->orderBy('search_count', 'desc')
                ->orderBy('last_searched_at', 'desc')
                ->get();

            if ($entries->count() > 1) {
                // Keep the first one (highest search_count and most recent)
                $keep = $entries->first();
                
                // Sum up all search counts
                $totalSearchCount = $entries->sum('search_count');
                
                // Update the kept entry with the total count
                $keep->update(['search_count' => $totalSearchCount]);
                
                // Delete the rest
                $toDelete = $entries->slice(1);
                $deletedCount = $toDelete->count();
                
                foreach ($toDelete as $entry) {
                    $entry->delete();
                }
                
                $totalDeleted += $deletedCount;
                
                $this->line("✓ Kept: {$keep->location_name} (count: {$totalSearchCount}), Deleted: {$deletedCount} duplicates");
            }
        }

        $this->info("🎉 Cleanup complete! Deleted {$totalDeleted} duplicate entries.");
        
        return 0;
    }
}