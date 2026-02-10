<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ApiController extends Controller
{
    public function weather(Request $request): JsonResponse
    {
        return $this->getWeather($request);
    }

    public function geolocation(Request $request): JsonResponse
    {
        return $this->getGeolocation($request);
    }

    public function fetch(Request $request, string $provider): JsonResponse
    {
        switch ($provider) {
            case 'weather':
                return $this->getWeather($request);
            case 'geolocation':
                return $this->getGeolocation($request);
            default:
                return response()->json(['error' => 'Provider not found'], 404);
        }
    }

    private function getWeather(Request $request): JsonResponse
    {
        $lat = $request->get('lat', 52.5200);
        $lon = $request->get('lon', 13.4050);
        $apiKey = config('services.openweathermap.api_key');

        if (!$apiKey) {
            return response()->json(['error' => 'OpenWeatherMap API key not configured'], 500);
        }

        $cacheKey = "weather_{$lat}_{$lon}";
        $weather = Cache::remember($cacheKey, 600, function () use ($lat, $lon, $apiKey) {
            $response = Http::get("https://api.openweathermap.org/data/2.5/weather", [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => $apiKey,
                'units' => 'metric',
                'lang' => 'de'
            ]);

            if (!$response->successful()) {
                throw new \Exception('Weather API request failed');
            }

            return $response->json();
        });

        return response()->json([
            'location' => $weather['name'],
            'temperature' => round($weather['main']['temp']),
            'condition' => $weather['weather'][0]['description'],
            'icon' => $this->getWeatherIcon($weather['weather'][0]['icon']),
            'humidity' => $weather['main']['humidity'],
            'wind_speed' => $weather['wind']['speed'],
            'coordinates' => [
                'lat' => $weather['coord']['lat'],
                'lon' => $weather['coord']['lon']
            ]
        ]);
    }

    private function getGeolocation(Request $request): JsonResponse
    {
        $address = $request->get('address');
        $apiKey = config('services.openweathermap.api_key');

        if (!$address) {
            return response()->json(['error' => 'Address parameter required'], 400);
        }

        if (!$apiKey) {
            return response()->json(['error' => 'OpenWeatherMap API key not configured'], 500);
        }

        $cacheKey = "geolocation_" . md5($address);
        $location = Cache::remember($cacheKey, 3600, function () use ($address, $apiKey) {
            $response = Http::get("http://api.openweathermap.org/geo/1.0/direct", [
                'q' => $address,
                'limit' => 1,
                'appid' => $apiKey
            ]);

            if (!$response->successful() || empty($response->json())) {
                throw new \Exception('Geolocation API request failed');
            }

            return $response->json()[0];
        });

        return response()->json([
            'address' => $address,
            'coordinates' => [
                'lat' => $location['lat'],
                'lon' => $location['lon']
            ],
            'name' => $location['name'],
            'country' => $location['country']
        ]);
    }

    private function getWeatherIcon(string $iconCode): string
    {
        $iconMap = [
            '01d' => '☀️', '01n' => '🌙',
            '02d' => '⛅', '02n' => '☁️',
            '03d' => '☁️', '03n' => '☁️',
            '04d' => '☁️', '04n' => '☁️',
            '09d' => '🌧️', '09n' => '🌧️',
            '10d' => '🌦️', '10n' => '🌧️',
            '11d' => '⛈️', '11n' => '⛈️',
            '13d' => '❄️', '13n' => '❄️',
            '50d' => '🌫️', '50n' => '🌫️'
        ];

        return $iconMap[$iconCode] ?? '🌤️';
    }
}
