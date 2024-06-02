const adminMenuMonthChart = document.getElementById('wpStatistic-adminMenu__monthChart').getContext('2d');
// Sample visitors data
const adminMenuMonthChartLabels = [
    '2024-05-01', '2024-05-02', '2024-05-03', '2024-05-04', '2024-05-05', '2024-05-06', '2024-05-07', '2024-05-08', '2024-05-09', '2024-05-10',
    '2024-05-11', '2024-05-12', '2024-05-13', '2024-05-14', '2024-05-15', '2024-05-16', '2024-05-17', '2024-05-18', '2024-05-19', '2024-05-20',
    '2024-05-21', '2024-05-22', '2024-05-23', '2024-05-24', '2024-05-25', '2024-05-26', '2024-05-27', '2024-05-28', '2024-05-29', '2024-05-30'
];
const adminMenuMonthChartPageViews = [
    10000, 11000, 12000, 13000, 14000, 15000, 16000, 17000, 18000, 19000,
    20000, 21000, 22000, 23000, 24000, 25000, 26000, 27000, 28000, 29000,
    30000, 31000, 32000, 33000, 34000, 35000, 36000, 37000, 38000, 39000
];
const adminMenuMonthChartVisitors = [
    7000, 7400, 7800, 8200, 8600, 9000, 9400, 9800, 10200, 10600,
    11000, 11400, 11800, 12200, 12600, 13000, 13400, 13800, 14200, 14600,
    15000, 15400, 15800, 16200, 16600, 17000, 17400, 17800, 18200, 18600
];

const monthChart = new Chart(adminMenuMonthChart, {
    type: 'bar',
    data: {
        labels: adminMenuMonthChartLabels,
        datasets: [{
            label: 'Page Views',
            data: adminMenuMonthChartPageViews,
            backgroundColor: 'rgba(255, 255, 255, 0.2)',
            borderWidth: 0,
            borderRadius: 10,
            hoverBackgroundColor: 'rgba(255, 255, 255, 1)',
        }]
    },
    options: {
        responsive:true,
        maintainAspectRatio:false,
        animation: false,
        scales: {
            x: {
                display: false,
            },
            y: {
                display: false,
             }
        },
        plugins: {
            legend: {
                display: false,
            },
            tooltip: {
                displayColors:false,
                callbacks: {
                    title: (tooltipItems) => {
                        const date = tooltipItems[0].label;
                        return `${new Date(date).toLocaleDateString('en-GB', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        })}`;
                    },
                    label: (tooltipItem) => {
                        const index = tooltipItem.dataIndex;
                        const visitors = adminMenuMonthChartVisitors;
                        return [
                            `Visitors: ${visitors[index].toLocaleString()}k`,
                            `Views: ${tooltipItem.raw.toLocaleString()}k`
                        ];
                    }
                }
            },

        },
     }

})

