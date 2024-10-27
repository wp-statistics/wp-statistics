wps_js.referring_meta_box = {

    view: function (args = []) {
        let t = '<div class="o-table-wrapper">';
        t += `<table width="100%" class="o-table wps-new-table"><thead>
        <tr>
            <th class="wps-pd-l">${wps_js._('address')}</th>
            <th class="wps-pd-l"><span class="wps-order">${wps_js.meta_box_lang('referring', 'references')}</span></th>
        </tr></thead><tbody>`;

        args.referring.forEach(function (value) {
            t += `<tr>
			<td class="wps-pd-l"><a class="wps-link-arrow" href='//${value['domain']}' title='${value['domain']}' target="_blank"><span>${value['domain']}</span></a></td>
			<td class="wps-pd-l wps-middle-vertical"><a style="justify-content: flex-end;" href="${value['page_link']}">${wps_js.formatNumber(value['number'])} </a></td>
			</tr>`;
        });

        t += `</tbody></table></div>`;
        return t;
    }

};
