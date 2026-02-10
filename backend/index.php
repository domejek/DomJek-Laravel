<?php
header('Content-Type: application/json');

$widgets = [
    'weather' => [
        'location' => 'Berlin',
        'temperature' => rand(15, 25),
        'condition' => 'Sunny',
        'icon' => '☀️'
    ],
    'crypto' => [
        'bitcoin' => [
            'price' => '$' . number_format(rand(40000, 50000), 0),
            'change' => rand(-10, 15),
            'symbol' => 'BTC'
        ]
    ],
    'news' => [
        'headlines' => [
            ['title' => 'Tech News Update', 'summary' => 'New AI developments...'],
            ['title' => 'Market Report', 'summary' => 'Stocks rising...'],
            ['title' => 'Sports Results', 'summary' => 'Football scores...']
        ]
    ],
    'github' => [
        'repositories' => rand(10, 20),
        'followers' => rand(50, 100),
        'stars' => rand(100, 200)
    ],
    'system' => [
        'api_server' => 'online',
        'database' => 'connected',
        'cache' => 'active',
        'uptime' => '99.9%'
    ]
];

echo json_encode([
    'success' => true,
    'data' => $widgets,
    'timestamp' => date('Y-m-d H:i:s')
]);
