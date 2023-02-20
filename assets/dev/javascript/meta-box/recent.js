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
            <th class="o-table__td--sm-width">${wps_js._('hits')}</th>
            <th class="o-table__link">${wps_js._('ip')}</th>
            <th>${wps_js._('referrer')}</th>
        </tr></thead><tbody>`;

        args.forEach(function (value, index) {
            t += `<tr>
            <td class="row-id">${++index}</td>
            <td><a class="is-normal-text" href="${value['browser']['link']}" title="${value['browser']['name']}"><img src="${value['browser']['logo']}" alt="${value['browser']['name']}" class='wps-flag log-tools' title='${value['browser']['name']}'/> ${value['browser']['name']}</a></td>
            ` + (wps_js.is_active('geo_ip') ? `<td><img src='${value['country']['flag']}' alt='${value['country']['name']}' title='${value['country']['name']}' class='wps-flag'/> ${value['country']['name']}</td>` : ``) + `
            ` + (wps_js.is_active('geo_city') ? `<td>${value['city']}</td>` : ``) + `
            <td>${value['date']}</td>
            <td class="o-table__td--sm-width">${value['hits']}</td>
            <td class="o-table__link o-table__ip">` + (value['hash_ip'] ? value['hash_ip'] : `<a href='${value['ip']['link']}'>${value['ip']['value']}</a>`) + `</td>
            <td class="o-table__referred">${value['referred']}</td>
			</tr>`;
        });

        t += `</tbody></table>`;
        t += `</div>`;
        return t;
    }

};
