if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "referrals") {
    // Add Income Visitor Chart

     if (document.getElementById('incomeVisitorChart')) {
         const parentElement = jQuery('#incomeVisitorChart').parent();
         const placeholder = wps_js.rectangle_placeholder();
         parentElement.append(placeholder);

         const searchData ={
             data:{
                 datasets: [
                     {
                         "label": "Total",
                         "data": [
                             0,
                             0,
                             0,
                             0,
                             0,
                             0,
                             0
                         ]
                     },
                     {
                         "label": "Google",
                         "data": [
                             0,
                             0,
                             0,
                             0,
                             0,
                             0,
                             0
                         ]
                     }
                 ],
                 labels:[
                     {
                         "date": "Sep 2",
                         "day": "Monday"
                     },
                     {
                         "date": "Sep 3",
                         "day": "Tuesday"
                     },
                     {
                         "date": "Sep 4",
                         "day": "Wednesday"
                     },
                     {
                         "date": "Sep 5",
                         "day": "Thursday"
                     },
                     {
                         "date": "Sep 6",
                         "day": "Friday"
                     },
                     {
                         "date": "Sep 7",
                         "day": "Saturday"
                     },
                     {
                         "date": "Sep 8",
                         "day": "Sunday"
                     }
                 ]
             },
             previousData:{
                 datasets: [
                     {
                         "label": "Total",
                         "data": [
                             0,
                             0,
                             0,
                             0,
                             0,
                             0,
                             0
                         ]
                     }
                 ],
                 labels:[
                     {
                         "date": "Sep 2",
                         "day": "Monday"
                     },
                     {
                         "date": "Sep 3",
                         "day": "Tuesday"
                     },
                     {
                         "date": "Sep 4",
                         "day": "Wednesday"
                     },
                     {
                         "date": "Sep 5",
                         "day": "Thursday"
                     },
                     {
                         "date": "Sep 6",
                         "day": "Friday"
                     },
                     {
                         "date": "Sep 7",
                         "day": "Saturday"
                     },
                     {
                         "date": "Sep 8",
                         "day": "Sunday"
                     }
                 ]
             }
         };
        if (!searchData?.data?.datasets || searchData.data.datasets.length === 0) {
            parentElement.html(wps_js.no_results());
            jQuery('.wps-ph-item').remove();

        } else {
            jQuery('.wps-ph-item').remove();
            jQuery('.wps-postbox-chart--data').removeClass('c-chart__wps-skeleton--legend');
            parentElement.removeClass('c-chart__wps-skeleton');
            const data = {
                data: {
                    labels: searchData.data.labels,
                    ...searchData.data.datasets.reduce((acc, item) => {
                        acc[item.label] = item.data;
                        return acc;
                    }, {})
                },
                previousData: {
                    labels: searchData.previousData.labels,
                    ...searchData.previousData.datasets.reduce((acc, item) => {
                        acc[item.label] = item.data;
                        return acc;
                    }, {})
                }
            };
            wps_js.new_line_chart(data, 'incomeVisitorChart', null)
        }
    }
}
