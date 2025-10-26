<?php

namespace App\Http\Controllers;

use App\Models\SearchHistory;
use App\Models\SavedLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserDataController extends Controller
{
    // ==================== SEARCH HISTORY ====================
    
    /**
     * Record a search in history.
     */
    public function recordSearch(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'location_name' => 'required|string|max:255',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'address_components' => 'nullable|array',
                'search_type' => 'nullable|string|in:manual,map_click,geolocation',
            ]);

            $search = SearchHistory::recordSearch(
                Auth::id(),
                $request->location_name,
                $request->latitude,
                $request->longitude,
                $request->address_components,
                $request->search_type ?? 'manual'
            );

            return response()->json([
                'success' => true,
                'data' => $search
            ]);
        } catch (\Exception $e) {
            Log::error('Record search error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error recording search'
            ], 500);
        }
    }

    /**
     * Get user's search history.
     */
    public function getSearchHistory(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);
            $type = $request->input('type', 'recent'); // recent or popular

            $histories = $type === 'popular'
                ? SearchHistory::getMostSearched(Auth::id(), $limit)
                : SearchHistory::getRecentSearches(Auth::id(), $limit);

            return response()->json([
                'success' => true,
                'data' => $histories
            ]);
        } catch (\Exception $e) {
            Log::error('Get search history error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching search history'
            ], 500);
        }
    }

    /**
     * Clear search history.
     */
    public function clearSearchHistory(Request $request): JsonResponse
    {
        try {
            $days = $request->input('days'); // Optional: clear only old entries

            if ($days) {
                $deleted = SearchHistory::clearOldHistory(Auth::id(), $days);
            } else {
                $deleted = SearchHistory::where('user_id', Auth::id())->delete();
            }

            return response()->json([
                'success' => true,
                'message' => "Deleted {$deleted} search entries"
            ]);
        } catch (\Exception $e) {
            Log::error('Clear search history error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error clearing search history'
            ], 500);
        }
    }

    /**
     * Delete a single search entry.
     */
    public function deleteSearchEntry(SearchHistory $search): JsonResponse
    {
        try {
            if ($search->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $search->delete();

            return response()->json([
                'success' => true,
                'message' => 'Search entry deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete search entry error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting search entry'
            ], 500);
        }
    }

    // ==================== SAVED LOCATIONS ====================
    
    /**
     * Toggle saved location.
     */
    public function toggleSavedLocation(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'location_name' => 'required|string|max:255',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'address_components' => 'nullable|array',
                'emoji' => 'nullable|string|max:10',
            ]);

            $result = SavedLocation::toggle(
                Auth::id(),
                $request->name,
                $request->location_name,
                $request->latitude,
                $request->longitude,
                $request->address_components,
                $request->emoji ?? '📍'
            );

            return response()->json([
                'success' => true,
                'action' => $result['action'],
                'data' => $result['saved']
            ]);
        } catch (\Exception $e) {
            Log::error('Toggle saved location error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error toggling saved location'
            ], 500);
        }
    }

    /**
     * Get user's saved locations.
     */
    public function getSavedLocations(Request $request): JsonResponse
    {
        try {
            $orderBy = $request->input('order_by', 'sort_order'); // sort_order, recent, popular, name

            $savedLocations = SavedLocation::getUserSavedLocations(Auth::id(), $orderBy);

            return response()->json([
                'success' => true,
                'data' => $savedLocations
            ]);
        } catch (\Exception $e) {
            Log::error('Get saved locations error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching saved locations'
            ], 500);
        }
    }

    /**
     * Update saved location.
     */
    public function updateSavedLocation(Request $request, SavedLocation $saved): JsonResponse
    {
        try {
            // Ensure user owns this saved location
            if ($saved->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'emoji' => 'sometimes|string|max:10',
                'notes' => 'sometimes|nullable|string|max:1000',
                'sort_order' => 'sometimes|integer|min:0',
            ]);

            $saved->update($request->only(['name', 'emoji', 'notes', 'sort_order']));

            return response()->json([
                'success' => true,
                'data' => $saved
            ]);
        } catch (\Exception $e) {
            Log::error('Update saved location error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating saved location'
            ], 500);
        }
    }

    /**
     * Delete saved location.
     */
    public function deleteSavedLocation(SavedLocation $saved): JsonResponse
    {
        try {
            if ($saved->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $saved->delete();

            return response()->json([
                'success' => true,
                'message' => 'Saved location deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete saved location error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting saved location'
            ], 500);
        }
    }

    /**
     * Record visit to saved location.
     */
    public function recordSavedLocationVisit(SavedLocation $saved): JsonResponse
    {
        try {
            if ($saved->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $saved->recordVisit();

            return response()->json([
                'success' => true,
                'data' => $saved
            ]);
        } catch (\Exception $e) {
            Log::error('Record visit error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error recording visit'
            ], 500);
        }
    }

    /**
     * Check if location is saved.
     */
    public function checkSavedLocation(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
            ]);

            $isSaved = SavedLocation::isSaved(
                Auth::id(),
                $request->latitude,
                $request->longitude
            );

            $savedLocation = null;
            if ($isSaved) {
                $savedLocation = SavedLocation::where('user_id', Auth::id())
                    ->where('latitude', $request->latitude)
                    ->where('longitude', $request->longitude)
                    ->first();
            }

            return response()->json([
                'success' => true,
                'is_saved' => $isSaved,
                'data' => $savedLocation
            ]);
        } catch (\Exception $e) {
            Log::error('Check saved location error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking saved location'
            ], 500);
        }
    }

    /**
     * Reorder saved locations.
     */
    public function reorderSavedLocations(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'ordered_ids' => 'required|array',
                'ordered_ids.*' => 'integer|exists:saved_locations,id'
            ]);

            // Verify all IDs belong to the user
            $userLocationIds = SavedLocation::where('user_id', Auth::id())
                ->pluck('id')
                ->toArray();

            foreach ($request->ordered_ids as $id) {
                if (!in_array($id, $userLocationIds)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized: Some locations do not belong to you'
                    ], 403);
                }
            }

            SavedLocation::reorder(Auth::id(), $request->ordered_ids);

            return response()->json([
                'success' => true,
                'message' => 'Saved locations reordered successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Reorder saved locations error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error reordering saved locations'
            ], 500);
        }
    }
}