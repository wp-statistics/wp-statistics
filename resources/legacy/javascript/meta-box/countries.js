wps_js.countries_meta_box = {

    view: function (args = []) {
        let t = '';
        if (args.countries && args.countries.length > 0) {
            t += `<div class="o-table-wrapper"><table width="100%" class="o-table wps-new-table"><thead>
        <tr>
            <th class="wps-pd-l">${wps_js._('country')}</th>
            <th class="wps-pd-l"><span class="wps-order">${wps_js._('visitor_count')}</span></th>
        </tr></thead><tbody>`;

            let i = 1;
            args.countries.forEach(function (value) {
                t += `<tr>
 			<td class="wps-pd-l"><img src="${value['flag']}" title="${value['name']}" alt="${value['name']}" class="wps-flag wps-flag--first"/> ${value['name']}</td>
			<td class="wps-pd-l wps-middle-vertical"><a href="${value['link']}" title="${value['name']}" target="_blank">${wps_js.number_format(value['number'])}</a></td>
			</tr>`;
                i++;
            });

            t += `</tbody></table></div>`;
            return t;
        } else {
            return wps_js.no_meta_box_data()
        }
    }

};