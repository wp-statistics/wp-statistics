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
           </thead>`;

        args.forEach(function (value) {
            t += `<tbody><tr>
            <td class="wps-pd-l">10 Mar, 4:15 pm</td>
            <td class="wps-pd-l">
                ${wps_js.visitor_info(value)}
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
                    `<a target="_blank" href="" title="" class="wps-link-arrow">
                        <span></span>
                    </a>`
                    : `<span aria-hidden="true">—</span><span class="screen-reader-text">Unknown</span>`) + `
            </td>
            <td class="wps-pd-l">
                <a target="_blank" href="" title="" class="wps-link-arrow">
                    <span>Home Page: Sample Page</span>
                </a>
            </td>
            <td class="wps-pd-l">${value['online_for']}</td>
            <td class="wps-pd-l">
                <a href="${value['single_url']}">12</a>
            </td>
             </tr></tbody>`;
        });

        t += `</table></div>`;
        return t;
    }

};
wps_js.visitor_info = function (value) {

    let user_info = '<ul class="wps-visitor__information--container">';
    if (Object.keys(value['browser']).length > 0) {
        user_info += `<li class="wps-visitor__information">
            <div class="wps-tooltip" title="${value['browser']['name']}">
                <a href="${value['browser']['link']}">
                    <img src="${value['browser']['logo']}" width="15" height="15">
                </a>
            </div>
        </li>`
    }

    if (value['platform']) {
        user_info += `<li class="wps-visitor__information">
            <div class="wps-tooltip" title="${value['platform']}">
                <a href="">
                    <img src="" width="15" height="15">
                </a>
            </div>
        </li>`
    }

    if (typeof value['user'] != 'undefined') {
        user_info += `
    <li class="wps-visitor__information">
        <div>
             <a href="">
                <span class="wps-visitor__information__user-img"></span>
            </a>
            <a class="wps-visitor__information__user-text" href="">
                <span>getDisplayName</span>
                <span>#ID</span>
            </a>
             <div class="wps-tooltip" data-tooltip-content="#tooltip_user_id">
                <a href=""><span class="wps-visitor__information__user-img"></span></a>
            </div>
            <div class="wps-tooltip_templates">
                <div id="tooltip_user_id">
                    <div>
                    ${wps_js._('id')}: ID
                    </div>
                    <div>
                        ${wps_js._('name')}: name
                    </div>

                    <div>
                        ${wps_js._('email')}: email
                     </div>

                    <div>
                        ${wps_js._('ip')}: IP
                    </div>
                </div>
            </div>
         </div>
    </li>
     <li class="wps-visitor__information">
        <div>
             <a href="">
                <span class="wps-visitor__information__incognito-img"></span>
            </a>
            <span class="wps-visitor__information__incognito-text">
                ip
            </span>
             <div class="wps-tooltip" title="">
                <a href=""><span class="wps-visitor__information__incognito-img"></span></a>
            </div>
         </div>
    </li>
    `;
    }
    user_info += `</ul>`;
    user_info += ` <div class="wps-visitor__information__user-more-info">
        <div>
             ${wps_js._('email')}: email
        </div>
    
        <div>
            ${wps_js._('role')}: role   
        </div>
    </div>`;

    return user_info;
}