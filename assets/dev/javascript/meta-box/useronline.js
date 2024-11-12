wps_js.useronline_meta_box = {

    view: function (args = []) {
        let t = '<div class="o-table-wrapper">';
        t += `<table class="o-table wps-new-table">
           <thead>
                <tr>
                    <th class="wps-pd-l"><span class="wps-order">${wps_js._('last_view')}</span></th>
                    <th class="wps-pd-l">${wps_js._('visitor_info')}</th>
                    ` + (wps_js.is_active('geo_ip') ? `<th class="wps-pd-l">${wps_js._('location')}</th>` : ``) + `
                    <th class="wps-pd-l">${wps_js._('referrer')}</th>
                    <th class="wps-pd-l">${wps_js._('latest_page')}</th>
                    <th class="wps-pd-l">${wps_js._('online_for')}</th>
                    <th class="wps-pd-l">${wps_js._('views')}</th>
                 </tr>
           </thead><tbody>`;

        args.forEach(function (value) {
            t += `<tr>
            <td class="wps-pd-l">${value['last_view']}</td>
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
            <td class="wps-pd-l">${value['online_time']}</td>
            <td class="wps-pd-l">
                 <a href="${value['single_url']}">${value['hits']}</a>
            </td>
             </tr>`;
        });

        t += `</tbody></table></div>`;
        return t;
    }

};
wps_js.visitor_info = function (value) {
    const track_users = wps_js.isset(wps_js.global, 'options', 'track_users') ? wps_js.global['options']['track_users'] : null;
    let user_info = '<ul class="wps-visitor__information--container">';


    if (Object.keys(value['os']).length > 0) {
        user_info += `<li class="wps-visitor__information">
            <div class="wps-tooltip" title="${value['os']['name']}">
                <img src="${value['os']['logo']}" width="15" height="15">
            </div>
        </li>`
    }

    if (Object.keys(value['browser']).length > 0) {
        user_info += `<li class="wps-visitor__information">
            <div class="wps-tooltip" title="${value['browser']['name']}">
                <img src="${value['browser']['logo']}" width="15" height="15">
            </div>
        </li>`
    }
    user_info += `
    <li class="wps-visitor__information">
        <div>`;

    if (value['user'] && Object.keys(value['user']).length > 0) {
        if (track_users) {
            user_info += `<a href="${value['single_url']}">
                <span class="wps-visitor__information__user-img"></span>
            </a>
            <a class="wps-visitor__information__user-text wps-tooltip" title="${value['user']['email']} (${value['user']['role']})" href="${value['single_url']}">
                <span title="${value['user']['name']}">${value['user']['name']}</span>
                <span>#${value['ID']}</span>
            </a>`
        } else {
            user_info += `<div class="wps-tooltip" data-tooltip-content="#tooltip_user_id">
                <a href="${value['single_url']}"><span class="wps-visitor__information__user-img"></span></a>
            </div>
            <div class="wps-tooltip_templates">
                <div id="tooltip_user_id">
                    <div>
                    ${wps_js._('id')}: ${value['ID']}
                    </div>
                    <div>
                        ${wps_js._('name')}: ${value['user']['name']}
                    </div>

                    <div>
                        ${wps_js._('email')}: ${value['user']['email']}
                     </div>

                    <div>
                        ${wps_js._('ip')}: ${value['IP']}
                    </div>
                </div>
            </div>`
        }
    } else {
        if (track_users) {
            user_info += `
                <a href="${value['single_url']}">
                    <span class="wps-visitor__information__incognito-img"></span>
                </a>
                <a href="${value['single_url']}" class="wps-visitor__information__incognito-text">
                ${value['IP']}
                </a>`;
        } else {
            user_info += `
                <div class="wps-tooltip" title="  ${wps_js._('ip')}: ${value['IP']}">
                    <a href="${value['single_url']}"><span class="wps-visitor__information__incognito-img"></span></a>
                </div>`;
        }
    }
    user_info += `</div></li></ul>`;

    return user_info;
}