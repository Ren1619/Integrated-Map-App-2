<?php

namespace App\Services;

class WeatherAlertService
{

    private const THRESHOLDS = [
        'temperature' => [
            'extreme_heat' => 35,      // °C
            'very_hot' => 30,
            'extreme_cold' => 0,
            'very_cold' => 5,
        ],
        'wind_speed' => [
            'dangerous' => 75,         // km/h
            'very_strong' => 50,
            'strong' => 30,
        ],
        'humidity' => [
            'very_high' => 85,         // %
            'high' => 70,
            'very_low' => 25,
            'low' => 35,
        ],
        'precipitation' => [
            'heavy' => 10,             // mm
            'moderate' => 5,
        ],
        'uv_index' => [
            'extreme' => 11,
            'very_high' => 8,
            'high' => 6,
        ],
        'visibility' => [
            'very_poor' => 1000,       // meters
            'poor' => 5000,
        ],
        'pressure' => [
            'very_low' => 980,         // hPa (potential storm)
            'low' => 1000,
        ],
        // Thermal comfort thresholds based on apparent temperature
        'thermal_comfort_hot' => [
            'extreme_danger' => 54,    // °C - NOAA standard (apparent temp)
            'danger' => 41,
            'extreme_caution' => 32,
            'caution' => 27,
        ],
        'thermal_comfort_cold' => [
            'extreme_danger' => -40,   // °C - NWS standard (apparent temp)
            'danger' => -28,
            'warning' => -18,
            'advisory' => -9,
        ],
    ];

    /**
     * Analyze weather data and generate alerts
     */
    public function analyzeWeatherData(array $weatherData): array
    {
        $alerts = [];
        $current = $weatherData['current'] ?? [];

        // Temperature alerts
        if (isset($current['temperature_2m'])) {
            $tempAlert = $this->checkTemperature($current['temperature_2m']);
            if ($tempAlert) {
                $alerts[] = $tempAlert;
            }
        }

        // Thermal Comfort alerts (using apparent temperature from Open-Meteo)
        if (isset($current['apparent_temperature']) && isset($current['temperature_2m'])) {
            $thermalAlert = $this->checkThermalComfort(
                $current['apparent_temperature'],
                $current['temperature_2m'],
                $current['relative_humidity_2m'] ?? null,
                $current['wind_speed_10m'] ?? null
            );
            if ($thermalAlert) {
                $alerts[] = $thermalAlert;
            }
        }

        // Wind speed alerts
        if (isset($current['wind_speed_10m'])) {
            $windAlert = $this->checkWindSpeed($current['wind_speed_10m']);
            if ($windAlert) {
                $alerts[] = $windAlert;
            }
        }

        // Humidity alerts
        if (isset($current['relative_humidity_2m'])) {
            $humidityAlert = $this->checkHumidity($current['relative_humidity_2m']);
            if ($humidityAlert) {
                $alerts[] = $humidityAlert;
            }
        }

        // Precipitation alerts
        if (isset($current['precipitation'])) {
            $precipAlert = $this->checkPrecipitation($current['precipitation']);
            if ($precipAlert) {
                $alerts[] = $precipAlert;
            }
        }

        // UV Index alerts
        if (isset($current['uv_index'])) {
            $uvAlert = $this->checkUVIndex($current['uv_index']);
            if ($uvAlert) {
                $alerts[] = $uvAlert;
            }
        }

        // Visibility alerts
        if (isset($current['visibility'])) {
            $visibilityAlert = $this->checkVisibility($current['visibility']);
            if ($visibilityAlert) {
                $alerts[] = $visibilityAlert;
            }
        }

        // Pressure alerts
        if (isset($current['surface_pressure'])) {
            $pressureAlert = $this->checkPressure($current['surface_pressure']);
            if ($pressureAlert) {
                $alerts[] = $pressureAlert;
            }
        }

        // Weather code alerts (storms, heavy rain, etc.)
        if (isset($current['weather_code'])) {
            $weatherCodeAlert = $this->checkWeatherCode($current['weather_code']);
            if ($weatherCodeAlert) {
                $alerts[] = $weatherCodeAlert;
            }
        }

        return $alerts;
    }

