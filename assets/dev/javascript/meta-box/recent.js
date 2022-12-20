wps_js.recent_meta_box = {

    view: function (args = []) {
        let t = '';
        t += `<div class="o-table-wrapper">`;
        t += `<table width="100%" class="o-table o-table--visitors"><thead>
        <tr>
            <th></th>
            <th>${wps_js._('browser')}</th>
            ` + (wps_js.is_active('geo_ip') ? `<th>${wps_js._('country')}</th>` : ``) + `
            ` + (wps_js.is_active('geo_city') ? `<th>${wps_js._('city')}</th>` : ``) + `
            <th>${wps_js._('date')}</th>
            <th>${wps_js._('hits')}</th>
            <th>${wps_js._('ip')}</th>
            <th>${wps_js._('referrer')}</th>
        </tr></thead><tbody>`;

        args.forEach(function (value, index) {
            t += `<tr>
            <td style="text-align: left !important;" class="row-id">${++index}</td>
            <td><a class="is-normal-text" href="${value['browser']['link']}" title="${value['browser']['name']}"><img src="${value['browser']['logo']}" alt="${value['browser']['name']}" class='log-tools' title='${value['browser']['name']}'/></a></td>
            ` + (wps_js.is_active('geo_ip') ? `<td><img src='${value['country']['flag']}' alt='${value['country']['name']}' title='${value['country']['name']}' class='log-tools wps-flag'/></td>` : ``) + `
            ` + (wps_js.is_active('geo_city') ? `<td>${value['city']}</td>` : ``) + `
            <td>${value['date']}</td>
            <td>${value['hits']}</td>
            <td class="o-table__link">` + (value['hash_ip'] ? value['hash_ip'] : `<a href='${value['ip']['link']}'>${value['ip']['value']}</a>`) + `</td>
            <td>${value['referred']}</td>
			</tr>`;
        });

        t += `</tbody></table>`;
        t += `</div>`;
        return t;
    }

};
