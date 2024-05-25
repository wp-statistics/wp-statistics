<?php
$user       = wp_get_current_user();
$imageSrc   = '';

if ($user) {
    $imageSrc = esc_url(get_avatar_url($user->ID));
}

$publishedChartData = [
    ['x' => 30000,  'y' => 7,  'img' => $imageSrc],
    ['x' => 40000,  'y' => 5,  'img' => $imageSrc],
    ['x' => 75000,  'y' => 15, 'img' => $imageSrc],
    ['x' => 125000, 'y' => 11, 'img' => $imageSrc],
    ['x' => 180000, 'y' => 17, 'img' => $imageSrc]
];
?>

<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title); ?>
            <?php if ($tooltip): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
    </div>
    <div class="wps-scatter-chart">
        <div class="chart-container">
            <canvas id="publishedChart"></canvas>
        </div>
    </div>
</div>
<script>
    const publishedChartData = <?php echo json_encode($publishedChartData); ?>;
    const chartImageUrls = publishedChartData.map(point => point.img);

    // Preload images
    const chartImages = chartImageUrls.map(url => {
        const img = new Image();
        img.src = url;
        return img;
    });
    let chartRendered = 0
    const afterRenderPlugin = {
        id: 'afterRenderPlugin',  // A unique id for the plugin
        afterDraw: function (chart, args, options) {
            const canvas = document.getElementById('publishedChart');
            const ctx = canvas.getContext('2d');
            chart.data.datasets.forEach((dataset, datasetIndex) => {
                dataset.data.forEach((point, index) => {
                    const img = chartImages[index % chartImages.length];
                    const x = chart.scales.x.getPixelForValue(point.x);
                    const y = chart.scales.y.getPixelForValue(point.y);
                    const radius = 15;
                    const borderWidth = 2; // Adjust border width
                    const centerX = x - radius;
                    const centerY = y - radius;

                    // Draw border circle
                    ctx.beginPath();
                    ctx.arc(x, y, radius + borderWidth, 0, 2 * Math.PI);
                    ctx.lineWidth = borderWidth * 2;
                    ctx.strokeStyle = 'rgba(81,0,253,20%)';
                    ctx.stroke();
                    ctx.closePath();

                    // Clip to the circle
                    ctx.save();
                    ctx.beginPath();
                    ctx.arc(x, y, radius, 0, 2 * Math.PI);
                    ctx.clip();

                    // Draw image
                    ctx.drawImage(img, centerX, centerY, radius * 2, radius * 2);
                    ctx.restore();
                });
            });
        }
    };

    Chart.register(afterRenderPlugin);
    Chart.Tooltip.positioners.top = function (element, eventPosition) {
        const tooltip = this;

        const {chartArea: {bottom}, scales: {x, y}} = this.chart;

            return {
            x: x.getPixelForValue(x.getValueForPixel(eventPosition.x)),
            y: y.getPixelForValue(y.getValueForPixel(eventPosition.y)) - 20,
            xAlign: 'center',
            yAlign: 'bottom'
        }
    }

    const publishedData = {
        datasets: [{
            label: 'Views/Published Posts',
            data: publishedChartData,
            backgroundColor: '#E8EAEE'
        }],
    };
    const publishedConfig = {
        type: 'scatter',
        data: publishedData,
        options: {
            responsive: true,
            pointRadius: 16,
            pointHoverRadius: 16,
            tooltipPosition: {
                x: 10,
                y: 30
            },
            layout: {
                padding: {
                    Right: 20,
                    Left: 20,
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    min: 2,
                    max: 20,
                    ticks: {
                        stepSize: 4,
                        color: '#56585A',
                        fontSize: 13,
                        padding: 15,
                    },
                    title: {
                        display: true,
                        text: 'Published Posts',
                        fontSize: 14,
                        color: '#000'
                    },
                    grid: {
                        drawBorder: false,
                        tickLength: 0,
                    }
                },
                x: {
                    type: 'linear',
                    position: 'bottom',
                    min: 20000,
                    max: 200000,
                    title: {
                        display: true,
                        text: 'Post Views',
                        fontSize: 14,
                        color: '#000'
                    },
                    ticks: {
                        stepSize: 50000,
                        autoSkip: false,
                        maxRotation: 90,
                        minRotation: 90,
                        color: '#56585A',
                        padding: 15,
                        fontSize: 13
                    },
                    grid: {
                        drawBorder: false,
                        tickLength: 0,
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    position: 'top'
                }
            },
        },
        plugins: [afterRenderPlugin]
    };

    // Render chart
    const published = new Chart(
        document.getElementById('publishedChart'),
        publishedConfig
    );
</script>