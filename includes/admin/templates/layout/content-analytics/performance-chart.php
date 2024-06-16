<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo $title_text ?>
            <?php if ($tooltip_text): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip_text); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
        <p><?php echo $description_text ?></p>
    </div>
    <div class="wps-content-analytics-chart-items">
        <div class="wps-content-analytics-chart--item wps-content-analytics-chart--item--published">
            <p><?php echo esc_html__('Published Posts', 'wp-statistics')?></p>
            <span>126</span>
        </div>
        <div class="wps-content-analytics-chart--item wps-content-analytics-chart--item--views">
            <p><?php echo esc_html__('Views', 'wp-statistics')?></p>
            <span>352.3K</span>
        </div>
        <div class="wps-content-analytics-chart--item wps-content-analytics-chart--item--visitors">
            <p><?php echo esc_html__('Visitors', 'wp-statistics')?></p>
            <span>105.4K</span>
        </div>
    </div>
    <div class="wps-content-analytics-chart">
        <canvas id="performance-chart" height="299"></canvas>
    </div>
 </div>
<script>
    const ctx = document.getElementById('performance-chart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['1 Apr', '2 Apr', '3 Apr', '4 Apr', '5 Apr', '6 Apr', '7 Apr', '8 Apr', '9 Apr', '10 Apr', '11 Apr', '12 Apr', '13 Apr', '14 Apr', '15 Apr'],
            datasets: [
                {
                    type: 'line',
                    label: 'Views',
                    cubicInterpolationMode: 'monotone',
                    data: [10, 15, 20, 25, 30, 35, 30, 45, 20, 15, 45, 15, 20, 25, 30],
                    borderColor: '#0e9444',
                    backgroundColor: '#0e9444',
                    pointRadius: 5,
                    pointStyle:'circle',
                    fill: false,
                    yAxisID: 'y',
                    pointBorder:5,
                    pointBorderColor: '#fff',
                    pointWidth:5.5,
                    pointHeight:5.5,
                    pointBackgroundColor:'#0e9444',
                    lineTension: 0.5
                },
                {
                    type: 'line',
                    label: 'Visitors',
                    cubicInterpolationMode: 'monotone',
                    data: [5, 10, 15, 20, 25, 30, 25, 20, 15, 10, 5, 10, 15, 20, 25],
                    borderColor: '#4915b9',
                    backgroundColor: '#4915b9',
                    pointRadius: 5,
                    fill: false,
                    yAxisID: 'y',
                    pointBorder:5,
                    pointBorderColor: '#fff',
                    pointWidth:5.5,
                    pointHeight:5.5,
                    pointBackgroundColor:'#4915b9',
                    lineTension: 0.5
                },
                {
                    type: 'bar',
                    label: 'Published Posts',
                    data: [5, 7, 6, 5, 9, 8, 7, 6, 5, 8, 7, 6, 5, 8, 7],
                    backgroundColor: 'rgba(159,165,248,0.7)',
                    yAxisID: 'y1',
                    borderRadius: { topLeft: 10, topRight: 10 },
                },
            ]
        },
        options: {
            plugins: {
                legend: false
            },
            scales: {
                x: {
                    grid: {
                        display: true,
                        borderDash: [5, 5] // This creates dashed lines for the x-axis grid
                    }
                },
                y: {
                    type: 'linear',
                    position: 'right',
                    ticks: {
                        callback: function(value, index, values) {
                            return value + 'K';
                        }
                    },
                    title: {
                        display: true,
                        text: 'Views',
                        color: '#0e9444'
                    }
                },
                y1: {
                    type: 'linear',
                    position: 'left',
                    ticks: {
                        callback: function(value, index, values) {
                            return value;
                        }
                    },
                    title: {
                        display: true,
                        text: 'Published Posts',
                        color: '#9fa5f8',
                    }
                }
            }
        }
    });
</script>