@extends('layouts.app')

@section('content')
    <!-- Meta tag for authentication check -->
    <meta name="user-authenticated" content="{{ Auth::check() ? 'true' : 'false' }}">

    <!-- Main Container with Flexbox Layout -->
    <div class="p-4 flex flex-col gap-4 h-[calc(100vh-5rem)]">

        <!-- Top Section (Map + Forecast) - 2/3 height -->
        <div class="flex gap-4 h-[66%]">

            <!-- Map Container (2/3 width) -->
            <div class="flex-[2] relative rounded-2xl shadow-2xl border-4 border-white/30 overflow-hidden h-full">
                <!-- Search Controls Overlay -->
                <div class="overlay absolute justify-end top-4 left-4 right-4 flex gap-2 items-center">
                    <div class="search-container relative flex-1 max-w-md">
                        <input type="text"
                            class="w-full px-4 py-3 border-2 border-blue-500/30 rounded-full outline-none text-sm bg-white/95 backdrop-blur-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:bg-blue-400 transition-all duration-300 shadow-lg"
                            placeholder="Search for any place worldwide..." id="searchInput" autocomplete="off">

                        <!-- Loading indicator -->
                        <div id="loadingIndicator" class="absolute right-14 top-1/2 transform -translate-y-1/2 hidden">
                            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
                        </div>

                        <!-- Clear button -->
                        <button id="clearButton"
                            class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xl hidden"
                            title="Clear search">×</button>

                        <!-- Autocomplete Dropdown -->
                        <div id="autocompleteDropdown"
                            class="absolute top-full left-0 right-0 mt-2 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-blue-100 hidden max-h-80 overflow-y-auto z-50">
                        </div>
                    </div>

                    <button
                        class="bg-blue-500 hover:bg-blue-600 active:bg-blue-700 text-white px-6 py-3 rounded-3xl font-medium transition-all duration-300 hover:scale-105 hover:shadow-lg backdrop-blur-sm shadow-lg"
                        onclick="app.searchLocation()">🔍 Search</button>
                </div>

                <!-- Map Container -->
                <div id="map" class="w-full h-full rounded-2xl"></div>
            </div>

            <!-- 7-Day Forecast Panel (1/3 width) -->
            <div class="flex-1 panel-glass flex flex-col rounded-2xl h-full overflow-hidden">
                <!-- Panel Header -->
                <div class="p-6 border-b border-blue-100 flex-shrink-0">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        📅 7-Day Forecast
                    </h2>
                    <p class="text-sm text-gray-600 mt-1" id="forecastLocation">Select a location to view forecast</p>
                </div>

                <!-- Panel Content - Scrollable -->
                <div class="flex-1 overflow-y-auto custom-scrollbar p-4">
                    <div id="extendedForecast" class="space-y-3">
                        <div class="text-center py-8 text-gray-500">
                            <div class="text-4xl mb-3">🎯</div>
                            <p class="text-sm">Select any location worldwide to view 7-day weather forecast</p>
                        </div>
                    </div>
                </div>

                <!-- Panel Footer -->
                <div class="p-4 border-t border-blue-100 bg-gray-50/50 rounded-b-2xl flex-shrink-0 mt-auto">
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>Extended Forecast</span>
                        <span id="forecastUpdated">Updated: --:--</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section - Comprehensive Weather Data - 1/3 height -->
        <div class="panel-glass flex flex-col rounded-2xl h-[33%] overflow-hidden">
            <!-- Panel Header -->
            <div class="p-4 border-b border-blue-100 flex-shrink-0">
                <div class="flex justify-between items-start gap-4">
                    <!-- Left side: Title and Location -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1 flex-wrap">
                            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2" id="weatherPanelTitle">
                                🌦️ Comprehensive Weather Data
                            </h2>

                            <!-- Save Location Button (Inline with title) -->
                            @auth
                                <button id="saveLocationBtn" onclick="app.toggleSaveLocation()"
                                    class="hidden px-3 py-1.5 bg-blue-500 hover:bg-blue-600 text-white rounded-lg shadow-sm hover:shadow-md transition-all duration-200 flex items-center gap-1.5 text-xs font-medium"
                                    title="Save this location">
                                    <span id="saveLocationIcon">📍</span>
                                    <span id="saveLocationText">Save</span>
                                </button>
                            @endauth
                        </div>
                        <p class="text-sm text-gray-600" id="locationDetails">Click on map or search for detailed weather
                            information</p>
                    </div>

                    <!-- Right side: Last Updated -->
                    <div class="text-xs text-gray-500 whitespace-nowrap">
                        <span id="lastUpdated">Updated: --:--</span>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto overflow-x-auto custom-scrollbar">
                <div class="flex gap-4 p-4 min-w-max">
                    <!-- Current Weather Section -->
                    <div class="flex-shrink-0 w-72">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            📍 Current Conditions
                        </h3>
                        <div id="currentWeatherData" class="hidden">
                            <!-- Current weather will be populated here -->
                        </div>
                        <div id="weatherPlaceholder" class="text-center py-6 text-gray-500">
                            <div class="text-3xl mb-2">🎯</div>
                            <p class="text-xs">Select location for weather data</p>
                        </div>
                    </div>

                    <!-- Multi-Level Temperature Section -->
                    <div class="flex-shrink-0 w-64">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            🌡️ Temperature
                        </h3>
                        <div id="temperatureByAltitude" class="grid grid-cols-2 gap-2">
                            <!-- Temperature data will be populated here in 2x2 grid -->
                        </div>
                    </div>

                    <!-- Multi-Level Wind Section -->
                    <div class="flex-shrink-0 w-64">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            💨 Wind
                        </h3>
                        <div id="windByAltitude" class="grid grid-cols-2 gap-2">
                            <!-- Wind data will be populated here in 2x2 grid -->
                        </div>
                    </div>

                    <!-- Soil Conditions Section -->
                    <div class="flex-shrink-0 w-64">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3 flex items-center gap-2">
                            🌱 Soil Conditions
                        </h3>
                        <div id="soilConditions" class="grid grid-cols-2 gap-2">
                            <!-- Soil data will be populated here in 2x2 grid -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enhanced CSS -->
    <style>
        .panel-glass {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .weather-card {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border: 1px solid rgba(59, 130, 246, 0.2);
            transition: all 0.3s ease;
        }

        .weather-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
        }

        .current-weather-card {
            min-height: 168px !important;
            height: 168px !important;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .altitude-card {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .soil-card {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: white;
        }

        /* ============================================
       LOADING & ANIMATIONS
       ============================================ */
    .loading-shimmer {
        background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
        background-size: 200% 100%;
        animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
        0% {
            background-position: 200% 0;
        }
        100% {
            background-position: -200% 0;
        }
    }

    /* ============================================
       SCROLLBAR STYLES
       ============================================ */
    .custom-scrollbar-thin::-webkit-scrollbar {
        width: 4px;
        height: 4px;
    }

    .custom-scrollbar-thin::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.05);
        border-radius: 2px;
    }

    .custom-scrollbar-thin::-webkit-scrollbar-thumb {
        background: rgba(59, 130, 246, 0.3);
        border-radius: 2px;
    }

    .custom-scrollbar-thin::-webkit-scrollbar-thumb:hover {
        background: rgba(59, 130, 246, 0.5);
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(59, 130, 246, 0.5);
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(59, 130, 246, 0.7);
    }

    /* Scrollbar for Firefox */
    .custom-scrollbar {
        scrollbar-width: thin;
        scrollbar-color: rgba(59, 130, 246, 0.5) rgba(0, 0, 0, 0.1);
    }

    /* ============================================
       MENU & DROPDOWN STYLES
       ============================================ */
    .menu-content {
        transform: translateY(-10px);
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    details[open] .menu-content {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
    }

    /* ============================================
       DATA GRID & METRIC CARDS
       ============================================ */
    .data-grid-compact {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }

    .metric-card-compact {
        background: rgba(59, 130, 246, 0.1);
        border: 1px solid rgba(59, 130, 246, 0.2);
        border-radius: 0.5rem;
        padding: 0.75rem;
        text-align: center;
        transition: all 0.3s ease;
    }

    .metric-card-compact:hover {
        background: rgba(59, 130, 246, 0.15);
        transform: translateY(-1px);
    }

    .metric-value-compact {
        font-size: 1.25rem;
        font-weight: bold;
        color: #1e40af;
    }

    .metric-label-compact {
        font-size: 0.7rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }

    /* Uniform Data Card Styles */
    .data-card-uniform {
        min-height: 80px;
        height: 80px;
        padding: 0.75rem;
        border-radius: 0.5rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }

    .metric-card-compact,
    .altitude-card div,
    #windByAltitude > div,
    #soilConditions > div,
    #temperatureByAltitude > div {
        min-height: 80px;
        height: 80px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
    }

    #temperatureByAltitude,
    #windByAltitude,
    #soilConditions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
        min-height: 168px;
    }

    /* ============================================
       FORECAST ITEMS
       ============================================ */
    .forecast-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem;
        border-radius: 0.75rem;
        background: rgba(249, 250, 251, 0.8);
        border: 1px solid rgba(229, 231, 235, 0.5);
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }

    .forecast-item:hover {
        background: rgba(243, 244, 246, 0.9);
        transform: translateX(2px);
    }

    /* ============================================
       WEATHER CONTENT
       ============================================ */
    .weather-content-container {
        min-height: 0;
    }

    .forecast-content {
        min-height: 0;
    }

    /* ============================================
       SAVE LOCATION BUTTON
       ============================================ */
    #saveLocationBtn {
        white-space: nowrap;
        flex-shrink: 0;
        font-size: 0.75rem;
        line-height: 1rem;
    }

    #saveLocationBtn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    #saveLocationBtn:hover:not(:disabled) {
        transform: translateY(-1px);
    }

    /* Notification Animation */
    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .notification-enter {
        animation: slideInRight 0.3s ease-out;
    }

    /* ============================================
       WEATHER LAYER STYLES
       ============================================ */

    /* Weather Layer Toggle */
    .weather-layer-toggle:checked + div {
        background-color: #3b82f6;
    }

    .weather-layer-toggle:checked + div .dot {
        transform: translateX(100%);
    }

    /* Weather Tooltips */
    .temp-tooltip {
        background: rgba(0, 0, 0, 0.8) !important;
        color: white !important;
        border: none !important;
        border-radius: 6px !important;
        font-weight: 600 !important;
        font-size: 12px !important;
        padding: 4px 8px !important;
    }

    .precip-tooltip {
        background: rgba(59, 130, 246, 0.9) !important;
        color: white !important;
        border: none !important;
        border-radius: 6px !important;
        font-weight: 600 !important;
        font-size: 11px !important;
        padding: 3px 6px !important;
    }

    /* Wind Arrow Animations */
    .wind-arrow {
        position: absolute;
        pointer-events: none;
        z-index: 1000;
        animation: windPulse 2s ease-in-out infinite;
    }

    .wind-arrow div {
        color: #1d4ed8;
        filter: drop-shadow(0 0 2px rgba(255, 255, 255, 0.8));
        font-weight: bold;
        transition: all 0.3s ease;
    }

    @keyframes windPulse {
        0%, 100% { 
            opacity: 0.8; 
        }
        50% { 
            opacity: 1; 
        }
    }

    /* Temperature Layer Styles */
    .temp-cold {
        background: radial-gradient(circle, rgba(59, 130, 246, 0.7) 0%, rgba(59, 130, 246, 0.2) 70%, transparent 100%) !important;
        animation: tempGlow 3s ease-in-out infinite;
    }

    .temp-cool {
        background: radial-gradient(circle, rgba(34, 197, 94, 0.7) 0%, rgba(34, 197, 94, 0.2) 70%, transparent 100%) !important;
        animation: tempGlow 3s ease-in-out infinite;
    }

    .temp-warm {
        background: radial-gradient(circle, rgba(251, 191, 36, 0.7) 0%, rgba(251, 191, 36, 0.2) 70%, transparent 100%) !important;
        animation: tempGlow 3s ease-in-out infinite;
    }

    .temp-hot {
        background: radial-gradient(circle, rgba(239, 68, 68, 0.7) 0%, rgba(239, 68, 68, 0.2) 70%, transparent 100%) !important;
        animation: tempGlow 3s ease-in-out infinite;
    }

    @keyframes tempGlow {
        0%, 100% { 
            transform: scale(1); 
            opacity: 0.7; 
        }
        50% { 
            transform: scale(1.1); 
            opacity: 0.9; 
        }
    }

    /* Precipitation Layer Styles */
    .precip-light {
        background: radial-gradient(circle, rgba(34, 197, 94, 0.6) 0%, rgba(34, 197, 94, 0.1) 80%, transparent 100%) !important;
        animation: precipFall 2s linear infinite;
    }

    .precip-moderate {
        background: radial-gradient(circle, rgba(251, 191, 36, 0.7) 0%, rgba(251, 191, 36, 0.2) 80%, transparent 100%) !important;
        animation: precipFall 1.5s linear infinite;
    }

    .precip-heavy {
        background: radial-gradient(circle, rgba(239, 68, 68, 0.8) 0%, rgba(239, 68, 68, 0.3) 80%, transparent 100%) !important;
        animation: precipFall 1s linear infinite;
    }

    @keyframes precipFall {
        0% { 
            opacity: 0.3; 
        }
        50% { 
            opacity: 0.8; 
        }
        100% { 
            opacity: 0.3; 
        }
    }

    /* Weather Info Panel Enhancements */
    #weatherInfo {
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        animation: slideInFromBottom 0.5s ease-out;
    }

    @keyframes slideInFromBottom {
        0% {
            transform: translateY(100px);
            opacity: 0;
        }
        100% {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Weather Layer Control Panel */
    .weather-layer-panel {
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    /* Enhanced Toggle Switches */
    .weather-layer-toggle + div {
        position: relative;
        width: 2.5rem;
        height: 1.5rem;
        background-color: #d1d5db;
        border-radius: 9999px;
        transition: background-color 0.3s ease;
        cursor: pointer;
    }

    .weather-layer-toggle + div .dot {
        position: absolute;
        top: 0.125rem;
        left: 0.125rem;
        width: 1.25rem;
        height: 1.25rem;
        background-color: white;
        border-radius: 50%;
        transition: transform 0.3s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .weather-layer-toggle:checked + div {
        background-color: #3b82f6;
    }

    .weather-layer-toggle:checked + div .dot {
        transform: translateX(1rem);
    }

    /* Opacity Slider */
    #layerOpacity {
        -webkit-appearance: none;
        appearance: none;
        height: 6px;
        background: linear-gradient(to right, #e5e7eb, #3b82f6);
        border-radius: 3px;
        outline: none;
    }

    #layerOpacity::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        background: #3b82f6;
        border-radius: 50%;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(59, 130, 246, 0.4);
        transition: all 0.3s ease;
    }

    #layerOpacity::-webkit-slider-thumb:hover {
        transform: scale(1.1);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.6);
    }

    #layerOpacity::-moz-range-thumb {
        width: 20px;
        height: 20px;
        background: #3b82f6;
        border-radius: 50%;
        cursor: pointer;
        border: none;
        box-shadow: 0 2px 6px rgba(59, 130, 246, 0.4);
    }

    /* Weather Loading States */
    .weather-loading {
        position: relative;
        overflow: hidden;
    }

    .weather-loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.4),
            transparent
        );
        animation: shimmer 1.5s infinite;
    }

    /* ============================================
       SEARCH & AUTOCOMPLETE STYLES
       ============================================ */
    .search-container {
        position: relative;
        z-index: 1000;
    }

    .autocomplete-dropdown {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .autocomplete-item {
        transition: all 0.2s ease;
    }

    .autocomplete-item:hover {
        transform: translateX(2px);
        background: linear-gradient(to right, #dbeafe, #bfdbfe);
    }

    .autocomplete-item.selected {
        background: linear-gradient(to right, #3b82f6, #1d4ed8);
        color: white;
        transform: translateX(2px);
    }

    .autocomplete-item.selected .text-gray-600 {
        color: rgba(255, 255, 255, 0.8);
    }

    .autocomplete-item.selected .text-blue-500 {
        color: rgba(255, 255, 255, 0.9);
    }

    /* ============================================
       OVERLAY STYLES
       ============================================ */
    .overlay {
        z-index: 9999 !important;
    }

    /* ============================================
       BODY & CONTAINER OVERRIDES
       ============================================ */
    body {
        overflow-x: hidden;
        overflow-y: auto;
    }

    /* ============================================
       TABLET RESPONSIVE (768px - 1024px)
       ============================================ */
    @media (max-width: 1024px) {
        /* Adjust main container padding */
        .p-4 {
            padding: 0.75rem;
        }

        /* Map and Forecast Section */
        .h-\[66\%\] {
            height: auto;
            min-height: 400px;
        }

        /* Weather Data Section */
        .h-\[33\%\] {
            height: auto;
            min-height: 300px;
        }

        /* Map Container */
        .flex-\[2\] {
            flex: 1;
            min-height: 400px;
        }

        /* Forecast Panel */
        .flex-1 {
            min-height: 400px;
        }

        /* Search Input */
        .search-container {
            max-width: 100%;
        }

        /* Reduce card padding */
        .weather-card {
            padding: 0.75rem;
        }

        /* Forecast items smaller */
        .forecast-item {
            padding: 0.5rem;
            font-size: 0.875rem;
        }
    }

    /* ============================================
       MOBILE RESPONSIVE (max-width: 768px)
       ============================================ */
    @media (max-width: 768px) {
        /* Container Adjustments */
        .p-4 {
            padding: 0.5rem;
        }

        .h-\[calc\(100vh-5rem\)\] {
            height: calc(100vh - 3rem);
        }

        /* Layout Changes */
        .h-\[66\%\],
        .h-\[33\%\] {
            height: auto;
        }

        /* Stack Map and Forecast Vertically */
        .h-\[66\%\] {
            flex-direction: column;
            min-height: 60vh;
        }

        /* Map Section */
        .flex-\[2\] {
            flex: none;
            height: 50vh;
            min-height: 300px;
        }

        /* Forecast Panel */
        .flex-1 {
            height: auto;
            min-height: 300px;
        }

        /* Weather Data Section */
        .h-\[33\%\] {
            min-height: 250px;
        }

        /* Search Overlay */
        .overlay {
            flex-direction: column;
            gap: 0.5rem;
            padding: 0.75rem;
        }

        .search-container {
            width: 100%;
            max-width: 100%;
        }

        .search-container input {
            font-size: 0.875rem;
            padding: 0.625rem 0.75rem;
        }

        /* Hide search button on mobile */
        .overlay button {
            display: none;
        }

        /* Weather Cards - Full Width */
        .w-72,
        .w-64 {
            width: 100%;
            max-width: none;
        }

        /* Reduce card heights on mobile */
        .current-weather-card {
            min-height: 148px !important;
            height: 148px !important;
        }

        .data-card-uniform,
        .metric-card-compact,
        .altitude-card div,
        #windByAltitude > div,
        #soilConditions > div,
        #temperatureByAltitude > div {
            min-height: 70px;
            height: 70px;
            padding: 0.5rem;
        }

        #temperatureByAltitude,
        #windByAltitude,
        #soilConditions {
            min-height: 148px;
        }

        /* Font Size Adjustments */
        .metric-value-compact {
            font-size: 1rem;
        }

        .metric-label-compact {
            font-size: 0.625rem;
        }

        /* Header Text */
        #weatherPanelTitle {
            font-size: 1rem;
        }

        #locationDetails {
            font-size: 0.75rem;
        }

        /* Forecast Items */
        .forecast-item {
            padding: 0.5rem;
            font-size: 0.75rem;
            gap: 0.5rem;
        }

        .forecast-item .text-xl {
            font-size: 1.25rem;
        }

        /* Horizontal Scroll for Weather Data */
        .flex.gap-4.p-4.min-w-max {
            padding: 0.5rem;
            gap: 0.75rem;
        }

        /* Section Widths */
        .flex-shrink-0 {
            width: 85vw;
            min-width: 85vw;
        }

        /* Panel Headers */
        .p-6 {
            padding: 0.75rem;
        }

        /* Save Location Button */
        #saveLocationBtn {
            font-size: 0.625rem;
            padding: 0.375rem 0.5rem;
        }

        /* Autocomplete Dropdown */
        .autocomplete-dropdown {
            max-height: 60vh;
        }

        .autocomplete-item {
            padding: 0.75rem;
            font-size: 0.875rem;
        }

        /* Flex wrapping */
        .flex-nowrap {
            flex-wrap: wrap;
        }
    }

    /* ============================================
       EXTRA SMALL MOBILE (max-width: 480px)
       ============================================ */
    @media (max-width: 480px) {
        /* Further reduce spacing */
        .gap-4 {
            gap: 0.5rem;
        }

        .gap-3 {
            gap: 0.375rem;
        }

        /* Map height */
        .flex-\[2\] {
            height: 40vh;
            min-height: 250px;
        }

        /* Forecast panel */
        .flex-1 {
            min-height: 250px;
        }

        /* Text sizes */
        .text-xl {
            font-size: 1rem;
        }

        .text-lg {
            font-size: 0.9375rem;
        }

        .text-sm {
            font-size: 0.75rem;
        }

        .text-xs {
            font-size: 0.625rem;
        }

        /* Weather data cards */
        .current-weather-card {
            min-height: 128px !important;
            height: 128px !important;
        }

        .data-card-uniform,
        .metric-card-compact,
        .altitude-card div,
        #windByAltitude > div,
        #soilConditions > div,
        #temperatureByAltitude > div {
            min-height: 60px;
            height: 60px;
            padding: 0.375rem;
        }

        #temperatureByAltitude,
        #windByAltitude,
        #soilConditions {
            min-height: 128px;
            gap: 0.375rem;
        }

        /* Section widths */
        .flex-shrink-0 {
            width: 90vw;
            min-width: 90vw;
        }

        /* Search input */
        .search-container input {
            font-size: 0.8125rem;
            padding: 0.5rem;
        }

        /* Forecast items */
        .forecast-item {
            padding: 0.375rem;
        }
    }

    /* ============================================
       LANDSCAPE MODE
       ============================================ */
    @media (max-height: 600px) and (orientation: landscape) {
        /* Optimize for landscape on mobile */
        .h-\[calc\(100vh-5rem\)\] {
            height: calc(100vh - 2rem);
        }

        .flex-\[2\] {
            height: 60vh;
        }

        .h-\[66\%\] {
            flex-direction: row;
            height: 60vh;
        }

        .h-\[33\%\] {
            height: 40vh;
        }

        /* Reduce vertical padding */
        .py-4,
        .py-6 {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
    }

    /* ============================================
       TOUCH DEVICE OPTIMIZATIONS
       ============================================ */
    @media (hover: none) and (pointer: coarse) {
        /* Increase touch targets on mobile */
        button,
        a,
        .forecast-item {
            min-height: 44px;
        }

        /* Larger tap areas for map controls */
        .leaflet-control-zoom a {
            width: 44px;
            height: 44px;
            line-height: 44px;
        }

        /* Remove hover effects on touch devices */
        .weather-card:hover,
        .forecast-item:hover,
        .data-card-uniform:hover,
        .metric-card-compact:hover {
            transform: none;
        }
    }

    /* ============================================
       HIGH CONTRAST MODE SUPPORT
       ============================================ */
    @media (prefers-contrast: high) {
        .temp-tooltip,
        .precip-tooltip {
            background: #000000 !important;
            color: #ffffff !important;
            border: 2px solid #ffffff !important;
        }

        .wind-arrow div {
            color: #000000;
            filter: drop-shadow(0 0 2px #ffffff);
        }
    }

    /* ============================================
       REDUCED MOTION SUPPORT
       ============================================ */
    @media (prefers-reduced-motion: reduce) {
        .wind-arrow,
        .temp-cold,
        .temp-cool,
        .temp-warm,
        .temp-hot,
        .precip-light,
        .precip-moderate,
        .precip-heavy {
            animation: none;
        }

        .weather-layer-toggle + div,
        .weather-layer-toggle + div .dot,
        #layerOpacity::-webkit-slider-thumb {
            transition: none;
        }

        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }

    /* ============================================
       DARK MODE SUPPORT
       ============================================ */
    @media (prefers-color-scheme: dark) {
        .glass-effect,
        .panel-glass {
            background: rgba(31, 41, 55, 0.95);
            border: 1px solid rgba(75, 85, 99, 0.3);
        }

        .weather-layer-panel {
            background: rgba(31, 41, 55, 0.95);
            border: 1px solid rgba(75, 85, 99, 0.3);
        }

        #weatherInfo {
            background: rgba(31, 41, 55, 0.95);
            border: 1px solid rgba(75, 85, 99, 0.3);
            color: #f9fafb;
        }

        .temp-tooltip {
            background: rgba(31, 41, 55, 0.95) !important;
        }

        .weather-card {
            background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        .forecast-item {
            background: rgba(31, 41, 55, 0.8);
            border: 1px solid rgba(75, 85, 99, 0.5);
        }

        .metric-card-compact {
            background: rgba(59, 130, 246, 0.2);
            border: 1px solid rgba(59, 130, 246, 0.3);
        }
    }

    /* ============================================
       PRINT STYLES
       ============================================ */
    @media print {
        .overlay,
        #weatherInfo,
        .weather-layer-panel,
        aside,
        nav,
        footer,
        button {
            display: none !important;
        }

        #map {
            border-radius: 0 !important;
            page-break-inside: avoid;
        }

        .panel-glass {
            page-break-inside: avoid;
            border: 1px solid #000;
            box-shadow: none;
        }

        body {
            background: white;
        }
    }
        </style>

        <!-- Enhanced JavaScript -->
        <script>
            class EnhancedWeatherMapApp {
                constructor() {
                    this.map = null;
                    this.currentMarker = null;
                    this.currentLat = null;
                    this.currentLng = null;
                    this.currentLocationDetails = null;
                    this.isSaved = false;
                    this.savedLocationId = null;
                    this.mapLayers = {};
                    this.suggestions = [];
                    this.selectedIndex = -1;
                    this.debounceTimer = null;
                    this.cache = new Map();
                    this.isAuthenticated = document.querySelector('meta[name="user-authenticated"]')?.content === 'true';

                    this.init();
                }

                init() {
                    this.initMap();
                    this.bindEvents();
                    this.initAutocomplete();
                    this.setActiveLayerButton('standard');
                    this.updateLastUpdated();
                }

                initMap() {
                    this.map = L.map('map').setView([7.1907, 125.4553], 12);

                    this.mapLayers = {
                        standard: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors',
                            maxZoom: 18
                        }),
                        cycle: L.tileLayer('https://{s}.tile-cyclosm.openstreetmap.fr/cyclosm/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors, CyclOSM',
                            maxZoom: 18
                        }),
                        transport: L.tileLayer('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', {
                            attribution: '© OpenStreetMap contributors, Humanitarian OSM Team',
                            maxZoom: 18
                        })
                    };

                    this.mapLayers.standard.addTo(this.map);

                    this.map.on('click', (e) => {
                        this.handleMapClick(e.latlng);
                    });
                }

                initAutocomplete() {
                    const input = document.getElementById('searchInput');
                    const clearButton = document.getElementById('clearButton');

                    input.addEventListener('input', (e) => this.handleAutocompleteInput(e));
                    input.addEventListener('keydown', (e) => this.handleAutocompleteKeydown(e));
                    clearButton.addEventListener('click', () => this.clearSearch());
                }

                handleAutocompleteInput(e) {
                    const query = e.target.value.trim();
                    const clearButton = document.getElementById('clearButton');

                    if (query) {
                        clearButton.classList.remove('hidden');
                    } else {
                        clearButton.classList.add('hidden');
                    }

                    clearTimeout(this.debounceTimer);

                    if (query.length < 2) {
                        this.hideAutocompleteDropdown();
                        return;
                    }

                    this.debounceTimer = setTimeout(() => {
                        this.searchAutocompleteLocations(query);
                    }, 300);
                }

                handleAutocompleteKeydown(e) {
                    const dropdown = document.getElementById('autocompleteDropdown');
                    if (!dropdown.classList.contains('hidden')) {
                        switch (e.key) {
                            case 'ArrowDown':
                                e.preventDefault();
                                this.navigateAutocompleteDown();
                                break;
                            case 'ArrowUp':
                                e.preventDefault();
                                this.navigateAutocompleteUp();
                                break;
                            case 'Enter':
                                e.preventDefault();
                                this.selectCurrentAutocompleteSuggestion();
                                break;
                            case 'Escape':
                                this.hideAutocompleteDropdown();
                                break;
                        }
                    }
                }

                async searchAutocompleteLocations(query) {
                    if (this.cache.has(query)) {
                        this.displayAutocompleteSuggestions(this.cache.get(query));
                        return;
                    }

                    this.showAutocompleteLoading(true);

                    try {
                        const response = await fetch('/weather/autocomplete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ query })
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            this.cache.set(query, result.data);
                            this.displayAutocompleteSuggestions(result.data);
                        } else {
                            console.error('Autocomplete API error:', result);
                            this.displayAutocompleteError(result.message || 'Search failed');
                        }
                    } catch (error) {
                        console.error('Autocomplete network error:', error);
                        this.displayAutocompleteError('Network error occurred');
                    } finally {
                        this.showAutocompleteLoading(false);
                    }
                }

                displayAutocompleteError(message) {
                    const dropdown = document.getElementById('autocompleteDropdown');
                    dropdown.innerHTML = `
                        <div class="p-4 text-center text-red-500">
                            <div class="text-2xl mb-2">⚠️</div>
                            <p class="text-sm">${message}</p>
                        </div>
                    `;
                    this.showAutocompleteDropdown();
                }

                displayAutocompleteSuggestions(suggestions) {
                    this.suggestions = suggestions;
                    const dropdown = document.getElementById('autocompleteDropdown');

                    if (suggestions.length === 0) {
                        dropdown.innerHTML = '<div class="p-4 text-center text-gray-500">No results found</div>';
                        this.showAutocompleteDropdown();
                        return;
                    }

                    let html = '';
                    suggestions.forEach((suggestion, index) => {
                        const population = suggestion.population ?
                            `<span class="text-xs text-gray-500">${this.formatPopulation(suggestion.population)}</span>` : '';

                        html += `
                            <div class="autocomplete-item p-3 cursor-pointer hover:bg-blue-50" 
                                 onclick="app.selectAutocompleteSuggestion(${index})">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-semibold text-gray-800">${suggestion.name}</div>
                                        <div class="text-sm text-gray-600">${suggestion.display_name}</div>
                                        ${population}
                                    </div>
                                    <div class="text-xl">🌍</div>
                                </div>
                            </div>
                        `;
                    });

                    dropdown.innerHTML = html;
                    this.showAutocompleteDropdown();
                }

                formatPopulation(population) {
                    if (population > 1000000) {
                        return `${(population / 1000000).toFixed(1)}M people`;
                    } else if (population > 1000) {
                        return `${(population / 1000).toFixed(0)}K people`;
                    }
                    return `${population} people`;
                }

                selectAutocompleteSuggestion(index) {
                    const suggestion = this.suggestions[index];
                    const input = document.getElementById('searchInput');

                    input.value = suggestion.name;
                    this.hideAutocompleteDropdown();

                    this.map.setView([suggestion.lat, suggestion.lng], 12);

                    const locationDetails = {
                        name: suggestion.name,
                        admin1: suggestion.admin1,
                        country: suggestion.country,
                        display_name: suggestion.display_name
                    };

                    this.handleLocationSelected(suggestion.lat, suggestion.lng, locationDetails);

                    setTimeout(() => {
                        input.value = '';
                        document.getElementById('clearButton').classList.add('hidden');
                    }, 2000);
                }

                navigateAutocompleteDown() {
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.suggestions.length - 1);
                    this.updateAutocompleteSelection();
                }

                navigateAutocompleteUp() {
                    this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                    this.updateAutocompleteSelection();
                }

                updateAutocompleteSelection() {
                    const items = document.querySelectorAll('.autocomplete-item');
                    items.forEach((item, index) => {
                        if (index === this.selectedIndex) {
                            item.classList.add('selected');
                        } else {
                            item.classList.remove('selected');
                        }
                    });
                }

                selectCurrentAutocompleteSuggestion() {
                    if (this.selectedIndex >= 0) {
                        this.selectAutocompleteSuggestion(this.selectedIndex);
                    }
                }

                clearSearch() {
                    const input = document.getElementById('searchInput');
                    input.value = '';
                    this.hideAutocompleteDropdown();
                    document.getElementById('clearButton').classList.add('hidden');
                }

                showAutocompleteDropdown() {
                    document.getElementById('autocompleteDropdown').classList.remove('hidden');
                }

                hideAutocompleteDropdown() {
                    document.getElementById('autocompleteDropdown').classList.add('hidden');
                    this.selectedIndex = -1;
                }

                showAutocompleteLoading(show) {
                    const loading = document.getElementById('loadingIndicator');
                    const clear = document.getElementById('clearButton');

                    if (show) {
                        loading.classList.remove('hidden');
                        clear.classList.add('hidden');
                    } else {
                        loading.classList.add('hidden');
                        if (document.getElementById('searchInput').value.trim()) {
                            clear.classList.remove('hidden');
                        }
                    }
                }

                bindEvents() {
                    document.getElementById('searchInput').addEventListener('keypress', (e) => {
                        if (e.key === 'Enter') {
                            this.searchLocation();
                        }
                    });

                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                const lat = position.coords.latitude;
                                const lng = position.coords.longitude;
                                this.map.setView([lat, lng], 12);
                                this.handleLocationSelected(lat, lng, null);
                            },
                            (error) => console.log('Geolocation error:', error)
                        );
                    }
                }

                changeMapLayer(layerType) {
                    this.map.eachLayer((layer) => {
                        if (layer !== this.currentMarker &&
                            layer.options && layer.options.attribution) {
                            this.map.removeLayer(layer);
                        }
                    });

                    this.mapLayers[layerType].addTo(this.map);
                    this.setActiveLayerButton(layerType);
                }

                setActiveLayerButton(layerType) {
                    document.querySelectorAll('[id$="Btn"]').forEach(btn => {
                        btn.classList.remove('bg-blue-700');
                        btn.classList.add('bg-blue-500');
                    });
                    const targetBtn = document.getElementById(layerType + 'Btn');
                    if (targetBtn) {
                        targetBtn.classList.add('bg-blue-700');
                        targetBtn.classList.remove('bg-blue-500');
                    }
                }

                async searchLocation() {
                    const query = document.getElementById('searchInput').value.trim();
                    if (!query) return;

                    try {
                        const response = await fetch('/search', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ query })
                        });

                        const result = await response.json();

                        if (response.ok && result.success) {
                            const { lat, lng, location_details } = result.data;
                            this.map.setView([lat, lng], 12);
                            this.handleLocationSelected(lat, lng, location_details);
                            document.getElementById('searchInput').value = '';
                            this.hideAutocompleteDropdown();
                        } else {
                            console.error('Search error:', result);
                            alert(result.message || 'Location not found');
                        }
                    } catch (error) {
                        console.error('Search network error:', error);
                        alert('Network error occurred while searching');
                    }
                }

                handleMapClick(latlng) {
                    const { lat, lng } = latlng;
                    this.handleLocationSelected(lat, lng, null);
                }

                async handleLocationSelected(lat, lng, locationDetails = null) {
                    this.currentLat = lat;
                    this.currentLng = lng;
                    this.currentLocationDetails = locationDetails;

                    if (this.currentMarker) {
                        this.map.removeLayer(this.currentMarker);
                    }

                    this.currentMarker = L.circleMarker([lat, lng], {
                        radius: 12,
                        fillColor: '#3b82f6',
                        color: 'white',
                        weight: 3,
                        fillOpacity: 0.9
                    }).addTo(this.map);

                    await this.updateLocationDetailsAndTitle(locationDetails, lat, lng);

                    if (this.isAuthenticated) {
                        await this.checkIfLocationSaved();
                    }

                    this.showLoadingStates();
                    await this.getEnhancedWeatherData(lat, lng);
                    this.updateLastUpdated();
                }

                async updateLocationDetailsAndTitle(locationDetails, lat, lng) {
                    const titleElement = document.getElementById('weatherPanelTitle');
                    const detailsElement = document.getElementById('locationDetails');
                    const forecastLocationElement = document.getElementById('forecastLocation');
                    const saveButton = document.getElementById('saveLocationBtn');

                    if (saveButton) {
                        if (this.isAuthenticated) {
                            saveButton.classList.remove('hidden');
                        } else {
                            saveButton.classList.add('hidden');
                        }
                    }

                    if (locationDetails && locationDetails.name) {
                        const parts = [];
                        if (locationDetails.name) parts.push(locationDetails.name);
                        if (locationDetails.admin1) parts.push(locationDetails.admin1);
                        if (locationDetails.country) parts.push(locationDetails.country);

                        const locationName = parts.join(', ');
                        titleElement.innerHTML = `🌦️ ${locationName}`;
                        detailsElement.textContent = locationName;
                        forecastLocationElement.textContent = locationName;
                        return;
                    }

                    const coordsText = `${lat.toFixed(6)}°, ${lng.toFixed(6)}°`;
                    titleElement.innerHTML = `🌦️ Loading location...`;
                    detailsElement.textContent = `Coordinates: ${coordsText}`;
                    forecastLocationElement.textContent = 'Loading...';

                    try {
                        const response = await fetch('/weather/location-name', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                lat: parseFloat(lat),
                                lng: parseFloat(lng)
                            })
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        const result = await response.json();

                        if (result.success && result.data && result.data.location_name) {
                            titleElement.innerHTML = `🌦️ ${result.data.location_name}`;

                            const fullAddress = result.data.full_address || result.data.location_name;
                            detailsElement.innerHTML = `
                                <div class="text-sm">
                                    <div class="font-medium">${fullAddress}</div>
                                    ${result.data.address_components && result.data.address_components.barangay ?
                                    `<div class="text-xs text-gray-500 mt-1">Barangay: ${result.data.address_components.barangay}</div>` : ''}
                                </div>
                            `;

                            forecastLocationElement.textContent = result.data.location_name;

                            this.currentLocationDetails = {
                                name: result.data.location_name,
                                display_name: result.data.display_name,
                                full_address: fullAddress,
                                address_components: result.data.address_components
                            };

                            return;
                        }
                    } catch (error) {
                        console.error('Reverse geocoding error:', error);
                    }

                    titleElement.innerHTML = `🌦️ Location: ${coordsText}`;
                    detailsElement.textContent = `Coordinates: ${coordsText}`;
                    forecastLocationElement.textContent = `Location: ${coordsText}`;
                }

                async checkIfLocationSaved() {
                    if (!this.isAuthenticated || !this.currentLat || !this.currentLng) {
                        return;
                    }

                    try {
                        const response = await fetch('/user/saved-locations/check', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                latitude: this.currentLat,
                                longitude: this.currentLng
                            })
                        });

                        const result = await response.json();

                        if (result.success) {
                            this.isSaved = result.is_saved;
                            this.savedLocationId = result.data?.id || null;
                            this.updateSaveButton();
                        }
                    } catch (error) {
                        console.error('Error checking saved location:', error);
                    }
                }

                updateSaveButton() {
                    const saveBtn = document.getElementById('saveLocationBtn');
                    const saveIcon = document.getElementById('saveLocationIcon');
                    const saveText = document.getElementById('saveLocationText');

                    if (!saveBtn) return;

                    if (this.isSaved) {
                        saveBtn.classList.remove('bg-blue-500', 'hover:bg-blue-600');
                        saveBtn.classList.add('bg-yellow-500', 'hover:bg-yellow-600');
                        if (saveIcon) saveIcon.textContent = '⭐';
                        if (saveText) saveText.textContent = 'Saved';
                    } else {
                        saveBtn.classList.remove('bg-yellow-500', 'hover:bg-yellow-600');
                        saveBtn.classList.add('bg-blue-500', 'hover:bg-blue-600');
                        if (saveIcon) saveIcon.textContent = '📍';
                        if (saveText) saveText.textContent = 'Save';
                    }
                }

                async toggleSaveLocation() {
                    if (!this.isAuthenticated) {
                        this.showNotification('Please log in to save locations', 'warning');
                        return;
                    }

                    if (!this.currentLat || !this.currentLng) {
                        this.showNotification('No location selected', 'error');
                        return;
                    }

                    const saveBtn = document.getElementById('saveLocationBtn');
                    const originalHTML = saveBtn.innerHTML;

                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<span class="animate-spin">⏳</span> Saving...';

                    try {
                        const locationName = this.currentLocationDetails?.name || 
                                            `Location ${this.currentLat.toFixed(4)}, ${this.currentLng.toFixed(4)}`;

                        const response = await fetch('/user/saved-locations/toggle', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                name: locationName,
                                location_name: locationName,
                                latitude: this.currentLat,
                                longitude: this.currentLng,
                                address_components: this.currentLocationDetails?.address_components || null,
                                emoji: '📍'
                            })
                        });

                        const result = await response.json();

                        if (result.success) {
                            this.isSaved = result.action === 'added';
                            this.savedLocationId = result.data?.id || null;
                            this.updateSaveButton();

                            const message = result.action === 'added' 
                                ? 'Location saved successfully!' 
                                : 'Location removed from saved';
                            this.showNotification(message, 'success');
                        } else {
                            throw new Error(result.message || 'Failed to save location');
                        }
                    } catch (error) {
                        console.error('Error toggling saved location:', error);
                        this.showNotification('Failed to save location. Please try again.', 'error');
                        saveBtn.innerHTML = originalHTML;
                    } finally {
                        saveBtn.disabled = false;
                        if (!saveBtn.innerHTML.includes('⭐') && !saveBtn.innerHTML.includes('📍')) {
                            saveBtn.innerHTML = originalHTML;
                        }
                    }
                }

                showNotification(message, type = 'info') {
                    const notification = document.createElement('div');
                    notification.className = `fixed top-4 right-4 z-[10000] px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300 translate-x-0`;

                    const colors = {
                        success: 'bg-green-500 text-white',
                        error: 'bg-red-500 text-white',
                        warning: 'bg-yellow-500 text-white',
                        info: 'bg-blue-500 text-white'
                    };

                    notification.className += ` ${colors[type] || colors.info}`;

                    const icons = {
                        success: '✅',
                        error: '❌',
                        warning: '⚠️',
                        info: 'ℹ️'
                    };

                    notification.innerHTML = `
                        <div class="flex items-center gap-2">
                            <span class="text-xl">${icons[type] || icons.info}</span>
                            <span class="font-medium">${message}</span>
                        </div>
                    `;

                    document.body.appendChild(notification);

                    setTimeout(() => {
                        notification.style.transform = 'translateX(0)';
                    }, 10);

                    setTimeout(() => {
                        notification.style.transform = 'translateX(400px)';
                        setTimeout(() => {
                            document.body.removeChild(notification);
                        }, 300);
                    }, 3000);
                }

                showLoadingStates() {
                    document.getElementById('weatherPlaceholder').classList.add('hidden');
                    document.getElementById('currentWeatherData').innerHTML = this.getLoadingCard();
                    document.getElementById('currentWeatherData').classList.remove('hidden');

                    document.getElementById('temperatureByAltitude').innerHTML = this.getLoadingGrid();
                    document.getElementById('windByAltitude').innerHTML = this.getLoadingGrid();
                    document.getElementById('soilConditions').innerHTML = this.getLoadingGrid();
                    document.getElementById('extendedForecast').innerHTML = this.getForecastLoading();
                }

                getLoadingCard() {
                    return `
                        <div class="current-weather-card rounded-xl p-4 loading-shimmer">
                            <div class="animate-pulse">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="h-4 bg-white/30 rounded w-24"></div>
                                    <div class="h-8 bg-white/30 rounded-full w-8"></div>
                                </div>
                                <div class="h-8 bg-white/30 rounded w-20 mb-3"></div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="h-12 bg-white/30 rounded"></div>
                                    <div class="h-12 bg-white/30 rounded"></div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                getLoadingGrid() {
                    return Array(4).fill(0).map(() => `
                        <div class="metric-card-compact animate-pulse">
                            <div class="h-6 bg-gray-300 rounded mb-2"></div>
                            <div class="h-3 bg-gray-300 rounded"></div>
                        </div>
                    `).join('');
                }

                getForecastLoading() {
                    return Array(7).fill(0).map(() => `
                        <div class="forecast-item animate-pulse">
                            <div class="flex items-center gap-3">
                                <div class="h-6 w-6 bg-gray-300 rounded"></div>
                                <div class="h-3 bg-gray-300 rounded w-16"></div>
                            </div>
                            <div class="h-4 bg-gray-300 rounded w-12"></div>
                        </div>
                    `).join('');
                }

                async getEnhancedWeatherData(lat, lng) {
                    try {
                        const response = await fetch('/weather/data', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ lat, lng })
                        });

                        const result = await response.json();
                        if (result.success) {
                            this.displayEnhancedWeatherData(result.data.weather);
                        } else {
                            this.showWeatherError();
                        }
                    } catch (error) {
                        console.error('Error fetching weather data:', error);
                        this.showWeatherError();
                    }
                }

                displayEnhancedWeatherData(weatherData) {
                    if (!weatherData || !weatherData.current) {
                        this.showWeatherError();
                        return;
                    }

                    this.displayCurrentWeather(weatherData.current);
                    this.displayTemperatureByAltitude(weatherData.current);
                    this.displayWindByAltitude(weatherData.current);
                    this.displaySoilConditions(weatherData.hourly);
                    this.displayExtendedForecast(weatherData.daily);
                }

                displayCurrentWeather(current) {
                    const container = document.getElementById('currentWeatherData');
                    const weatherEmoji = this.getWeatherEmoji(current.weather_code);
                    const temp = Math.round(current.temperature_2m);
                    const feelsLike = Math.round(current.apparent_temperature);

                    container.innerHTML = `
                        <div class="current-weather-card bg-blue-500 rounded-xl p-4" style="min-height: 168px; height: 168px;">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="text-4xl font-bold text-white">${temp}°C</div>
                                    <div>
                                        <h4 class="text-sm font-semibold text-white">Current</h4>
                                        <p class="text-blue-100 text-xs">${new Date().toLocaleTimeString()}</p>
                                    </div>
                                </div>
                                <div class="text-3xl">${weatherEmoji}</div>
                            </div>

                            <div class="mb-2">
                                <div class="text-blue-100 text-xs">Feels like ${feelsLike}°C</div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div class="bg-white/20 rounded-lg p-2 text-center">
                                    <div class="text-white font-bold text-sm">${current.relative_humidity_2m}%</div>
                                    <div class="text-blue-100 text-xs">Humidity</div>
                                </div>
                                <div class="bg-white/20 rounded-lg p-2 text-center">
                                    <div class="text-white font-bold text-sm">${Math.round(current.surface_pressure)}</div>
                                    <div class="text-blue-100 text-xs">Pressure</div>
                                </div>
                            </div>
                        </div>
                    `;
                }

                displayTemperatureByAltitude(current) {
                    const container = document.getElementById('temperatureByAltitude');

                    const altitudeLevels = [
                        { level: '2m', temp: current.temperature_2m, color: '#ef4444' },
                        { level: '80m', temp: current.temperature_80m, color: '#f97316' },
                        { level: '120m', temp: current.temperature_120m, color: '#eab308' },
                        { level: '180m', temp: current.temperature_180m, color: '#22c55e' }
                    ];

                    const hasTemperatureData = altitudeLevels.some(item => item.temp !== undefined && item.temp !== null);

                    if (!hasTemperatureData) {
                        container.innerHTML = `
                            <div class="data-card-uniform bg-gray-100 col-span-2">
                                <div class="text-2xl mb-1">🌡️</div>
                                <p class="text-xs text-gray-500">Temperature altitude data not available</p>
                            </div>
                        `;
                        return;
                    }

                    container.innerHTML = altitudeLevels.map(item => {
                        const temp = item.temp;
                        const displayTemp = (temp !== undefined && temp !== null) ? Math.round(temp) : '--';
                        const tempClass = (temp !== undefined && temp !== null) ? 'text-white' : 'text-gray-200';

                        return `
                            <div class="data-card-uniform" style="background: linear-gradient(135deg, ${item.color} 0%, ${item.color}dd 100%);">
                                <div class="text-xs font-semibold text-white mb-1">${item.level}</div>
                                <div class="text-lg font-bold ${tempClass}">${displayTemp}°C</div>
                            </div>
                        `;
                    }).join('');
                }

                displayWindByAltitude(current) {
                    const container = document.getElementById('windByAltitude');
                    const windData = [
                        { level: '10m', speed: current.wind_speed_10m, direction: current.wind_direction_10m, gusts: current.wind_gusts_10m },
                        { level: '80m', speed: current.wind_speed_80m, direction: current.wind_direction_80m },
                        { level: '120m', speed: current.wind_speed_120m, direction: current.wind_direction_120m },
                        { level: '180m', speed: current.wind_speed_180m, direction: current.wind_direction_180m }
                    ].filter(item => item.speed !== undefined);

                    while (windData.length < 4) {
                        windData.push({ level: '--', speed: 0, direction: 0 });
                    }

                    container.innerHTML = windData.slice(0, 4).map(item => `
                        <div class="data-card-uniform bg-blue-100">
                            <div class="text-xs font-semibold text-gray-700 mb-1">${item.level}</div>
                            <div class="text-lg font-bold text-blue-600">${Math.round(item.speed)} km/h</div>
                            ${item.direction ? `<div class="text-xs text-gray-600">${item.direction}°</div>` : ''}
                            ${item.gusts ? `<div class="text-xs text-gray-500">Gusts: ${Math.round(item.gusts)}</div>` : ''}
                        </div>
                    `).join('');
                }

                displaySoilConditions(hourly) {
                    const container = document.getElementById('soilConditions');
                    if (!hourly) {
                        container.innerHTML = `
                            <div class="data-card-uniform bg-gray-100 col-span-2">
                                <div class="text-2xl mb-1">🌱</div>
                                <p class="text-xs text-gray-500">Soil data not available</p>
                            </div>
                        `;
                        return;
                    }

                    const soilData = [
                        { depth: '0cm', temp: hourly.soil_temperature_0cm?.[0], moisture: hourly.soil_moisture_0_1cm?.[0] },
                        { depth: '6cm', temp: hourly.soil_temperature_6cm?.[0], moisture: hourly.soil_moisture_1_3cm?.[0] },
                        { depth: '18cm', temp: hourly.soil_temperature_18cm?.[0], moisture: hourly.soil_moisture_3_9cm?.[0] },
                        { depth: '54cm', temp: hourly.soil_temperature_54cm?.[0], moisture: hourly.soil_moisture_9_27cm?.[0] }
                    ];

                    const validSoilData = soilData.filter(item => item.temp !== undefined || item.moisture !== undefined);

                    if (validSoilData.length === 0) {
                        container.innerHTML = `
                            <div class="data-card-uniform bg-gray-100 col-span-2">
                                <div class="text-2xl mb-1">🌱</div>
                                <p class="text-xs text-gray-500">Soil data not available</p>
                            </div>
                        `;
                        return;
                    }

                    while (validSoilData.length < 4) {
                        validSoilData.push({ depth: '--', temp: null, moisture: null });
                    }

                    container.innerHTML = validSoilData.slice(0, 4).map(item => `
                        <div class="data-card-uniform bg-orange-500 text-white">
                            <div class="text-xs font-semibold mb-1">${item.depth}</div>
                            ${item.temp ? `<div class="text-lg font-bold">${Math.round(item.temp)}°C</div>` : '<div class="text-lg font-bold">--°C</div>'}
                            ${item.moisture ? `<div class="text-xs">${item.moisture.toFixed(2)} m³/m³</div>` : '<div class="text-xs">-- m³/m³</div>'}
                        </div>
                    `).join('');
                }

                displayExtendedForecast(daily) {
                    const container = document.getElementById('extendedForecast');
                    if (!daily || !daily.time) {
                        container.innerHTML = '<div class="text-gray-500 text-center py-4">Forecast data not available</div>';
                        return;
                    }

                    const days = daily.time.slice(0, 7).map((date, index) => ({
                        date: new Date(date),
                        weatherCode: daily.weather_code[index],
                        maxTemp: daily.temperature_2m_max[index],
                        minTemp: daily.temperature_2m_min[index],
                        precipitation: daily.precipitation_sum[index],
                        windSpeed: daily.wind_speed_10m_max[index]
                    }));

                    container.innerHTML = days.map(day => `
                        <div class="forecast-item">
                            <div class="flex items-center gap-3">
                                <div class="text-xl">${this.getWeatherEmoji(day.weatherCode)}</div>
                                <div>
                                    <div class="font-semibold text-gray-800 text-sm">${day.date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })}</div>
                                    <div class="text-xs text-gray-600">Rain: ${Math.round(day.precipitation || 0)}mm</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-gray-800 text-sm">${Math.round(day.maxTemp)}° / ${Math.round(day.minTemp)}°</div>
                                <div class="text-xs text-gray-600">${Math.round(day.windSpeed)} km/h</div>
                            </div>
                        </div>
                    `).join('');
                }

                showWeatherError() {
                    document.getElementById('currentWeatherData').innerHTML = `
                        <div class="bg-red-100 border border-red-300 rounded-xl p-3 text-center">
                            <div class="text-red-500 text-xl mb-1">⚠️</div>
                            <p class="text-red-700 text-xs">Unable to fetch weather data</p>
                        </div>
                    `;
                }

                getWeatherEmoji(weatherCode) {
                    const weatherEmojis = {
                        0: '☀️', 1: '🌤️', 2: '⛅', 3: '☁️', 45: '🌫️', 48: '🌫️',
                        51: '🌦️', 53: '🌦️', 55: '🌧️', 61: '🌦️', 63: '🌧️', 65: '🌧️',
                        71: '🌨️', 73: '🌨️', 75: '🌨️', 80: '🌦️', 81: '🌧️', 82: '⛈️',
                        95: '⛈️', 96: '⛈️', 99: '⛈️'
                    };
                    return weatherEmojis[weatherCode] || '🌤️';
                }

                updateLastUpdated() {
                    const now = new Date();
                    const timeString = now.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    document.getElementById('lastUpdated').textContent = `Updated: ${timeString}`;
                    document.getElementById('forecastUpdated').textContent = `Updated: ${timeString}`;
                }
            }

            let app;
            document.addEventListener('DOMContentLoaded', () => {
                app = new EnhancedWeatherMapApp();
            });
        </script>
@endsection