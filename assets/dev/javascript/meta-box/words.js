wps_js.words_meta_box = {

    view: function (args = []) {
        let t = '';
        t += `<div class="o-table-wrapper">`;
        t += `<table width="100%" class="o-table"><tbody>
        <tr>
            <td>${wps_js._('word')}</td>
            <td>${wps_js._('browser')}</td>
            ` + (wps_js.is_active('geo_ip') ? `<td>${wps_js._('country')}</td>` : ``) + `
            ` + (wps_js.is_active('geo_city') ? `<td>${wps_js._('city')}</td>` : ``) + `
            <td>${wps_js._('date')}</td>
            <td>${wps_js._('ip')}</td>
            <td>${wps_js._('referrer')}</td>
        </tr>`;

        let i = 1;
        args.forEach(function (value) {
            t += `<tr>
            <td><span title='${value['word']}' class='wps-cursor-default wps-text-wrap'>${value['word']}</span></td>
            <td><a href="${value['browser']['link']}" title="${value['browser']['name']}" class="is-normal-text"><img src="${value['browser']['logo']}" alt="${value['browser']['name']}" title='${value['browser']['name']}' class="wps-flag"/> ${value['browser']['name']}</a></td>
            ` + (wps_js.is_active('geo_ip') ? `<td style="text-align: left"><img src='${value['country']['flag']}' alt='${value['country']['name']}' title='${value['country']['name']}' class='wps-flag'/> ${value['country']['name']}</td>` : ``) + `
            ` + (wps_js.is_active('geo_city') ? `<td style="text-align: left">${value['city']}</td>` : ``) + `
            <td>${value['date']}</td>
            <td>` + (value['hash_ip'] ? value['hash_ip'] : `<a href='${value['ip']['link']}'>${value['ip']['value']}</a>`) + `</td>
            <td class="wps-admin-column__referred">${value['referred']}</td>
			</tr>`;
            i++;
        });

        t += `</tbody></table>`;
        t += `</div>`;
        return t;
    }

};
