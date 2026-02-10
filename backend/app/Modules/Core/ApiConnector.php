<?php

namespace App\Modules\Core;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

abstract class ApiConnector
{
    protected string $baseUrl;
    protected string $apiKey;
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->baseUrl = $config['base_url'] ?? '';
        $this->apiKey = $config['api_key'] ?? '';
    }

    abstract public function fetchData(): JsonResponse;
    abstract public function validateConfig(): bool;

    protected function makeRequest(string $endpoint, array $params = []): array
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 10,
            'headers' => [
                'Accept' => 'application/json',
                'User-Agent' => 'API-Dashboard/1.0'
            ]
        ]);

        try {
            $response = $client->get($endpoint, [
                'query' => array_merge($params, $this->getAuthParams())
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }

    protected function getAuthParams(): array
    {
        return $this->apiKey ? ['apikey' => $this->apiKey] : [];
    }

    protected function cacheData(string $key, $data, int $ttl = 300): void
    {
        \Cache::put($key, $data, $ttl);
    }

    protected function getCachedData(string $key)
    {
        return \Cache::get($key);
    }
}