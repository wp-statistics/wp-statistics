<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo $title_text ?>
            <?php if($tooltip_text):?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip_text); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif?>
        </h2>
        <?php if($title_description):?>
            <p><?php echo $title_description ?></p>
        <?php endif?>
    </div>
    <div class="wps-card__chart-matrix">
        <div class="chart-container">
            <canvas id="myChart" >
        </div>
        <div class="wps-card__chart-guide">
            <div class="wps-card__chart-guide--items">
                <span><?php echo esc_html__('Less', 'wp-statistics')?></span>
                <ul>
                    <li class="wps-card__chart-guide--item"></li>
                    <li class="wps-card__chart-guide--item"></li>
                    <li class="wps-card__chart-guide--item"></li>
                    <li class="wps-card__chart-guide--item"></li>
                    <li class="wps-card__chart-guide--item"></li>
                </ul>
                <span><?php echo esc_html__('More', 'wp-statistics')?></span>
            </div>
        </div>
    </div>
</div>
 <script>
    //date setting
    function  isoDayOfWeek(dt){
        let wd=dt.getDay(); //0...6 , from sunday
        wd=(wd+6) % 7 + 1 // 1 ..7 , starting week from monday
        return '' + wd; // string so it gets parsed
    }

    //setup date 365 days //squares
    function  generateData(){
         const d= new Date();
         const data=[];
        const end =  new Date(d.getFullYear(),d.getMonth() , d.getDate() ,0 ,0 ,0 ,0);
        let dt = new Date(new Date().setDate(end.getDate() - 365));
        while(dt  <= end){
            const iso=  dt.toISOString().substr(0, 10);
            data.push({
                x: iso,
                y: isoDayOfWeek(dt),
                d: iso,
                v: Math.random() * 50
            });
            dt = new Date(dt.setDate(dt.getDate() + 1));
         }
         console.log(data) ;
         return data;
    }

     //setup block
    const data = {
        datasets: [{
            label: 'overview',
            data: generateData(),
            backgroundColor(c) {
                const value = c.dataset.data[c.dataIndex].v;
                const alpha = (10 + value) / 60;
                const colors = ['#E8EAEE', '#B28DFF', '#5100FD', '#4915B9', '#250766'];
                const index = Math.floor(alpha * colors.length);
                let color = colors[index];
                return Chart.helpers.color(color).rgbString();
            },
            borderColor:'transparent',
            borderWidth: 4,
            borderRadius:2,
            boxShadow:0,
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
    const scales={
        y:{
            type: 'time',
            offset:true,
            time:{
                unit:'day',
                round:'day',
                isoWeek:1,
                parser:'i',
                displayFormats:{
                    day:'iiiiii'
                }
            },
            reverse: true,
            position:'left',
            ticks:{
                maxRotation: 0,
                autoSkip: true,
                padding: 5,
                color:'#000',
                font: {
                    size: 12
                }
            },
            grid:{
                display: false,
                drawBorder: false,
                tickLength: 0,
             },
            border: {
                display: false
            },
        },
        x:{
            type: 'time',
            offset:true,
            position:'top',
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
                color:'#000000',
                 font: {
                    size: 12
                },
                callback: function(value, index, values) {
                    const date = new Date(value);
                    const month = date.toLocaleString('default', { month: 'short' });
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
            maintainAspectRatio:false,
            scales: scales,
            aspectRatio:10,
            plugins:{
                chartAreaBorder: {
                    borderWidth:5,
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

    jQuery(document).ready(function () {
        const myChart = new Chart(
            document.getElementById('myChart'),
            config
        );
    });
    // render init block

 </script>