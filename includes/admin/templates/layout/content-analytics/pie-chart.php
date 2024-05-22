<?php
$unique_id     = 'chart_' . preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($title_text));
$visitors_text = esc_html__('Visitors :', 'wp-statistics');
?>

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
        <canvas id="<?php echo $unique_id; ?>" height="288"></canvas>
    </div>
</div>
<script>

    const label_callback_<?php echo $unique_id; ?> = function (tooltipItem) {
        return tooltipItem.label;
    }
    const tooltip_callback_<?php echo $unique_id; ?> = (ctx) => {
        return '<?php echo $visitors_text; ?>' + ctx[0].formattedValue
    }
    const data_<?php echo $unique_id; ?> = {
        labels:  <?php echo json_encode($labels); ?>,
        datasets: [{
            data:  <?php echo json_encode($data); ?>,
            backgroundColor: <?php echo json_encode($background_color); ?>,
            borderColor: '#fff',
            borderWidth: 1,
        }]
    };
    const options_<?php echo $unique_id; ?> = {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
                hidden: false,
                labels: {
                    padding: 13,
                    fontSize: 13,
                    fontWeight: 500,
                    color: '#56585A',
                    usePointStyle: true,
                    pointStyle: 'rect',
                    pointRadius: 2
                }
            },
            tooltip: {
                enable: true,
                callbacks: {
                    label: label_callback_<?php echo $unique_id; ?>,
                    title: tooltip_callback_<?php echo $unique_id; ?>
                }
            }
        }
    };
    const ctx_<?php echo $unique_id; ?> = document.getElementById('<?php echo $unique_id; ?>').getContext('2d');
    const chart_<?php echo $unique_id; ?> = new Chart(ctx_<?php echo $unique_id; ?>, {
        type: 'pie',
        data: data_<?php echo $unique_id; ?>,
        options: options_<?php echo $unique_id; ?>
    });
</script>