wps_js.useronline_meta_box = {

    view: function (args = []) {
        let t = '';
        t += `<table class="o-table wps-report-table wps-table-fixed">
        <tr>
            <td width="35%" style='text-align: left;'>${wps_js._('page')}</td>
            <td style='text-align: left;'>${wps_js._('referrer')}</td>`
            + (wps_js.is_active('geo_ip') ? `<td style='text-align: left;'>${wps_js._('country')}</td>` : ``) + `
            <td style='text-align: left;'>${wps_js._('ip')}</td>
        </tr>`;

        args.forEach(function (value) {
            t += `<tr>
            <td style='text-align: left !important;'><span class="wps-text-wrap">` + (value['page']['link'].length > 2 ? `<a href="${value['page']['link']}" title="${value['page']['title']}" target="_blank" class="wps-text-muted">` : ``) + value['page']['title'] + (value['page']['link'].length > 2 ? `</a>` : ``) + `</span></td>
            <td style="text-align: left !important">${value['referred']}</td>`
                + (wps_js.is_active('geo_ip') ? `<td style="text-align: left"><img src='${value['country']['flag']}' alt='${value['country']['name']}' class='wps-flag'/> ${value['country']['name']}</td>` : ``) + `
            <td style='text-align: left !important'><a href='${value['ip']['link']}'>` + (value['hash_ip'] ? value['hash_ip'] : value['ip']['value']) + `</a></td>
			</tr>`;
        });

        t += `</table>`;
        return t;
    }

};
