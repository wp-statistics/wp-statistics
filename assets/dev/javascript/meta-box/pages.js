wps_js.pages_meta_box = {

    view: function (args = []) {
        let t = '';
        if (args.pages && args.pages.length > 0) {
            t += `<div class="o-table-wrapper"><table width="100%" class="o-table wps-new-table">
        <thead>
            <tr>
                <th scope="col" class="wps-pd-l">${wps_js._('title')}</th>
                <th scope="col" class="wps-pd-l"><span class="wps-order">${wps_js._('views')}</span></th>
                <th scope="col"><span class="screen-reader-text">Details</span></th>
            </tr>
        </thead>
        <tbody> `;
            const siteUrl = wps_js.global.admin_url.replace('/wp-admin/', '');
            let i = 1;
            args.pages.forEach(function (value) {
                const statisticsPagesWidget = document.getElementById('wp-statistics-pages-widget');
                let viewContentLabel = wps_js._('view');
                if (statisticsPagesWidget) {
                    const isInsideDashboardWidgets = statisticsPagesWidget.closest('#dashboard-widgets') !== null;
                    viewContentLabel = isInsideDashboardWidgets ? wps_js._('view') : window.innerWidth <= 500 ? wps_js._('view') : wps_js._('view_content');
                }
                t += `<tr>
			<td class="wps-pd-l"><div class="wps-ellipsis-parent" title="${value['title']}"><span class="wps-ellipsis-text">${value['title']}</span></div></td>
			<td class="wps-pd-l"><a href="${value['hits_page']}">${value['number']}</a></td>
		    <td class="wps-pd-l"><a target="_blank" class="wps-view-content" href="${value['link']}">${viewContentLabel}</a></td>
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
window.addEventListener('resize', function () {
    window.addEventListener('resize', function () {
        const statisticsPagesWidget = document.getElementById('wp-statistics-pages-widget');
        let isInsideDashboardWidgets = null;
        if (statisticsPagesWidget) {
            isInsideDashboardWidgets = statisticsPagesWidget.closest('#dashboard-widgets') !== null;
        }
        const metaBoxes = document.querySelectorAll('.wps-view-content');
        if (metaBoxes.length > 0) {
            metaBoxes.forEach(function (metaBox) {
                metaBox.textContent = isInsideDashboardWidgets ? wps_js._('view') : window.innerWidth <= 500 ? wps_js._('view') : wps_js._('view_content');
                ;
            });
        }
    });
});