    /**
     * Check thermal comfort using Open-Meteo's apparent temperature
     * Applies NOAA heat index thresholds for hot conditions
     * Applies NWS wind chill thresholds for cold conditions
     */
    private function checkThermalComfort(
        float $apparentTemp,
        float $actualTemp,
        ?float $humidity = null,
        ?float $windSpeed = null
    ): ?array {
        // Determine if this is a hot or cold scenario based on actual temperature
        $isHotScenario = $actualTemp >= 27;
        $isColdScenario = $actualTemp <= 10;

        // HOT SCENARIO - Apply NOAA Heat Index thresholds
        if ($isHotScenario) {
            if ($apparentTemp >= self::THRESHOLDS['thermal_comfort_hot']['extreme_danger']) {
                return [
                    'type' => 'thermal_comfort',
                    'severity' => 'danger',
                    'title' => 'Extreme Heat Danger',
                    'message' => "Feels like temperature is extremely dangerous at " . round($apparentTemp, 1) . "°C. Heat stroke is imminent. Stay indoors in air conditioning. Avoid all outdoor activities.",
                    'icon' => '🔥',
                    'value' => round($apparentTemp, 1),
                    'unit' => '°C',
                    'details' => [
                        'actual_temperature' => round($actualTemp, 1),
                        'humidity' => $humidity,
                        'wind_speed' => $windSpeed,
                        'category' => 'Extreme Danger',
                        'type' => 'heat'
                    ]
                ];
            } elseif ($apparentTemp >= self::THRESHOLDS['thermal_comfort_hot']['danger']) {
                return [
                    'type' => 'thermal_comfort',
                    'severity' => 'danger',
                    'title' => 'Heat Danger',
                    'message' => "Feels like temperature is dangerous at " . round($apparentTemp, 1) . "°C. Heat cramps and heat exhaustion are likely. Heat stroke is probable with continued activity.",
                    'icon' => '🌡️',
                    'value' => round($apparentTemp, 1),
                    'unit' => '°C',
                    'details' => [
                        'actual_temperature' => round($actualTemp, 1),
                        'humidity' => $humidity,
                        'wind_speed' => $windSpeed,
                        'category' => 'Danger',
                        'type' => 'heat'
                    ]
                ];
            } elseif ($apparentTemp >= self::THRESHOLDS['thermal_comfort_hot']['extreme_caution']) {
                return [
                    'type' => 'thermal_comfort',
                    'severity' => 'warning',
                    'title' => 'Extreme Heat Caution',
                    'message' => "Feels like temperature is at extreme caution level (" . round($apparentTemp, 1) . "°C). Heat cramps and heat exhaustion are possible. Minimize outdoor activities.",
                    'icon' => '☀️',
                    'value' => round($apparentTemp, 1),
                    'unit' => '°C',
                    'details' => [
                        'actual_temperature' => round($actualTemp, 1),
                        'humidity' => $humidity,
                        'wind_speed' => $windSpeed,
                        'category' => 'Extreme Caution',
                        'type' => 'heat'
                    ]
                ];
            } elseif ($apparentTemp >= self::THRESHOLDS['thermal_comfort_hot']['caution']) {
                return [
                    'type' => 'thermal_comfort',
                    'severity' => 'info',
                    'title' => 'Heat Caution',
                    'message' => "Feels like temperature is at caution level (" . round($apparentTemp, 1) . "°C). Fatigue is possible with prolonged exposure. Stay hydrated.",
                    'icon' => '🌤️',
                    'value' => round($apparentTemp, 1),
                    'unit' => '°C',
                    'details' => [
                        'actual_temperature' => round($actualTemp, 1),
                        'humidity' => $humidity,
                        'wind_speed' => $windSpeed,
                        'category' => 'Caution',
                        'type' => 'heat'
                    ]
                ];
            }
        }

        // COLD SCENARIO - Apply NWS Wind Chill thresholds
        if ($isColdScenario) {
            if ($apparentTemp <= self::THRESHOLDS['thermal_comfort_cold']['extreme_danger']) {
                return [
                    'type' => 'thermal_comfort',
                    'severity' => 'danger',
                    'title' => 'Extreme Cold Danger',
                    'message' => "Feels like temperature is extremely dangerous at " . round($apparentTemp, 1) . "°C. Frostbite can occur in minutes. Avoid outdoor exposure.",
                    'icon' => '🥶',
                    'value' => round($apparentTemp, 1),
                    'unit' => '°C',
                    'details' => [
                        'actual_temperature' => round($actualTemp, 1),
                        'humidity' => $humidity,
                        'wind_speed' => $windSpeed,
                        'category' => 'Extreme Danger',
                        'type' => 'cold'
                    ]
                ];
            } elseif ($apparentTemp <= self::THRESHOLDS['thermal_comfort_cold']['danger']) {
                return [
                    'type' => 'thermal_comfort',
                    'severity' => 'danger',
                    'title' => 'Cold Danger',
                    'message' => "Feels like temperature is dangerous at " . round($apparentTemp, 1) . "°C. Frostbite possible within 10 minutes. Dress in layers and limit outdoor time.",
                    'icon' => '❄️',
                    'value' => round($apparentTemp, 1),
                    'unit' => '°C',
                    'details' => [
                        'actual_temperature' => round($actualTemp, 1),
                        'humidity' => $humidity,
                        'wind_speed' => $windSpeed,
                        'category' => 'Danger',
                        'type' => 'cold'
                    ]
                ];
            } elseif ($apparentTemp <= self::THRESHOLDS['thermal_comfort_cold']['warning']) {
                return [
                    'type' => 'thermal_comfort',
                    'severity' => 'warning',
                    'title' => 'Cold Warning',
                    'message' => "Feels like temperature is at warning level (" . round($apparentTemp, 1) . "°C). Frostbite possible within 30 minutes. Wear appropriate cold weather gear.",
                    'icon' => '🌬️',
                    'value' => round($apparentTemp, 1),
                    'unit' => '°C',
                    'details' => [
                        'actual_temperature' => round($actualTemp, 1),
                        'humidity' => $humidity,
                        'wind_speed' => $windSpeed,
                        'category' => 'Warning',
                        'type' => 'cold'
                    ]
                ];
            } elseif ($apparentTemp <= self::THRESHOLDS['thermal_comfort_cold']['advisory']) {
                return [
                    'type' => 'thermal_comfort',
                    'severity' => 'info',
                    'title' => 'Cold Advisory',
                    'message' => "Feels like temperature is at advisory level (" . round($apparentTemp, 1) . "°C). Dress warmly for outdoor activities.",
                    'icon' => '🧊',
                    'value' => round($apparentTemp, 1),
                    'unit' => '°C',
                    'details' => [
                        'actual_temperature' => round($actualTemp, 1),
                        'humidity' => $humidity,
                        'wind_speed' => $windSpeed,
                        'category' => 'Advisory',
                        'type' => 'cold'
                    ]
                ];
            }
        }

        return null;
    }

