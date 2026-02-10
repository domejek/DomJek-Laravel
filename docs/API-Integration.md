# API Integration Guide

## Übersicht
Dieser Guide beschreibt die Integration verschiedener öffentlicher APIs in das Dashboard-Aggregator System.

## Unterstützte APIs

### 1. Weather APIs

#### OpenWeatherMap API
**Endpoint**: `https://api.openweathermap.org/data/2.5/weather`
**Authentication**: API Key
**Rate Limit**: 1000 calls/day (free)

```php
class OpenWeatherMapConnector extends ApiConnector
{
    private string $apiKey;
    private string $baseUrl = 'https://api.openweathermap.org/data/2.5';
    
    public function fetch(array $params): array
    {
        $city = $params['city'] ?? 'Berlin';
        $units = $params['units'] ?? 'metric';
        
        $response = Http::get("{$this->baseUrl}/weather", [
            'q' => $city,
            'appid' => $this->apiKey,
            'units' => $units
        ]);
        
        return [
            'temperature' => $response->json('main.temp'),
            'humidity' => $response->json('main.humidity'),
            'description' => $response->json('weather.0.description'),
            'icon' => $response->json('weather.0.icon'),
            'city' => $response->json('name'),
            'country' => $response->json('sys.country')
        ];
    }
    
    public function getCacheDuration(): int
    {
        return 900; // 15 minutes
    }
}
```

**Widget Config**:
```json
{
    "city": "Berlin",
    "units": "metric",
    "show_forecast": true
}
```

### 2. Cryptocurrency APIs

#### CoinGecko API
**Endpoint**: `https://api.coingecko.com/api/v3/`
**Authentication**: None (free tier)
**Rate Limit**: 10-50 calls/minute

```php
class CoinGeckoConnector extends ApiConnector
{
    private string $baseUrl = 'https://api.coingecko.com/api/v3';
    
    public function fetch(array $params): array
    {
        $coins = $params['coins'] ?? ['bitcoin', 'ethereum'];
        $vsCurrency = $params['vs_currency'] ?? 'usd';
        
        $response = Http::get("{$this->baseUrl}/simple/price", [
            'ids' => implode(',', $coins),
            'vs_currencies' => $vsCurrency,
            'include_24hr_change' => true
        ]);
        
        $data = [];
        foreach ($response->json() as $coin => $prices) {
            $data[$coin] = [
                'price' => $prices[$vsCurrency],
                'change_24h' => $prices[$vsCurrency . '_24h_change'],
                'symbol' => strtoupper($coin)
            ];
        }
        
        return $data;
    }
    
    public function getCacheDuration(): int
    {
        return 60; // 1 minute for crypto prices
    }
}
```

**Widget Config**:
```json
{
    "coins": ["bitcoin", "ethereum", "cardano"],
    "vs_currency": "eur",
    "show_chart": true
}
```

### 3. News APIs

#### NewsAPI.org
**Endpoint**: `https://newsapi.org/v2/`
**Authentication**: API Key
**Rate Limit**: 1000 calls/day (free)

```php
class NewsApiConnector extends ApiConnector
{
    private string $apiKey;
    private string $baseUrl = 'https://newsapi.org/v2';
    
    public function fetch(array $params): array
    {
        $category = $params['category'] ?? 'general';
        $country = $params['country'] ?? 'de';
        $pageSize = $params['page_size'] ?? 10;
        
        $response = Http::get("{$this->baseUrl}/top-headlines", [
            'category' => $category,
            'country' => $country,
            'pageSize' => $pageSize,
            'apiKey' => $this->apiKey
        ]);
        
        return [
            'articles' => collect($response->json('articles'))->map(function ($article) {
                return [
                    'title' => $article['title'],
                    'description' => $article['description'],
                    'url' => $article['url'],
                    'source' => $article['source']['name'],
                    'published_at' => $article['publishedAt'],
                    'image_url' => $article['urlToImage']
                ];
            })->toArray(),
            'total_results' => $response->json('totalResults')
        ];
    }
    
    public function getCacheDuration(): int
    {
        return 1800; // 30 minutes
    }
}
```

**Widget Config**:
```json
{
    "category": "technology",
    "country": "de",
    "page_size": 5,
    "show_images": true
}
```

### 4. GitHub APIs

#### GitHub REST API
**Endpoint**: `https://api.github.com/`
**Authentication**: Personal Access Token (recommended)
**Rate Limit**: 5000 calls/hour (authenticated)

```php
class GitHubConnector extends ApiConnector
{
    private ?string $token = null;
    private string $baseUrl = 'https://api.github.com';
    
    public function fetch(array $params): array
    {
        $username = $params['username'];
        $repoType = $params['repo_type'] ?? 'owner';
        
        $headers = [];
        if ($this->token) {
            $headers['Authorization'] = "token {$this->token}";
        }
        
        $response = Http::withHeaders($headers)
            ->get("{$this->baseUrl}/users/{$username}/repos", [
                'type' => $repoType,
                'sort' => 'updated',
                'per_page' => 10
            ]);
        
        return [
            'user' => $username,
            'repositories' => collect($response->json())->map(function ($repo) {
                return [
                    'name' => $repo['name'],
                    'description' => $repo['description'],
                    'stars' => $repo['stargazers_count'],
                    'language' => $repo['language'],
                    'updated_at' => $repo['updated_at'],
                    'url' => $repo['html_url']
                ];
            })->toArray()
        ];
    }
    
    public function getCacheDuration(): int
    {
        return 300; // 5 minutes
    }
}
```

**Widget Config**:
```json
{
    "username": "laravel",
    "repo_type": "owner",
    "show_language_stats": true
}
```

### 5. Testing APIs

#### JSONPlaceholder
**Endpoint**: `https://jsonplaceholder.typicode.com/`
**Authentication**: None
**Rate Limit**: None (for testing)

