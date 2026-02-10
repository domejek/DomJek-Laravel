<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index');
    }

    public function data(): JsonResponse
    {
        return response()->json([
            'user' => Auth::check() ? Auth::user() : null,
            'widgets' => [
                'weather' => $this->getWeatherData(),
                'crypto' => $this->getCryptoData(),
                'news' => $this->getNewsData(),
                'github' => $this->getGithubData(),
                'system' => $this->getSystemData()
            ]
        ]);
    }

    private function getWeatherData(): array
    {
        return [
            'location' => 'Berlin',
            'temperature' => rand(15, 25),
            'condition' => 'Sunny',
            'icon' => '☀️'
        ];
    }

    private function getCryptoData(): array
    {
        return [
            'bitcoin' => [
                'price' => rand(40000, 50000),
                'change' => rand(-10, 15),
                'symbol' => 'BTC'
            ]
        ];
    }

    private function getNewsData(): array
    {
        return [
            'headlines' => [
                ['title' => 'Tech News Update', 'summary' => 'New AI developments...'],
                ['title' => 'Market Report', 'summary' => 'Stocks rising...'],
                ['title' => 'Sports Results', 'summary' => 'Football scores...']
            ]
        ];
    }

    private function getGithubData(): array
    {
        return [
            'repositories' => rand(10, 20),
            'followers' => rand(50, 100),
            'stars' => rand(100, 200)
        ];
    }

    private function getSystemData(): array
    {
        return [
            'api_server' => 'online',
            'database' => 'connected',
            'cache' => 'active',
            'uptime' => '99.9%'
        ];
    }
}
