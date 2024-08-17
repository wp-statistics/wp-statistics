import { Chart as ChartJS } from 'chart.js/auto';
import { CategoryScale } from 'chart.js';
import { Bar } from 'react-chartjs-2';

ChartJS.register(CategoryScale);

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

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false,
            }
        }
    };

    let postChartData = [];
    if (typeof (data.postChartData) !== 'undefined' && data.postChartData !== null) {
        postChartData = data.postChartData;
    }
    const chartData = {
        labels: postChartData.map(stat => stat.shortDate),
        datasets: [{
            data: postChartData.map(stat => stat.views),
            backgroundColor: 'rgba(115, 98, 191, 0.5)',
            borderWidth: 1,
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
