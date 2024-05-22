<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo $title_text ?>
            <?php if ($tooltip_text): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip_text); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
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
                    type: 'bar',
                    label: 'Published Posts',
                    data: [5, 7, 6, 5, 9, 8, 7, 6, 5, 8, 7, 6, 5, 8, 7],
                    backgroundColor: 'rgba(159,165,248,0.7)',
                    yAxisID: 'y1',
                    borderRadius: { topLeft: 10, topRight: 10 },

                },
                {
                    type: 'line',
                    label: 'Views',
                    cubicInterpolationMode: 'monotone',
                    data: [10, 15, 20, 25, 30, 35, 30, 25, 20, 15, 10, 15, 20, 25, 30],
                    borderColor: '#0e9444',
                    backgroundColor: '#0e9444',
                    pointRadius: 5,
                    pointHoverBackgroundColor: '#fff',
                    pointBackgroundColor: '#fff',
                    pointHoverBorderWidth: 4,
                    fill: false,
                    yAxisID: 'y'
                },
                {
                    type: 'line',
                    label: 'Visitors',
                    cubicInterpolationMode: 'monotone',
                    data: [5, 10, 15, 20, 25, 30, 25, 20, 15, 10, 5, 10, 15, 20, 25],
                    borderColor: '#4915b9',
                    backgroundColor: '#4915b9',
                    pointRadius: 5,
                    pointHoverBackgroundColor: '#fff',
                    pointBackgroundColor: '#fff',
                    pointHoverBorderWidth: 4,
                    fill: false,
                    yAxisID: 'y'
                }
            ]
        },
        options: {
            scales: {
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