<!DOCTYPE html>
<html>
<head>
    <title>IoT Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #1a1a1a;
            color: #ffffff;
            min-height: 100vh;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            padding: 2rem 0;
            background: linear-gradient(135deg, #00b4d8, #0077b6);
            margin-bottom: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        .stats-container {
            display: flex;
            gap: 20px;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 10px;
            flex: 1;
            min-width: 200px;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
            padding: 20px;
        }

        .device-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .device-card:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .device-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .device-id {
            font-size: 1.2rem;
            color: #00b4d8;
            font-weight: bold;
        }

        .last-updated {
            font-size: 0.9rem;
            color: #888;
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #4CAF50;
            box-shadow: 0 0 8px #4CAF50;
        }

        canvas {
            max-height: 250px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Smart Factory Monitoring</h1>
            <p>Real-time IoT Device Tracking</p>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Devices</h3>
                <p class="stat-value">10</p>
            </div>
            <div class="stat-card">
                <h3>Average Temperature</h3>
                <p class="stat-value" id="avg-temp">-</p>
            </div>
            <div class="stat-card">
                <h3>Average Humidity</h3>
                <p class="stat-value" id="avg-humidity">-</p>
            </div>
        </div>

        <div class="dashboard">
            @for ($i = 1; $i <= 10; $i++)
                <div class="device-card">
                    <div class="device-header">
                        <div>
                            <span class="device-id">DEVICE {{ $i }}</span>
                            <div class="last-updated" id="update-{{ $i }}"></div>
                        </div>
                        <div class="status-indicator"></div>
                    </div>
                    <canvas id="device{{ $i }}Chart"></canvas>
                </div>
            @endfor
        </div>
    </div>

    <script>
        let charts = {};

        async function fetchData() {
            try {
                const response = await fetch('/api/data');
                return await response.json();
            } catch (error) {
                console.error('Error fetching data:', error);
                return [];
            }
        }

        function calculateAverages(data) {
            const temps = data.map(d => d.temperature);
            const humids = data.map(d => d.humidity);
            
            document.getElementById('avg-temp').textContent = 
                (temps.reduce((a,b) => a + b, 0) / temps.length).toFixed(1) + '°C';
            
            document.getElementById('avg-humidity').textContent = 
                (humids.reduce((a,b) => a + b, 0) / humids.length).toFixed(1) + '%';
        }

        function updateLastUpdated(deviceId) {
            const now = new Date();
            document.getElementById(`update-${deviceId}`).textContent = 
                `Last updated: ${now.toLocaleTimeString()}`;
        }

        function createChart(deviceId, data) {
            const ctx = document.getElementById(`device${deviceId}Chart`);
            
            if (charts[deviceId]) {
                charts[deviceId].destroy();
            }

            charts[deviceId] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.map(entry => new Date(entry.created_at).toLocaleTimeString()),
                    datasets: [{
                        label: 'Temperature °C',
                        data: data.map(entry => entry.temperature),
                        borderColor: '#FF6B6B',
                        backgroundColor: 'rgba(255, 107, 107, 0.1)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 2
                    },
                    {
                        label: 'Humidity %',
                        data: data.map(entry => entry.humidity),
                        borderColor: '#4ECDC4',
                        backgroundColor: 'rgba(78, 205, 196, 0.1)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#fff'
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#aaa' },
                            grid: { color: 'rgba(255,255,255,0.1)' }
                        },
                        y: {
                            ticks: { color: '#aaa' },
                            grid: { color: 'rgba(255,255,255,0.1)' }
                        }
                    }
                }
            });
        }

        async function updateCharts() {
            const allData = await fetchData();
            calculateAverages(allData);
            
            for (let deviceId = 1; deviceId <= 10; deviceId++) {
                const deviceData = allData.filter(d => d.device_id === deviceId);
                if (deviceData.length > 0) {
                    createChart(deviceId, deviceData);
                    updateLastUpdated(deviceId);
                }
            }
        }

        // Initial load
        updateCharts();
        // Update every 5 seconds
        setInterval(updateCharts, 5000);
    </script>
</body>
</html>