    private function checkTemperature(float $temp): ?array
    {
        if ($temp >= self::THRESHOLDS['temperature']['extreme_heat']) {
            return [
                'type' => 'temperature',
                'severity' => 'danger',
                'title' => 'Extreme Heat Warning',
                'message' => "Temperature is extremely high at {$temp}°C. Stay hydrated and avoid outdoor activities.",
                'icon' => '🌡️',
                'value' => $temp,
                'unit' => '°C'
            ];
        } elseif ($temp >= self::THRESHOLDS['temperature']['very_hot']) {
            return [
                'type' => 'temperature',
                'severity' => 'warning',
                'title' => 'High Temperature Alert',
                'message' => "It's very hot at {$temp}°C. Take precautions when going outside.",
                'icon' => '☀️',
                'value' => $temp,
                'unit' => '°C'
            ];
        } elseif ($temp <= self::THRESHOLDS['temperature']['extreme_cold']) {
            return [
                'type' => 'temperature',
                'severity' => 'danger',
                'title' => 'Extreme Cold Warning',
                'message' => "Temperature is freezing at {$temp}°C. Dress warmly and limit outdoor exposure.",
                'icon' => '❄️',
                'value' => $temp,
                'unit' => '°C'
            ];
        } elseif ($temp <= self::THRESHOLDS['temperature']['very_cold']) {
            return [
                'type' => 'temperature',
                'severity' => 'warning',
                'title' => 'Cold Temperature Alert',
                'message' => "It's cold at {$temp}°C. Wear appropriate clothing.",
                'icon' => '🥶',
                'value' => $temp,
                'unit' => '°C'
            ];
        }

        return null;
    }

