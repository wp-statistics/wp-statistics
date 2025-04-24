const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const path = require("path");

module.exports = {
    ...defaultConfig,
    entry: {
        "react-bundle": path.resolve(process.cwd(), "./assets/js/react/pages/data-migration/index.js"),
    },
    output: {
        path: path.resolve(process.cwd(), "build"),
        filename: "[name].js",
    },
};
