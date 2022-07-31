wps_js.hits_meta_box = {

    placeholder: function () {
        return wps_js.rectangle_placeholder();
    },

    view: function (args = []) {

        // Check Hit Chart size in Different Page
        let height = wps_js.is_active('overview_page') ? 110 : 210;
        if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "hits") {
            height = 80;
        }

        // Create Html
        let html = '';

        // // Check Show Button Group
        // if (wps_js.is_active('overview_page')) {
        //     html += wps_js.btn_group_chart('hits', args);
        //     setTimeout(function(){ wps_js.date_picker(); }, 1000);
        // }

        // Add Chart
        html += '<div class="o-wrap"><canvas id="' + wps_js.chart_id('hits') + '" height="' + height + '"></canvas></div>';
        html += `<div class="c-footer">
                    <div class="c-footer__filter js-widget-filters">
                    <button class="c-footer__filter__btn" onclick="jQuery(this).closest('.js-widget-filters').toggleClass('is-active')">Filter by date</button>
                    <div class="c-footer__filters">
                        <div class="c-footer__filters__current-filter">
                            <span class="c-footer__current-filter__title js-filter-title">Last 7 days</span>
                            <span class="c-footer__current-filter__date-range hs-filter-range">May 12,2020  -  May 20, 2020</span>
                        </div>
                        <div class="c-footer__filters__list">
                            <button data-filter="today" class="c-footer__filters__list-item">Today</button>
                            <button data-filter="yesterday" class="c-footer__filters__list-item">Yesterday</button>
                            <button data-filter="last7" class="c-footer__filters__list-item">Last 7 days</button>
                            <button data-filter="last30" class="c-footer__filters__list-item">Last 30 days</button>
                            <button data-filter="last90" class="c-footer__filters__list-item">Last 90 days</button>
                            <button class="c-footer__filters__list-item c-footer__filters__list-item--more" onclick="jQuery(this).closest('.c-footer__filters__list').find('.js-more-filters').addClass('is-open')">More present ranges <svg width="8" height="6" viewBox="0 0 8 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.25736 5.07544L4.32794 5.14601C4.241 5.23295 4.12308 5.28182 4.00009 5.28182C3.87715 5.28182 3.7592 5.233 3.67226 5.14604C3.67226 5.14603 3.67225 5.14603 3.67225 5.14602L0.0358041 1.50968L0.106514 1.43896L0.0358032 1.50968C-0.14526 1.32861 -0.14526 1.03507 0.0357727 0.854006M4.25736 5.07544L0.10649 0.92471M4.25736 5.07544L4.32794 5.14601L7.9642 1.50965C8.14527 1.32859 8.14527 1.03504 7.9642 0.853976C7.78317 0.67294 7.4896 0.672907 7.30853 0.853976L7.37924 0.924687M4.25736 5.07544L7.37924 0.924687M0.0357727 0.854006L0.10649 0.92471M0.0357727 0.854006C0.0357708 0.854008 0.0357689 0.85401 0.035767 0.854012L0.10649 0.92471M0.0357727 0.854006C0.126294 0.763456 0.245135 0.718189 0.363629 0.718189C0.482123 0.718189 0.600959 0.763457 0.691478 0.853975L4.00008 4.16249M0.10649 0.92471C0.177495 0.85368 0.270562 0.818189 0.363629 0.818189C0.456695 0.818189 0.549762 0.85368 0.620768 0.924686L3.92938 4.2332L4.00008 4.16249M4.00008 4.16249L7.30853 0.853977L7.37924 0.924687M4.00008 4.16249L4.0708 4.2332L7.37924 0.924687" fill="#5F6368" stroke="#5F6368" stroke-width="0.2"/></svg></button>
                            <div class="c-footer__filters__more-filters js-more-filters">
                                <button data-filter="last7" class="c-footer__filters__list-item">Last 14 days</button>
                                <button data-filter="last30" class="c-footer__filters__list-item">Last 60 days</button>
                                <button data-filter="last90" class="c-footer__filters__list-item">Last 120 days</button>
                                <button data-filter="last90" class="c-footer__filters__list-item">Last 6 months</button>
                                <button data-filter="last90" class="c-footer__filters__list-item">This year</button>
                                <button class="c-footer__filters__close-more-filters" onclick="jQuery(this).closest('.js-more-fi' + 'lters').removeClass('is-open')"><svg width="8" height="6" viewBox="0 0 8 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.25736 5.07544L4.32794 5.14601C4.241 5.23295 4.12308 5.28182 4.00009 5.28182C3.87715 5.28182 3.7592 5.233 3.67226 5.14604C3.67226 5.14603 3.67225 5.14603 3.67225 5.14602L0.0358041 1.50968L0.106514 1.43896L0.0358032 1.50968C-0.14526 1.32861 -0.14526 1.03507 0.0357727 0.854006M4.25736 5.07544L0.10649 0.92471M4.25736 5.07544L4.32794 5.14601L7.9642 1.50965C8.14527 1.32859 8.14527 1.03504 7.9642 0.853976C7.78317 0.67294 7.4896 0.672907 7.30853 0.853976L7.37924 0.924687M4.25736 5.07544L7.37924 0.924687M0.0357727 0.854006L0.10649 0.92471M0.0357727 0.854006C0.0357708 0.854008 0.0357689 0.85401 0.035767 0.854012L0.10649 0.92471M0.0357727 0.854006C0.126294 0.763456 0.245135 0.718189 0.363629 0.718189C0.482123 0.718189 0.600959 0.763457 0.691478 0.853975L4.00008 4.16249M0.10649 0.92471C0.177495 0.85368 0.270562 0.818189 0.363629 0.818189C0.456695 0.818189 0.549762 0.85368 0.620768 0.924686L3.92938 4.2332L4.00008 4.16249M4.00008 4.16249L7.30853 0.853977L7.37924 0.924687M4.00008 4.16249L4.0708 4.2332L7.37924 0.924687" fill="#5F6368" stroke="#5F6368" stroke-width="0.2"/></svg> Back</button>
                            </div>
                            <button class="c-footer__filters__list-item c-footer__filters__list-item--custom">Custom...</button>
                        </div>
                    </div>
                    </div>
                    <div class="c-footer__more">
                   <a class="c-footer__more__link" href="#">
                    Hit statistics report
                    <svg width="14" height="10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="m9.61181.611328-.71269.712722 3.17148 3.17149L0 4.49951v1.00398h12.0706L8.89912 8.67495l.71269.71272L14 4.99948 9.61181.611328Z" fill="#404BF2"/></svg>
                    </a>
                    </div>
                </div>`;

        // show Data
        return html;
    },

    meta_box_init: function (args = []) {

        // Show chart
        this.hits_chart(wps_js.chart_id('hits'), args);

        // Set Total For Hits Page
        if (wps_js.isset(wps_js.global, 'request_params', 'page') && wps_js.global.request_params.page === "hits") {
            ["visits", "visitors"].forEach(function (key) {
                let tag = "span[id^='number-total-chart-" + key + "']";
                if (wps_js.exist_tag(tag)) {
                    jQuery(tag).html(args.total[key]);
                }
            });
        }
    },

    hits_chart: function (tag_id, args = []) {

        // Check Hit-chart for Quick State
        let params = args;
        if ('hits-chart' in args) {
            params = args['hits-chart'];
        }

        // Prepare Chart Data
        let datasets = [];
        if (wps_js.is_active('visitors')) {
            datasets.push({
                label: wps_js._('visitors'),
                data: params['visitors'],
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1,
                fill: true,
                tension: 0.4
            });
        }
        if (wps_js.is_active('visits')) {
            datasets.push({
                label: wps_js._('visits'),
                data: params['visits'],
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                fill: true,
                tension: 0.4
            });
        }
        wps_js.line_chart(tag_id, params['title'], params['date'], datasets);
    }
};
