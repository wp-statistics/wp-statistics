<?php
function generateChartData() {
    $data   = [];
    $end    = time();
    $start  = strtotime('-365 days');

    while ($start <= $end) {
        $data[] = [
            'x' => date('Y-m-d', $start),
            'y' => date('N', $start),
            'd' => date('Y-m-d', $start),
            'v' => wp_rand(0, 50)
        ];

        $start += 86400;
    }
    return $data;
}

$data = generateChartData();
?>
<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title); ?>
            <?php if ($tooltip) : ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
        <?php if ($description) : ?>
            <p><?php echo esc_html($description); ?></p>
        <?php endif ?>
    </div>
    <div class="wps-card__chart-matrix">
        <div class="chart-container">
            <canvas id="myChart">
        </div>
        <div class="wps-card__chart-guide">
            <div class="wps-card__chart-guide--items">
                <span><?php esc_html_e('Less', 'wp-statistics') ?></span>
                <ul>
                    <li class="wps-card__chart-guide--item"></li>
                    <li class="wps-card__chart-guide--item"></li>
                    <li class="wps-card__chart-guide--item"></li>
                    <li class="wps-card__chart-guide--item"></li>
                    <li class="wps-card__chart-guide--item"></li>
                </ul>
                <span><?php esc_html_e('More', 'wp-statistics') ?></span>
            </div>
        </div>
    </div>
</div>

<script>
    //setup block
    const data = {
        datasets: [{
            label: 'overview',
            data: <?php echo json_encode($data); ?>,
            backgroundColor(c) {
                const value = c.dataset.data[c.dataIndex].v;
                const alpha = (10 + value) / 60;
                const colors = ['#E8EAEE', '#B28DFF', '#5100FD', '#4915B9', '#250766'];
                const index = Math.floor(alpha * colors.length);
                let color = colors[index];
                return Chart.helpers.color(color).rgbString();
            },
            borderColor: 'transparent',
            borderWidth: 4,
            borderRadius: 2,
            boxShadow: 0,
            width(c) {
                const a = c.chart.chartArea || {};
                return ((a.right - a.left) / 53 - 1) - 2;
            },
            height(c) {
                const a = c.chart.chartArea || {};
                return ((a.bottom - a.top) / 7 - 1) - 1;
            }
        }]
    }

    //scales
    const scales = {
        y: {
            type: 'time',
            offset: true,
            time: {
                unit: 'day',
                round: 'day',
                isoWeek: 1,
                parser: 'i',
                displayFormats: {
                    day: 'iiiiii'
                }
            },
            reverse: true,
            position: 'left',
            ticks: {
                maxRotation: 0,
                autoSkip: true,
                padding: 5,
                color: '#000',
                font: {
                    size: 12
                }
            },
            grid: {
                display: false,
                drawBorder: false,
                tickLength: 0,
            },
            border: {
                display: false
            },
        },
        x: {
            type: 'time',
            offset: true,
            position: 'top',
            time: {
                unit: 'month',
                round: 'week',
                isoWeekday: 1,
                displayFormats: {
                    week: 'MMM'
                }
            },
            ticks: {
                maxRotation: 0,
                autoSkip: true,
                padding: 5,
                color: '#000000',
                font: {
                    size: 12
                },
                callback: function(value, index, values) {
                    const date = new Date(value);
                    const month = date.toLocaleString('default', {
                        month: 'short'
                    });
                    const day = date.getDate();
                    return day === 1 ? month : month + ' ' + day;
                }
            },
            border: {
                display: false
            },
            grid: {
                display: false,
                drawBorder: false,
                tickLength: 0,
            }
        }
    }


    // config
    const config = {
        type: 'matrix',
        data,
        options: {
            maintainAspectRatio: false,
            scales: scales,
            aspectRatio: 10,
            animation: false,
            plugins: {
                chartAreaBorder: {
                    borderWidth: 5,
                    borderColor: '#fff',
                },
                legend: false,
                tooltip: {
                    displayColors: false,
                    callbacks: {
                        title() {
                            return '';
                        },
                        label(context) {
                            const v = context.dataset.data[context.dataIndex];
                            return ['Date: ' + v.d, 'Value: ' + v.v.toFixed(2)];
                        }
                    }
                }
            }
        }
    };

    jQuery(document).ready(function() {
        const myChart = new Chart(
            document.getElementById('myChart'),
            config
        );
    });
    // render init block
</script>