```php
class JsonPlaceholderConnector extends ApiConnector
{
    private string $baseUrl = 'https://jsonplaceholder.typicode.com';
    
    public function fetch(array $params): array
    {
        $resource = $params['resource'] ?? 'posts';
        $limit = $params['limit'] ?? 10;
        
        $response = Http::get("{$this->baseUrl}/{$resource}", [
            '_limit' => $limit
        ]);
        
        return $response->json();
    }
    
    public function getCacheDuration(): int
    {
        return 3600; // 1 hour for test data
    }
}
```

#### SpaceX API
**Endpoint**: `https://api.spacexdata.com/v4/`
**Authentication**: None
**Rate Limit**: None

```php
class SpaceXConnector extends ApiConnector
{
    private string $baseUrl = 'https://api.spacexdata.com/v4';
    
    public function fetch(array $params): array
    {
        $query = $params['query'] ?? 'launches';
        $limit = $params['limit'] ?? 5;
        
        $response = Http::get("{$this->baseUrl}/{$query}", [
            'limit' => $limit,
            'sort' => 'flight_number',
            'order' => 'desc'
        ]);
        
        return collect($response->json())->map(function ($launch) {
            return [
                'name' => $launch['name'],
                'date_utc' => $launch['date_utc'],
                'success' => $launch['success'] ?? false,
                'details' => $launch['details'],
                'rocket' => $launch['rocket']
            ];
        })->toArray();
    }
    
    public function getCacheDuration(): int
    {
        return 3600; // 1 hour
    }
}
```

## Implementierungs-Guide

### 1. Neuen API-Connector erstellen

#### Step 1: Connector Class erstellen
```php
namespace App\Modules\MyApi;

use App\Services\ApiConnector;

class MyApiConnector extends ApiConnector
{
    private string $baseUrl;
    private ?string $apiKey;
    
    public function __construct()
    {
        $this->baseUrl = config('services.myapi.base_url');
        $this->apiKey = config('services.myapi.key');
    }
    
    public function fetch(array $params): array
    {
        // API-Implementierung
    }
    
    public function validateConfig(array $config): bool
    {
        // Validierungslogik
        return isset($config['required_param']);
    }
    
    public function getCacheDuration(): int
    {
        return config('services.myapi.cache_duration', 300);
    }
}
```

#### Step 2: Service Provider registrieren
```php
// config/app.php
'providers' => [
    // ...
    App\Modules\MyApi\MyApiServiceProvider::class,
],
```

#### Step 3: Widget-Komponente erstellen
```javascript
// resources/js/widgets/MyApiWidget.js
class MyApiWidget extends BaseWidget {
    constructor(config) {
        super(config);
        this.template = this.getTemplate();
    }
    
    render(data) {
        // Rendering-Logik
    }
    
    getTemplate() {
        return `
            <div class="myapi-widget">
                <h3>{{title}}</h3>
                <div class="content">
                    <!-- Widget content -->
                </div>
            </div>
        `;
    }
}
```

### 2. API-Konfiguration

#### Environment Variables
```env
# .env
OPENWEATHER_API_KEY=your_openweather_key
NEWS_API_KEY=your_news_api_key
GITHUB_TOKEN=your_github_token
```

#### Services Config
```php
// config/services.php
return [
    'openweathermap' => [
        'base_url' => 'https://api.openweathermap.org/data/2.5',
        'key' => env('OPENWEATHER_API_KEY'),
        'cache_duration' => 900,
    ],
    'newsapi' => [
        'base_url' => 'https://newsapi.org/v2',
        'key' => env('NEWS_API_KEY'),
        'cache_duration' => 1800,
    ],
    // ...
];
```

### 3. Error Handling

#### API Error Responses
```php
try {
    $response = Http::timeout(10)->get($url, $params);
    
    if ($response->failed()) {
        throw new ApiException("API request failed: " . $response->status());
    }
    
    return $response->json();
    
} catch (\Exception $e) {
    Log::error("API Connector Error", [
        'connector' => static::class,
        'error' => $e->getMessage(),
        'params' => $params
    ]);
    
    return $this->getFallbackData();
}
```

#### Frontend Error Handling
```javascript
async fetchWidgetData(widgetId) {
    try {
        const response = await this.apiService.getWidgetData(widgetId);
        this.updateWidget(widgetId, response.data);
    } catch (error) {
        this.showWidgetError(widgetId, error.message);
        this.loadCachedData(widgetId);
    }
}
```

### 4. Rate Limiting

#### Backend Rate Limiting
```php
// routes/api.php
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/widgets/{id}/data', [WidgetController::class, 'getData']);
});
```

#### Queue Processing für API Calls
```php
// app/Jobs/FetchApiData.php
class FetchApiData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue;
    
    public int $tries = 3;
    public int $backoff = [30, 60, 120];
    
    public function handle()
    {
        // API-Aufruf mit Retry-Logik
    }
    
    public function failed(\Exception $exception)
    {
        Log::error("API Data Fetch Failed", [
            'widget_id' => $this->widgetId,
            'error' => $exception->getMessage()
        ]);
    }
}
```

## Best Practices

### 1. Caching
- Multi-Level Caching (Redis + Database)
- Unterschiedliche Cache-Dauern pro API-Typ
- Cache Invalidation bei API-Änderungen

### 2. Security
- API-Keys niemals im Frontend speichern
- Input Validation für alle API-Parameter
- Rate Limiting für externe APIs

### 3. Performance
- Asynchrone API-Aufrufe mit Queues
- Batching für multiple API-Requests
- Lazy Loading für Widget-Daten

### 4. Monitoring
- API Response Times loggen
- Error Rates überwachen
- Cache Hit Rates tracken