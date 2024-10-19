wps_js.top_visitors_meta_box = {

    view: function (args = []) {
        let t = '';
        t += `<div class="o-table-wrapper">`;
        t += `<table width="100%" class="o-table wps-new-table"><thead>
        <tr>
            <th class="wps-pd-l"><span class="wps-order">${wps_js._('views')}</span></th>
            <th class="wps-pd-l">${wps_js._('visitor_info')}</th>
            ` + (wps_js.is_active('geo_ip') ? `<th class="wps-pd-l">${wps_js._('location')}</th>` : ``) + `
            <th class="wps-pd-l">${wps_js._('referrer')}</th>
            <th class="wps-pd-l">${wps_js._('latest_page')}</th>
            <th class="wps-pd-l">${wps_js._('last_view')}</th>
        </tr></thead><tbody>`;

        let i = 1;
        args['data'].forEach(function (value) {
            t += `<tr>
            <td class="wps-pd-l">
                <a href="${value['single_url']}">${value['hits']}</a>
            </td>
             <td class="wps-pd-l">
                ${wps_js.visitor_info(value)}
            </td>`
                + (wps_js.is_active('geo_ip') ? `<td class="wps-pd-l">
                <div class="wps-country-flag wps-ellipsis-parent">
                    <a href="" class="wps-tooltip" title="${value['location']['country']}">
                        <img src="${value['location']['flag']}" alt="" width="15" height="15">
                    </a>
                     <span class="wps-ellipsis-text" title="${value['location']['location']}">${value['location']['location']}</span>
                </div>
             </td>` : `-`) + `
            <td class="wps-pd-l">`
                + (value['referrer'] && value['referrer']['name'] !== '' ?
                    `<a target="_blank" href="${value['referrer']['link']}" title="${value['referrer']['name']}" class="wps-link-arrow">
                        <span>${value['referrer']['name']}</span>
                    </a>`
                    : `<span aria-hidden="true">—</span>`) + `
            </td>
            <td class="wps-pd-l">`
                + (value['last_page'] && value['last_page']['title'] !== '' ?
                    `<a target="_blank" href="${value['last_page']['link']}" title="${value['last_page']['title']}" class="wps-link-arrow">
                        <span>${value['last_page']['title']}</span>
                    </a>`
                    : `<span aria-hidden="true">—</span><span class="screen-reader-text">-</span>`) + `
            </td>
             <td class="wps-pd-l">${value['last_view']}</td>
			</tr>`;
            i++;
        });

        t += `</tbody></table>`;
        t += `</div>`;
        return t;
    }

};
