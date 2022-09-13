wps_js.pages_meta_box = {

    view: function (args = []) {
        let t = '';
        t += `<table width="100%" class="widefat table-stats wps-report-table wps-table-fixed"><tbody>
        <tr>
            <td width='10%'>${wps_js._('rank')}</td>
            <td width='40%'>${wps_js._('title')}</td>
            <td width='40%'>${wps_js._('link')}</td>
            <td width='10%'>${wps_js._('visits')}</td>
        </tr>`;

        const siteUrl = wps_js.global.admin_url.replace('/wp-admin/', '');
        let i = 1;
        args.pages.forEach(function (value) {
            t += `<tr>
			<td style='text-align: left;'>${i}</td>
			<td style='text-align: left;'><span title='${value['title']}' class='wps-cursor-default wps-text-wrap'>${value['title']}</span></td>
			<td style='text-align: left;'><a href="${siteUrl}${value['str_url']}" title="${value['title']}" target="_blank">${value['title']} <span class="dashicons dashicons-external" style="font-size: 15px; vertical-align: middle"></span></a></td>
		    <td style="text-align: left"><a href="${value['hits_page']}">${value['number']} »</a></td>
			</tr>`;
            i++;
        });

        t += `</tbody></table>`;
        return t;
    }

};