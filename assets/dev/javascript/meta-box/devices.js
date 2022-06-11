wps_js.devices_meta_box = {

    placeholder: function () {
        return wps_js.circle_placeholder();
    },

    view: function (args = []) {

        // Create Html
        let html = '';

        // Check Show Button Group
        if (wps_js.is_active('overview_page')) {
            html += wps_js.btn_group_chart('devices', args);
            setTimeout(function () {
                wps_js.date_picker();
            }, 1000);
        }

        // Add Chart
        html += '<canvas id="' + wps_js.chart_id('devices') + '" height="220"></canvas>';

        // show Data
        return html;
    },

    meta_box_init: function (args = []) {

        // Get Background Color
        let backgroundColor = [];
        let color;
        for (let i = 0; i <= 20; i++) {
            color = wps_js.random_color();
            backgroundColor.push('rgba(' + color[0] + ',' + color[1] + ',' + color[2] + ',' + '0.4)');
        }

        // Prepare Data
        let data = [{
            label: wps_js._('device'),
            data: args['device_value'],
            backgroundColor: backgroundColor,
            tension: 0.4
        }];

        // Show Chart
        wps_js.pie_chart(wps_js.chart_id('devices'), args['device_name'], data);

        // Check Table information
        if (wps_js.exist_tag('#' + wps_js.getMetaBoxKey('devices-table'))) {

            // Reset All Height
            ['devices-table', 'devices'].forEach((key) => {
                jQuery("#" + wps_js.getMetaBoxKey(key) + " .inside").removeAttr("style");
            });

            // Show Table information
            let tbl = `<div class="title-center">${args.title}</div>
                    <table width="100%" class="widefat table-stats">
                        <tr>
                            <td class="wps-text-muted">${wps_js._('device')}</td>
                            <td class="wps-text-muted">${wps_js._('visitor_count')}</td>
                            <td class="wps-text-muted">${wps_js._('percentage')}</td>
                        </tr>`;

            for (let i = 0; i < args.device_name.length; i++) {
                tbl += `
                 <tr>
                        <td>${args.device_name[i]}</td>
                        <td>${(parseInt(args.device_value[i]) > 0 ? `<a href="` + args.info.visitor_page + `&device=` + args.device_name[i] + `&from=` + args.from + `&to=` + args.to + `" target="_blank"> ${wps_js.number_format(args.device_value[i])} </a>` : wps_js.number_format(args.device_value[i]))}</td>
                        <td>${wps_js.number_format((args.device_value[i] / args.total) * 100)}%</td>
                 </tr>
                `;
            }

            // Set Total
            tbl += ` <tr><td>${wps_js._('total')}</td><td>${wps_js.number_format(args.total)}</td><td></td></tr>`;
            tbl += `</table>`;
            jQuery("#" + wps_js.getMetaBoxKey('devices-table') + " .inside").html(tbl);

            // Set Equal Height
            wps_js.set_equal_height('.postBox-table .inside', '.postBox-chart .inside');
        }

    }

};