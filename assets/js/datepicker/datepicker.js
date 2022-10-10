jQuery( document ).ready(function() {
    new easepick.create({
        element: document.querySelector('.js-datepicker-input'),
        css: [
            wps_js.global.assets_url + '/css/datepicker/easepick.css',
            wps_js.global.assets_url + '/css/datepicker/customize.css',
        ],
        plugins: ['RangePlugin'],
        RangePlugin: {
            tooltipNumber(num) {
                return num;
            },
            locale: {
                one: 'Day', // @todo Ali Fallah
                other: 'Days',
            },
        },
        setup(picker) {
            picker.on('select', (e) => {
                let startDate = new Date(e.detail.start).toISOString().slice(0, 10);
                let endDate = new Date(e.detail.end).toISOString().slice(0, 10);
                wps_js.run_meta_box(key, {'from': startDate, 'to': endDate});
            });
        },
    });
});