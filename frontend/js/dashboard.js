// Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
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

    // API-Funktion zum Abrufen der Daten
    async function fetchDashboardData() {
        try {
            const response = await fetch('/api/');
            const data = await response.json();
            
            if (data.success) {
                updateWidgets(data.data);
            }
        } catch (error) {
            console.error('Fehler beim Abrufen der Dashboard-Daten:', error);
        }
    }

    // Widgets mit echten Daten aktualisieren
    function updateWidgets(data) {
        // Wetter Widget
        const tempElement = document.querySelector('.text-2xl.font-bold');
        const weatherIcon = document.querySelector('.text-3xl');
        const weatherCondition = document.querySelector('.text-gray-600');
        if (data.weather) {
            tempElement.textContent = `${data.weather.temperature}°C`;
            weatherIcon.textContent = data.weather.icon;
            weatherCondition.textContent = data.weather.condition;
        }

        // Krypto Widget
        const cryptoElement = document.querySelectorAll('.text-2xl.font-bold')[1];
        const cryptoChange = document.querySelector('.text-green-600');
        if (data.crypto && data.crypto.bitcoin) {
            cryptoElement.textContent = `$${parseInt(data.crypto.bitcoin.price).toLocaleString()}`;
            cryptoChange.textContent = data.crypto.bitcoin.change >= 0 ? `+${data.crypto.bitcoin.change}%` : `${data.crypto.bitcoin.change}%`;
            cryptoChange.className = data.crypto.bitcoin.change >= 0 ? 'text-sm text-green-600' : 'text-sm text-red-600';
        }

        // Nachrichten Widget
        const newsContainer = document.querySelector('.space-y-2');
        if (data.news && data.news.headlines) {
            newsContainer.innerHTML = data.news.headlines.map(headline => `
                <div class="text-sm p-2 bg-gray-50 rounded">
                    <div class="font-medium">${headline.title}</div>
                    <div class="text-gray-600">${headline.summary}</div>
                </div>
            `).join('');
        }

        // GitHub Widget
        const githubRepos = document.querySelectorAll('.text-2xl.font-bold')[2];
        if (data.github) {
            githubRepos.textContent = data.github.repositories;
        }

        // System Status Widget
        updateSystemStatus(data.system);

        // Performance Chart Update (Daten können aus einem API-Endpunkt kommen)
        updatePerformanceChart();
    }

    // System Status aktualisieren
    function updateSystemStatus(systemData) {
        const systemContainer = document.querySelector('.space-y-2');
        if (!systemData || !systemContainer) return;

        const statusItems = [
            { label: 'API Server', key: 'api_server' },
            { label: 'Database', key: 'database' },
            { label: 'Cache', key: 'cache' }
        ];

        systemContainer.innerHTML = statusItems.map(item => {
            const isOnline = systemData[item.key] === 'online' || systemData[item.key] === 'connected' || systemData[item.key] === 'active';
            return `
                <div class="flex justify-between">
                    <span class="text-sm">${item.label}</span>
                    <span class="text-sm ${isOnline ? 'text-green-600' : 'text-red-600'}">
                        ${isOnline ? '✓ ' + systemData[item.key] : '✗ ' + systemData[item.key]}
                    </span>
                </div>
            `;
        }).join('');
    }

    // Performance Chart aktualisieren (simulierte Daten für Demo)
    function updatePerformanceChart() {
        // Hier könnten echte Performance-Daten von der API kommen
        const now = new Date();
        const hours = [];
        const requests = [];
        
        for (let i = 5; i >= 0; i--) {
            const hour = new Date(now - i * 3600000);
            hours.push(hour.getHours() + ':00');
            requests.push(Math.floor(Math.random() * 50) + 30 + (5 - i) * 10);
        }

        if (window.performanceChart) {
            window.performanceChart.data.labels = hours;
            window.performanceChart.data.datasets[0].data = requests;
            window.performanceChart.update();
        }
    }

    // Dashboard initialisieren
    async function initDashboard() {
        // Beim ersten Laden Daten von der API holen
        await fetchDashboardData();
        
        // Danach alle 30 Sekunden aktualisieren
        setInterval(fetchDashboardData, 30000);
    }

    initDashboard();

    console.log('Dashboard initialized successfully!');
});