    private function checkWindSpeed(float $windSpeed): ?array
    {
        if ($windSpeed >= self::THRESHOLDS['wind_speed']['dangerous']) {
            return [
                'type' => 'wind',
                'severity' => 'danger',
                'title' => 'Dangerous Wind Warning',
                'message' => "Extremely strong winds at {$windSpeed} km/h. Stay indoors and secure loose objects.",
                'icon' => '🌪️',
                'value' => $windSpeed,
                'unit' => 'km/h'
            ];
        } elseif ($windSpeed >= self::THRESHOLDS['wind_speed']['very_strong']) {
            return [
                'type' => 'wind',
                'severity' => 'warning',
                'title' => 'Strong Wind Alert',
                'message' => "Very strong winds at {$windSpeed} km/h. Exercise caution outdoors.",
                'icon' => '💨',
                'value' => $windSpeed,
                'unit' => 'km/h'
            ];
        } elseif ($windSpeed >= self::THRESHOLDS['wind_speed']['strong']) {
            return [
                'type' => 'wind',
                'severity' => 'info',
                'title' => 'Windy Conditions',
                'message' => "Moderate winds at {$windSpeed} km/h. Be aware of gusty conditions.",
                'icon' => '🍃',
                'value' => $windSpeed,
                'unit' => 'km/h'
            ];
        }

        return null;
    }

    private function checkHumidity(float $humidity): ?array
    {
        if ($humidity >= self::THRESHOLDS['humidity']['very_high']) {
            return [
                'type' => 'humidity',
                'severity' => 'warning',
                'title' => 'Very High Humidity',
                'message' => "Humidity is very high at {$humidity}%. Conditions may feel uncomfortable.",
                'icon' => '💧',
                'value' => $humidity,
                'unit' => '%'
            ];
        } elseif ($humidity <= self::THRESHOLDS['humidity']['very_low']) {
            return [
                'type' => 'humidity',
                'severity' => 'info',
                'title' => 'Very Low Humidity',
                'message' => "Humidity is very low at {$humidity}%. Stay hydrated and use moisturizer.",
                'icon' => '🏜️',
                'value' => $humidity,
                'unit' => '%'
            ];
        }

        return null;
    }

    private function checkPrecipitation(float $precipitation): ?array
    {
        if ($precipitation >= self::THRESHOLDS['precipitation']['heavy']) {
            return [
                'type' => 'precipitation',
                'severity' => 'warning',
                'title' => 'Heavy Rainfall',
                'message' => "Heavy rain detected ({$precipitation} mm). Possible flooding in low-lying areas.",
                'icon' => '🌧️',
                'value' => $precipitation,
                'unit' => 'mm'
            ];
        } elseif ($precipitation >= self::THRESHOLDS['precipitation']['moderate']) {
            return [
                'type' => 'precipitation',
                'severity' => 'info',
                'title' => 'Moderate Rain',
                'message' => "Moderate rainfall ({$precipitation} mm). Carry an umbrella.",
                'icon' => '☔',
                'value' => $precipitation,
                'unit' => 'mm'
            ];
        }

        return null;
    }

    private function checkUVIndex(float $uvIndex): ?array
    {
        if ($uvIndex >= self::THRESHOLDS['uv_index']['extreme']) {
            return [
                'type' => 'uv',
                'severity' => 'danger',
                'title' => 'Extreme UV Level',
                'message' => "UV index is extreme ({$uvIndex}). Avoid sun exposure. Use maximum protection.",
                'icon' => '☀️',
                'value' => $uvIndex,
                'unit' => ''
            ];
        } elseif ($uvIndex >= self::THRESHOLDS['uv_index']['very_high']) {
            return [
                'type' => 'uv',
                'severity' => 'warning',
                'title' => 'Very High UV Level',
                'message' => "UV index is very high ({$uvIndex}). Wear sunscreen and protective clothing.",
                'icon' => '🌞',
                'value' => $uvIndex,
                'unit' => ''
            ];
        } elseif ($uvIndex >= self::THRESHOLDS['uv_index']['high']) {
            return [
                'type' => 'uv',
                'severity' => 'info',
                'title' => 'High UV Level',
                'message' => "UV index is high ({$uvIndex}). Use sun protection.",
                'icon' => '😎',
                'value' => $uvIndex,
                'unit' => ''
            ];
        }

        return null;
    }

