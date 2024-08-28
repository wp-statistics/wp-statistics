wps_js.pages_meta_box = {

    view: function (args = []) {
        let t = '';
        if (args.pages && args.pages.length > 0) {
            t += `<div class="o-table-wrapper"><table width="100%" class="o-table wps-new-table o-table--pages ">
        <thead>
            <tr>
                <th class="wps-pd-l">${wps_js._('title')}</th>
                <th class="wps-pd-l"><span class="wps-order">${wps_js._('visitors')}</span></th>
                <th></th>
            </tr>
        </thead>
        <tbody> `;
            const siteUrl = wps_js.global.admin_url.replace('/wp-admin/', '');
            let i = 1;
            args.pages.forEach(function (value) {
                t += `<tr>
			<td class="wps-pd-l"><div class="wps-ellipsis-parent" title="${value['title']}"><span class="wps-ellipsis-text">${value['title']}</span></div></td>
			<td class="wps-pd-l"><a href="${value['hits_page']}">${value['number']}</a></td>
		    <td class="wps-pd-l"><a target="_blank" class="wps-view-content" href="${value['link']}">${wps_js._('view_content')}</a></td>
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