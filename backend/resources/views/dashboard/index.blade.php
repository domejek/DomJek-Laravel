<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-600 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-2xl font-bold">API Dashboard</h1>
            <nav class="flex space-x-4">
                <button class="px-4 py-2 bg-blue-500 rounded hover:bg-blue-700 transition">Dashboard</button>
                <button class="px-4 py-2 bg-blue-500 rounded hover:bg-blue-700 transition">Widgets</button>
                <button class="px-4 py-2 bg-blue-500 rounded hover:bg-blue-700 transition">Einstellungen</button>
            </nav>
        </div>
    </header>

    <main class="container mx-auto p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Wetter Widget -->
            <div class="bg-white rounded-lg shadow-lg p-4">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-semibold">Wetter</h3>
                    <span class="text-sm text-gray-500">Berlin</span>
                </div>
                <div class="text-center">
                    <div class="text-3xl mb-2">☀️</div>
                    <div class="text-2xl font-bold">22°C</div>
                    <div class="text-sm text-gray-600">Sonnig</div>
                </div>
            </div>

            <!-- Krypto Widget -->
            <div class="bg-white rounded-lg shadow-lg p-4">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-semibold">Krypto</h3>
                    <span class="text-sm text-gray-500">Bitcoin</span>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold mb-2">$45,234</div>
                    <div class="text-sm text-green-600">+5.2%</div>
                </div>
            </div>

            <!-- Nachrichten Widget -->
            <div class="bg-white rounded-lg shadow-lg p-4">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-semibold">Nachrichten</h3>
                    <span class="text-sm text-gray-500">Top 3</span>
                </div>
                <div class="space-y-2">
                    <div class="text-sm p-2 bg-gray-50 rounded">
                        <div class="font-medium">Tech News</div>
                        <div class="text-gray-600">Neue KI-Entwicklung...</div>
                    </div>
                    <div class="text-sm p-2 bg-gray-50 rounded">
                        <div class="font-medium">Wirtschaft</div>
                        <div class="text-gray-600">Märkte steigen...</div>
                    </div>
                    <div class="text-sm p-2 bg-gray-50 rounded">
                        <div class="font-medium">Sport</div>
                        <div class="text-gray-600">Fußball-Ergebnisse...</div>
                    </div>
                </div>
            </div>

            <!-- GitHub Widget -->
            <div class="bg-white rounded-lg shadow-lg p-4">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-semibold">GitHub</h3>
                    <span class="text-sm text-gray-500">Repos</span>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold mb-2">12</div>
                    <div class="text-sm text-gray-600">Öffentliche Repositories</div>
                </div>
            </div>

            <!-- System Status Widget -->
            <div class="bg-white rounded-lg shadow-lg p-4">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-semibold">System Status</h3>
                    <span class="text-sm text-gray-500">Live</span>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm">API Server</span>
                        <span class="text-sm text-green-600">✓ Online</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm">Database</span>
                        <span class="text-sm text-green-600">✓ Connected</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm">Cache</span>
                        <span class="text-sm text-green-600">✓ Active</span>
                    </div>
                </div>
            </div>

            <!-- Chart Widget -->
            <div class="bg-white rounded-lg shadow-lg p-4">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-lg font-semibold">Performance</h3>
                    <span class="text-sm text-gray-500">24h</span>
                </div>
                <canvas id="performanceChart" width="400" height="200"></canvas>
            </div>
        </div>
    </main>

    <footer class="bg-gray-800 text-white p-4 mt-8">
        <div class="container mx-auto text-center">
            <p>API Dashboard © 2026</p>
        </div>
    </footer>

    <script>
        // Dashboard JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Performance Chart
            const ctx = document.getElementById('performanceChart').getContext('2d');
            const performanceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
                    datasets: [{
                        label: 'API Anfragen',
                        data: [30, 45, 65, 80, 95, 70],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Widget Drag & Drop (einfache Simulation)
            let draggedElement = null;

            document.querySelectorAll('.bg-white.rounded-lg.shadow-lg.p-4').forEach(widget => {
                widget.draggable = true;
                widget.addEventListener('dragstart', function(e) {
                    draggedElement = this;
                    this.style.opacity = '0.5';
                });

                widget.addEventListener('dragend', function(e) {
                    this.style.opacity = '';
                });

                widget.addEventListener('dragover', function(e) {
                    e.preventDefault();
                });

                widget.addEventListener('drop', function(e) {
                    e.preventDefault();
                    if (this !== draggedElement) {
                        const parent = this.parentNode;
                        const draggedParent = draggedElement.parentNode;
                        
                        if (parent && draggedParent) {
                            const temp = document.createElement('div');
                            parent.insertBefore(temp, this);
                            draggedParent.insertBefore(this, draggedElement);
                            parent.insertBefore(draggedElement, temp);
                            parent.removeChild(temp);
                        }
                    }
                });
            });

            // Simulierte Live-Updates
            function updateWidgetData() {
                // Wetter Update
                const tempElement = document.querySelector('.text-2xl.font-bold');
                if (tempElement && tempElement.textContent.includes('°C')) {
                    const newTemp = Math.floor(Math.random() * 10) + 18;
                    tempElement.textContent = `${newTemp}°C`;
                }

                // Krypto Update
                const cryptoElement = document.querySelectorAll('.text-2xl.font-bold')[1];
                if (cryptoElement && cryptoElement.textContent.includes('$')) {
                    const newPrice = Math.floor(Math.random() * 5000) + 43000;
                    cryptoElement.textContent = `$${newPrice.toLocaleString()}`;
                }

                // System Status Update
                const apiStatus = document.querySelectorAll('.text-green-600')[1];
                if (apiStatus) {
                    const isOnline = Math.random() > 0.1;
                    apiStatus.textContent = isOnline ? '✓ Online' : '✗ Offline';
                    apiStatus.className = isOnline ? 'text-sm text-green-600' : 'text-sm text-red-600';
                }
            }

            // Alle 30 Sekunden aktualisieren
            setInterval(updateWidgetData, 30000);

            console.log('Dashboard initialized successfully!');
        });
    </script>
</body>
</html>