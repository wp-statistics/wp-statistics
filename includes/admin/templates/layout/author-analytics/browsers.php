<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title); ?>
            <?php if ($tooltip) : ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
    </div>
    <div class="wps-author-chart">
        <canvas id="wps-browsers" height="288"></canvas>
    </div>
</div>

<script>
    const browsersData = {
        labels: ['Chrome', 'Firefox', 'Safari', 'Opera', 'edge', 'Other'],
        datasets: [{
            data: [30, 20, 10, 5, 7, 5],
            backgroundColor: [
                '#F7D399',
                '#99D3FB',
                '#D7BDE2',
                '#D7BDE2',
                '#EBA39B',
                '#F5CBA7'
            ],
            borderColor: '#fff',
            borderWidth: 1,
        }]
    };
    const browsersOptions = {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
                hidden: false,
                labels: {
                    padding: 13,
                    fontSize: 13,
                    color: '#56585A',
                    usePointStyle: true,
                    pointStyle: 'rect',
                    pointRadius: 2
                }
            }
        }
    };
    const browsersCtx = document.getElementById('wps-browsers').getContext('2d');
    const browsersChart = new Chart(browsersCtx, {
        type: 'pie',
        data: browsersData,
        options: browsersOptions
    });
</script>