wps_js.countries_meta_box = {

    view: function (args = []) {
        let t = '';
        t += `<table width="100%" class="o-table table-stats wps-report-table"><tbody>
        <tr>
            <td width="10%"></td>
            <td width="40%">${wps_js._('country')}</td>
            <td width="40%">${wps_js._('visitor_count')}</td>
        </tr>`;

        let i = 1;
        args.countries.forEach(function (value) {
            t += `<tr>
			<td class="row-id">${i}</td>
			<td><img src="${value['flag']}" title="${value['name']}" alt="${value['name']}" class="wps-flag wps-flag--first"/> ${value['name']}</td>
			<td><a href="${value['link']}" title="${value['name']}" target="_blank">${wps_js.number_format(value['number'])}</a></td>
			</tr>`;
            i++;
        });

        t += `</tbody></table>`;
        return t;
    }

};