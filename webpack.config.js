const path = require("path");
const TerserPlugin = require("terser-webpack-plugin");
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const glob = require("glob");
const WrapperPlugin = require("wrapper-webpack-plugin");
const RemoveEmptyScriptsPlugin = require("webpack-remove-empty-scripts");

// Admin file list
const getOrderedAdminFiles = () => {
    const order = ["./assets/dev/javascript/plugin/*.js", "./assets/dev/javascript/config.js", "./assets/dev/javascript/ajax.js", "./assets/dev/javascript/placeholder.js", "./assets/dev/javascript/helper.js", "./assets/dev/javascript/chart.js", "./assets/dev/javascript/filters/*.js", "./assets/dev/javascript/components/*.js", "./assets/dev/javascript/meta-box.js", "./assets/dev/javascript/meta-box/*.js", "./assets/dev/javascript/pages/*.js", "./assets/dev/javascript/run.js", "./assets/dev/javascript/image-upload.js"];

    return order.flatMap((pattern) => {
        return glob.sync(pattern).map((file) => {
            return file.startsWith("./") ? file : `./${file}`;
        });
    });
};

// Tracker file list
const trackerFiles = ["./assets/dev/javascript/user-tracker.js", "./assets/dev/javascript/event-tracker.js", "./assets/dev/javascript/tracker.js"];

module.exports = {
    mode: "production",
    entry: {
        "admin.min": getOrderedAdminFiles(),
        "tracker.min": trackerFiles,
        "app.min": "./assets/dev/sass/app.scss",
    },
    output: {
        filename: "[name].js",
        path: path.resolve(__dirname, "assets/js"),
    },
    module: {
        rules: [
            {
                test: require.resolve("./assets/dev/javascript/config.js"),
                loader: "expose-loader",
                options: {
                    exposes: ["wps_js"],
                },
            },
            {
                test: /\.js$/,
                exclude: /node_modules/,
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
            header: "jQuery(document).ready(function ($) {",
            footer: "});",
        }),
        new MiniCssExtractPlugin({
            filename: "[name].css", // ✅ Correct location
        }),
        new RemoveEmptyScriptsPlugin(),
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
    },
    externals: {
        jquery: "jQuery",
    },
};
