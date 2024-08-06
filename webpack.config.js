const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
    ...defaultConfig,
    entry: {
        ...defaultConfig.entry(),
        index: './assets/dev/blocks/wp-statistics/index.js',
    },
};