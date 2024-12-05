wps_js.render_referrals_search_engines = function (response, key ) {
     if (response && response.response) {
        wps_js.metaBoxInner(key).html(response.response.output);
        if(response.response?.data){
            let params = response.response.data;
            const data = {
                data: params['data'],
                previousData: params['previousData']
            };
            wps_js.new_line_chart(data, `wps_${key}_meta_chart`);
        }
        wps_js.initDatePickerHandlers();
    }
    wps_js.handelReloadButton(key);
    wps_js.handelMetaBoxFooter(key,response)
};