    private function checkVisibility(float $visibility): ?array
    {
        if ($visibility <= self::THRESHOLDS['visibility']['very_poor']) {
            return [
                'type' => 'visibility',
                'severity' => 'danger',
                'title' => 'Very Poor Visibility',
                'message' => "Visibility is very poor (" . round($visibility / 1000, 1) . " km). Drive carefully with reduced speed.",
                'icon' => '🌫️',
                'value' => round($visibility / 1000, 1),
                'unit' => 'km'
            ];
        } elseif ($visibility <= self::THRESHOLDS['visibility']['poor']) {
            return [
                'type' => 'visibility',
                'severity' => 'warning',
                'title' => 'Reduced Visibility',
                'message' => "Visibility is reduced (" . round($visibility / 1000, 1) . " km). Exercise caution while driving.",
                'icon' => '🌁',
                'value' => round($visibility / 1000, 1),
                'unit' => 'km'
            ];
        }

        return null;
    }

    private function checkPressure(float $pressure): ?array
    {
        if ($pressure <= self::THRESHOLDS['pressure']['very_low']) {
            return [
                'type' => 'pressure',
                'severity' => 'warning',
                'title' => 'Low Atmospheric Pressure',
                'message' => "Very low pressure ({$pressure} hPa). Potential storm system approaching.",
                'icon' => '⚠️',
                'value' => $pressure,
                'unit' => 'hPa'
            ];
        }

        return null;
    }

    private function checkWeatherCode(int $weatherCode): ?array
    {
        // Severe weather codes
        $severeWeather = [
            95 => ['title' => 'Thunderstorm', 'message' => 'Thunderstorm in the area. Stay indoors and avoid open areas.', 'icon' => '⛈️', 'severity' => 'danger'],
            96 => ['title' => 'Thunderstorm with Hail', 'message' => 'Thunderstorm with slight hail. Seek shelter immediately.', 'icon' => '⛈️', 'severity' => 'danger'],
            99 => ['title' => 'Severe Thunderstorm', 'message' => 'Severe thunderstorm with heavy hail. Take immediate shelter.', 'icon' => '⛈️', 'severity' => 'danger'],
            65 => ['title' => 'Heavy Rain', 'message' => 'Heavy rain in the area. Watch for flooding.', 'icon' => '🌧️', 'severity' => 'warning'],
            75 => ['title' => 'Heavy Snow', 'message' => 'Heavy snowfall. Travel may be difficult.', 'icon' => '🌨️', 'severity' => 'warning'],
            82 => ['title' => 'Violent Rain Showers', 'message' => 'Violent rain showers. Stay indoors if possible.', 'icon' => '⛈️', 'severity' => 'warning'],
        ];

        if (isset($severeWeather[$weatherCode])) {
            return [
                'type' => 'weather_condition',
                'severity' => $severeWeather[$weatherCode]['severity'],
                'title' => $severeWeather[$weatherCode]['title'],
                'message' => $severeWeather[$weatherCode]['message'],
                'icon' => $severeWeather[$weatherCode]['icon'],
                'value' => $weatherCode,
                'unit' => ''
            ];
        }

        return null;
    }

    /**
     * Get alert statistics
     */
    public function getAlertStatistics(array $alerts): array
    {
        $stats = [
            'total' => count($alerts),
            'danger' => 0,
            'warning' => 0,
            'info' => 0,
        ];

        foreach ($alerts as $alert) {
            $severity = $alert['severity'] ?? 'info';
            $stats[$severity] = ($stats[$severity] ?? 0) + 1;
        }

        return $stats;
    }

    /**
     * Sort alerts by severity (danger > warning > info)
     */
    public function sortAlertsBySeverity(array $alerts): array
    {
        $severityOrder = ['danger' => 0, 'warning' => 1, 'info' => 2];
        
        usort($alerts, function($a, $b) use ($severityOrder) {
            $aSeverity = $severityOrder[$a['severity']] ?? 3;
            $bSeverity = $severityOrder[$b['severity']] ?? 3;
            return $aSeverity <=> $bSeverity;
        });

        return $alerts;
    }
}