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
        <canvas id="search-engines-chart" height="222"></canvas>
    </div>
</div>
<script>
    const lineChart = document.getElementById("search-engines-chart").getContext('2d');
    const searchEnginesOptions = {
        borderWidth: 2,
        cubicInterpolationMode: 'monotone',
        pointRadius: 2,
        pointHoverRadius: 5,
        pointHoverBackgroundColor: '#fff',
        pointHoverBorderWidth: 4,
        fill: true,
        responsive: true
    };

    const labels = [
        "17 Mar", "18 Mar", "19 Mar", "20 Mar", "21 Mar", "22 Mar", "23 Mar",
        "24 Mar", "25 Mar", "26 Mar", "27 Mar", "28 Mar", "29 Mar", "30 Mar", "31 Mar",
        "1 Apr", "2 Apr", "3 Apr", "4 Apr", "5 Apr", "6 Apr", "7 Apr", "8 Apr", "9 Apr",
        "10 Apr", "11 Apr", "12 Apr", "13 Apr", "14 Apr", "15 Apr", "16 Apr"
    ];
    const data = {
        labels: labels,
        datasets: [
            {
                label: 'Bing',
                data: [5, 10, 2, 7, 6, 5, 3, 8, 4, 7, 6, 5, 6, 9, 4, 7, 6, 5, 6, 7, 8, 9, 6, 4, 5, 6, 8, 9, 7, 6, 5],
                backgroundColor: 'rgba(244, 161, 31, 0.3)',
                borderColor: 'rgba(244, 161, 31, 1)',
                ...searchEnginesOptions
            },
            {
                label: 'DuckDuckGo',
                data: [3, 8, 5, 7, 6, 5, 4, 7, 6, 5, 6, 7, 5, 8, 6, 7, 6, 5, 7, 8, 6, 5, 4, 6, 7, 8, 9, 6, 5, 4, 6],
                backgroundColor: 'rgba(63, 158, 221, 0.3)',
                borderColor: 'rgba(63, 158, 221, 1)',
                ...searchEnginesOptions
            },
            {
                label: 'Google',
                data: [36, 45, 38, 35, 30, 25, 24, 37, 32, 28, 27, 30, 29, 38, 32, 35, 29, 28, 30, 35, 36, 37, 30, 28, 29, 33, 40, 37, 36, 32, 31],
                backgroundColor: 'rgba(195, 68, 55, 0.3)',
                borderColor: 'rgba(195, 68, 55, 1)',
                ...searchEnginesOptions
            },
            {
                label: 'Yahoo',
                data: [4, 6, 3, 7, 6, 5, 4, 6, 7, 8, 5, 4, 6, 7, 8, 9, 6, 5, 4, 6, 7, 8, 9, 5, 4, 6, 7, 8, 9, 6, 5],
                backgroundColor: 'rgba(160, 98, 186, 0.3)',
                borderColor: 'rgba(160, 98, 186, 1)',
                ...searchEnginesOptions
            },
            {
                label: 'Yandex',
                data: [6, 9, 6, 7, 6, 5, 7, 8, 6, 5, 4, 6, 7, 8, 9, 6, 5, 7, 8, 9, 6, 5, 4, 6, 7, 8, 9, 6, 5, 4, 6],
                backgroundColor: 'rgba(51, 178, 105, 0.3)',
                borderColor: 'rgba(51, 178, 105, 1)',
                ...searchEnginesOptions
            },
            {
                label: 'Total',
                data: [26, 45, 28, 35, 30, 25, 24, 37, 32, 28, 27, 30, 29, 38, 32, 35, 29, 28, 30, 35, 36, 37, 30, 38, 30, 33, 40, 37, 36, 32, 31],
                backgroundColor: 'rgba(185, 185, 185, 0.3)',
                borderColor: 'rgba(185, 185, 185, 1)',
                ...searchEnginesOptions
            }
        ]
    };

    new Chart(lineChart, {
        type: 'line',
        data: data,
        options: {
            plugins: {
                tooltip: {
                    caretPadding: 5,
                    boxWidth: 5,
                    usePointStyle: true,
                    boxPadding: 3
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                }
            }
        }
    });

</script>