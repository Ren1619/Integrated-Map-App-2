<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile.
     */
    /**
     * Display the user's profile.
     */
    public function show(Request $request): View
    {
        $user = $request->user();

        // Get activity statistics
        $totalSearches = $user->searchHistories()->count();
        $savedLocationsCount = $user->savedLocations()->count();

        // Get most searched location
        $topSearch = $user->searchHistories()
            ->orderBy('search_count', 'desc')
            ->first();

        // Get recent activity
        $recentSearches = $user->searchHistories()
            ->orderBy('last_searched_at', 'desc')
            ->limit(5)
            ->get();

        $recentSavedLocations = $user->savedLocations()
            ->orderBy('last_visited_at', 'desc')
            ->limit(5)
            ->get();

        return view('profile.show', [
            'user' => $user,
            'totalSearches' => $totalSearches,
            'savedLocationsCount' => $savedLocationsCount,
            'topSearch' => $topSearch,
            'recentSearches' => $recentSearches,
            'recentSavedLocations' => $recentSavedLocations,
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}