import { Chart as ChartJS, CategoryScale, LinearScale, BarController, BarElement, Tooltip, Legend } from 'chart.js';
import { Bar } from 'react-chartjs-2';

ChartJS.register(CategoryScale, LinearScale, BarController, BarElement, Tooltip, Legend);

const ChartElement = ({ data }) => {
    let postChartData = [];
    let postChartSettings = [];
    let $postChartColor = '#A5AAEA';
    let $postChartStroke = '#2C36D7';
    let $postChartLabel = 'Visitors';
    let gradient;
    let type = 'bar';

    if (typeof (data.postChartData) !== 'undefined' && data.postChartData !== null) {
        postChartData = data.postChartData;
    }
    if (typeof (data.postChartSettings) !== 'undefined' && data.postChartSettings !== null) {
        postChartSettings = data.postChartSettings;
        if (postChartSettings.color) $postChartColor = postChartSettings.color;
        if (postChartSettings.border) $postChartStroke = postChartSettings.border;
        if (postChartSettings.label) $postChartLabel = postChartSettings.label;
    }

    const externalTooltipHandler = (context) => {
        const { chart, tooltip } = context;

        let tooltipEl = chart.canvas.parentNode.querySelector('div');
        if (!tooltipEl) {
            tooltipEl = document.createElement('div');
            tooltipEl.classList.add('wps-mini-chart-post-summary-tooltip');
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
            const { offsetLeft: positionX, offsetTop: positionY, offsetWidth: canvasWidth, offsetHeight: canvasHeight } = chart.canvas;
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
                        return postChartData[tooltipItems[0].label].fullDate;
                    },
                    label: (tooltipItem) => {
                        const count = tooltipItem.formattedValue;
                        return `<div class="content-itemss"> <div class="content-item"><span>${$postChartLabel}</span> <span>${count}</span></div>`;
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
                    maxTicksLimit: 4,
                    fontColor: '#898A8E',
                    fontSize: 11,
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

    const hex_to_rgba = (hex, opacity) => {
        hex = hex.replace('#', '');
        let hex_to_rgba_r = parseInt(hex.substring(0, 2), 16);
        let hex_to_rgba_g = parseInt(hex.substring(2, 4), 16);
        let hex_to_rgba_b = parseInt(hex.substring(4, 6), 16);
        return `rgba(${hex_to_rgba_r}, ${hex_to_rgba_g}, ${hex_to_rgba_b}, ${opacity})`;
    }
    const getBackgroundColor = (value) => value.hits === 0 ? '#000000b3' : hex_to_rgba($postChartColor, 0.5);
    const getHoverBackgroundColor = (value) => value.hits === 0 ? '#000000b3' : $postChartColor;
    const backgroundColors = type === 'line' ? gradient : Object.entries(postChartData).map(getBackgroundColor);
    const hoverBackgroundColors = type === 'line' ? gradient : Object.entries(postChartData).map(getHoverBackgroundColor);
    const borderColor = $postChartStroke;

    const chartData = {
        labels: Object.entries(postChartData).map(([date, stat]) => date),
        datasets: [{
            data: Object.entries(postChartData).map(([date, stat]) => stat.hits),
            backgroundColor: backgroundColors,
            hoverBackgroundColor: hoverBackgroundColors,
            pointBackgroundColor: borderColor,
            fill: type === 'line',
            barPercentage: 0.9,
            categoryPercentage: 1.0,
            tension: 0.5,
            minBarLength: 1,
            borderWidth: type === 'line' ? 1 : 0,
            pointRadius: type === 'line' ? 0 : undefined,
            pointHoverRadius: type === 'line' ? 5 : undefined
        }],
    };

    return (
        <div className="wp-statistics-post-summary-panel-chart">
            <Bar
                data={chartData}
                options={chartOptions}
            />
        </div>
    );
};

export default ChartElement;
