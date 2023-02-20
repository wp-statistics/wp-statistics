wps_js.top_visitors_meta_box = {

    view: function (args = []) {
        let t = '';
        t += `<div class="o-table-wrapper">`;
        t += `<table width="100%" class="o-table o-table--responsive"><tbody>
        <tr>
            <td></td>
            <td class="o-table__td--sm-width">${wps_js._('hits')}</td>
            ` + (wps_js.is_active('geo_ip') ? `<td>${wps_js._('country')}</td>` : ``) + `
            ` + (wps_js.is_active('geo_city') ? `<td>${wps_js._('city')}</td>` : ``) + `
            <td>${wps_js._('ip')}</td>
            <td>${wps_js._('browser')}</td>
            <td>${wps_js._('platform')}</td>
            <td>${wps_js._('version')}</td>
        </tr>`;

        let i = 1;
        args.forEach(function (value) {
            t += `<tr>
            <td class="row-id">${i}</td>
            <td class="o-table__td--sm-width">${value['hits']}</td>
            ` + (wps_js.is_active('geo_ip') ? `<td><img src='${value['country']['flag']}' alt='${value['country']['name']}' title='${value['country']['name']}' class='log-tools wps-flag'/> ${value['country']['name']}</td>` : ``) + `
            ` + (wps_js.is_active('geo_city') ? `<td>${value['city']}</td>` : ``) + `
            <td class="wps-admin-column__ip">` + (value['hash_ip'] ? value['hash_ip'] : `<a href='${value['ip']['link']}'>${value['ip']['value']}</a>`) + `</td>
            <td><a class="is-normal-text" href="${value['browser']['link']}" title="${value['browser']['name']}"><img src="${value['browser']['logo']}" alt="${value['browser']['name']}" class='wps-flag log-tools' title='${value['browser']['name']}'/> ${value['browser']['name']}</a></td>
            <td>${value['platform']}</td>
            <td>${value['version']}</td>
			</tr>`;
            i++;
        });

        t += `</tbody></table>`;
        t += `</div>`;
        return t;
    }

};
