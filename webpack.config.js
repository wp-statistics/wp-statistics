const path = require("path");
const TerserPlugin = require("terser-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const glob = require("glob");
const WrapperPlugin = require("wrapper-webpack-plugin");
const RemoveEmptyScriptsPlugin = require("webpack-remove-empty-scripts");
const webpack = require("webpack");
// Admin file list
const getOrderedAdminFiles = () => {
    const order = ["./assets/js/datepicker/*.js", "./assets/js/jqvmap/jquery.vmap.min.js", "./assets/js/jqvmap/jquery.vmap.world.min.js", "./assets/js/select2/select2.full.min.js", "./assets/javascript/plugin/*.js", "./assets/javascript/config.js", "./assets/javascript/ajax.js", "./assets/javascript/placeholder.js", "./assets/javascript/helper.js", "./assets/javascript/chart.js", "./assets/javascript/filters/*.js", "./assets/javascript/components/*.js", "./assets/javascript/meta-box.js", "./assets/javascript/run.js", "./assets/javascript/meta-box/*.js", "./assets/javascript/pages/*.js", "./assets/javascript/image-upload.js"];

    return order.flatMap((pattern) => {
        return glob.sync(pattern).map((file) => {
            return file.startsWith("./") ? file : `./${file}`;
        });
    });
};

// Tracker file list
const trackerFiles = ["./assets/javascript/user-tracker.js", "./assets/javascript/event-tracker.js", "./assets/javascript/tracker.js"];

module.exports = {
    mode: "production",
    entry: {
        "admin.min": getOrderedAdminFiles(),
        "tracker.min": trackerFiles,
        "app.min": "./assets/sass/app.scss",
    },
    output: {
        filename: "[name].js",
        path: path.resolve(__dirname, "assets/js"),
    },
    module: {
        rules: [
            {
                test: require.resolve("./assets/javascript/config.js"),
                loader: "expose-loader",
                options: {
                    exposes: ["wps_js"],
                },
            },
            {
                test: /\.js$/,
                exclude: [/node_modules/, /assets\/js\/jqvmap/, /assets\/dev\/javascript\/config\.js/],
                use: {
                    loader: "babel-loader",
                    options: {
                        presets: ["@babel/env"],
                    },
                },
            },
            {
                test: /\.s[ac]ss$/i,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: "css-loader",
                        options: {
                            url: false, // This prevents css-loader from handling file URLs
                        },
                    },
                    "sass-loader",
                ],
            },
        ],
    },
    plugins: [
        new WrapperPlugin({
            test: /admin\.min\.js$/, // Wrap only admin.min.js
            // header: "jQuery(document).ready(function ($) {",
            // footer: "});",
        }),
        new MiniCssExtractPlugin({
            filename: "[name].css", // ✅ Correct location
        }),
        new RemoveEmptyScriptsPlugin(),
        new webpack.ProvidePlugin({
            $: "jquery",
            jQuery: "jquery",
            "window.jQuery": "jquery",
        }),
    ],
    optimization: {
        minimize: true,
        minimizer: [
            new TerserPlugin({
                terserOptions: {
                    compress: {
                        drop_console: true,
                    },
                    format: {
                        comments: false,
                        beautify: false,
                    },
                },
                extractComments: false,
            }),
        ],
    },
    resolve: {
        modules: [
            "node_modules",
            path.resolve(__dirname), // or path.resolve(__dirname, 'src')
        ],
        extensions: [".js", ".scss"],
        alias: { "./locale": "moment/locale" },
    },
    externals: {
        jquery: "jQuery",
    },
};
