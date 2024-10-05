wps_js.recent_meta_box = {

    view: function (args = []) {
        let t = '';
        t += `<div class="o-table-wrapper">`;
        t += `<table width="100%" class="o-table wps-new-table"><thead>
        <tr>
            <th class="wps-pd-l"><span class="wps-order">${wps_js._('last_view')}</span></th>
            <th class="wps-pd-l">${wps_js._('visitor_info')}</th>
            ` + (wps_js.is_active('geo_ip') ? `<th class="wps-pd-l">${wps_js._('location')}</th>` : ``) + `
             <th class="wps-pd-l">${wps_js._('referrer')}</th>
            <th class="wps-pd-l">${wps_js._('latest_page')}</th>
            <th class="wps-pd-l">${wps_js._('views')}</th>
        </tr></thead><tbody>`;

        args.forEach(function (value, index) {
            t += `<tr>
            <td class="wps-pd-l">${value['date']}</td>
            <td class="wps-pd-l">
                ${wps_js.visitor_info(value)}
            </td>
             </td>`
                + (wps_js.is_active('geo_ip') ? `<td class="wps-pd-l">
                <div class="wps-country-flag wps-ellipsis-parent">
                    <a href="" class="wps-tooltip" title="${value['country']['name']}">
                        <img src="${value['country']['flag']}" alt="" width="15" height="15">
                    </a>
                     <span class="wps-ellipsis-text" title="${value['country']['name']}">${value['country']['name']}</span>
                </div>
             </td>` : `-`) + `
             <td class="wps-pd-l">`
                + (value['refer'] && value['refer'] !== '' ?
                    `<a target="_blank" href="" title="${value['refer']}" class="wps-link-arrow">
                        <span>${value['refer']}</span>
                    </a>`
                    : `<span aria-hidden="true">—</span>`) + `
            </td>
            <td class="wps-pd-l">`
                + (value['page'] && value['page']['title'] !== '' ?
                    `<a target="_blank" href="${value['page']['link']}" title="${value['page']['title']}" class="wps-link-arrow">
                        <span>${value['page']['title']}</span>
                    </a>`
                    : `<span aria-hidden="true">—</span><span class="screen-reader-text">-</span>`) + `
            </td>
            <td class="wps-pd-l">
                <a href="">${value['hits']}</a>
            </td>
			</tr>`;
        });

        t += `</tbody></table>`;
        t += `</div>`;
        return t;
    }

};
