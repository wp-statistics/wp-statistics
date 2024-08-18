import { Chart as ChartJS, CategoryScale, LinearScale, BarController, BarElement, Tooltip, Legend } from 'chart.js';
import { Bar } from 'react-chartjs-2';

ChartJS.register(CategoryScale, LinearScale, BarController, BarElement, Tooltip, Legend);

const ChartElement = ({ data }) => {
    data.postChartData = [
        {
            id: 1,
            views: 2415,
            shortDate: '1 Aug',
            fullDate: '01 August 2024',
        },
        {
            id: 2,
            views: 2068,
            shortDate: '2 Aug',
            fullDate: '02 August 2024',
        },
        {
            id: 3,
            views: 1684,
            shortDate: '3 Aug',
            fullDate: '03 August 2024',
        },
        {
            id: 4,
            views: 3451,
            shortDate: '4 Aug',
            fullDate: '04 August 2024',
        },
        {
            id: 5,
            views: 520,
            shortDate: '5 Aug',
            fullDate: '05 August 2024',
        }
    ];
    let postChartData = [];
    if (typeof (data.postChartData) !== 'undefined' && data.postChartData !== null) {
        postChartData = data.postChartData;
    }

    const externalTooltipHandler = (context) => {
        const { chart, tooltip } = context;

        let tooltipEl = chart.canvas.parentNode.querySelector('div');
        if (!tooltipEl) {
            tooltipEl = document.createElement('div');
            tooltipEl.classList.add('wps-mini-chart-list-tooltip');
            chart.canvas.parentNode.appendChild(tooltipEl);
        }

        if (tooltip.opacity === 0) {
            tooltipEl.style.opacity = 0;
            return;
        }

        if (tooltip.body) {
            const titleLines = tooltip.title || [];
            const bodyLines = tooltip.body.map(b => b.lines);
            let innerHtml = `<div>`;

            // Title
            titleLines.forEach(title => {
                innerHtml += `<div class="chart-title">${title}</div>`;
            });

            bodyLines.forEach((body, i) => {
                const line = body.join(': ');
                innerHtml += `<div>${line}</div>`;
            });

            tooltipEl.innerHTML = innerHtml;
            const { offsetLeft: positionX, offsetTop: positionY ,offsetWidth: canvasWidth , offsetHeight: canvasHeight } = chart.canvas;
             tooltipEl.style.opacity = bodyLines[0].length === 0 ? 0 : 1;
             const tooltipWidth = tooltipEl.offsetWidth;

            let left = positionX + tooltip.caretX - tooltipWidth / 2;
            let top = positionY + canvasHeight;

            if (left < positionX) {
                left = positionX;
            }

            if (left + tooltipWidth > positionX + canvasWidth) {
                left = positionX + canvasWidth - tooltipWidth;
            }

            tooltipEl.style.left = `${left}px`;
            tooltipEl.style.top = `${top}px`;
        }
    };

    const chartOptions = {
        animation: false,
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false,
            },
            tooltip: {
                enabled: false,
                displayColors: false,
                position: 'nearest',
                intersect: false,
                external: externalTooltipHandler,
                callbacks: {
                    title: (tooltipItems) => {
                        return postChartData[tooltipItems[0].dataIndex].fullDate;
                    },
                    label: (tooltipItem) => {
                        const count = tooltipItem.formattedValue;
                        if (tooltipItem.label === '-1') {
                            return null;
                        } else {
                            return `<div class="content-itemss"> <div class="content-item"><span>Views</span> <span>${count}</span></div>`;
                        }
                    },
                },
            },
        },
        scales: {
            x: {
                offset: true,
                grid: {
                    display: false,
                },
                border: {
                    color: 'transparent',
                    width: 0
                },
                ticks: {
                    align: 'inner',
                    maxTicksLimit: 4,
                    fontColor: '#898A8E',
                    fontSize: 12,
                    padding: 5,
                }
            },
            y: {
                position: 'right',
                beginAtZero: true,
                grid: {
                    display: false,
                },
                border: {
                    color: 'transparent',
                    width: 0
                },
                ticks: {
                    align: 'inner',
                    maxTicksLimit: 5,
                    fontColor: '#898A8E',
                    fontSize: 12,
                    padding: 8,
                }
            }
        },
        layout: {
            padding: {
                left: 0,
                right: 0,
                top: 0,
                bottom: 0,
            },
        }
    };

    const chartData = {
        labels: postChartData.map(stat => stat.shortDate),
        datasets: [{
            data: postChartData.map(stat => stat.views),
            borderColor: '#0D0725',
            backgroundColor: 'rgba(115, 98, 191, 0.5)',
            pointBackgroundColor: '#0D0725',
            fill: true,
            barPercentage: 0.9,
            categoryPercentage: 1.0,
            tension: 0.5,
            minBarLength: 1,
            borderWidth: 1,
            pointRadius: 0,
            pointHoverRadius: 5,
        }],
    };

    return (
        <div className="wp-statistics-block-editor-panel-chart">
            <Bar
                data={chartData}
                options={chartOptions}
            />
        </div>
    );
};

export default ChartElement;
