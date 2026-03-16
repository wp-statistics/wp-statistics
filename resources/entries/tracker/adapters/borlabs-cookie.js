/**
 * Borlabs Cookie Adapter
 *
 * Integrates with Borlabs Cookie consent management.
 */

export default {
    init: function (params) {
        var levels = params.levels;
        var addFilter = params.addFilter;

        addFilter('trackingLevel', function () {
            return params.anonymousTracking ? levels.anonymous : levels.full;
        });
    }
};
