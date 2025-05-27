const path = require("path");

const commonConfig = {
    module: {
        rules: [{
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: "babel-loader",
                    options: {
                        presets: [
                            ["@babel/preset-env", {
                                targets: {
                                    node: "21"
                                }
                            }],
                            "@babel/preset-react"
                        ]
                    }
                }
            },
            {
                test: /\.scss$/,
                use: [
                    "style-loader",
                    "css-loader",
                    "sass-loader"
                ]
            },
            {
                test: /\.css$/,
                use: ["style-loader", "css-loader"]
            },
            {
                test: /\.(png|svg|jpg|jpeg|gif)$/i,
                type: "asset/resource"
            }
        ]
    },
    resolve: {
        extensions: [".js", ".jsx", ".json", ".scss"],
        fallback: {
            "path": false,
            "fs": false
        }
    }
};

const reactConfig = {
    ...commonConfig,
    name: 'react',
    entry: {
        "migration": "./assets/js/react/pages/DataMigration/index.jsx"
    },
    output: {
        path: path.resolve(__dirname, "assets/dist/react"),
        filename: "[name].js",
        clean: true
    }
};

const blocksConfig = {
    ...commonConfig,
    name: 'blocks',
    entry: {
        "blocks": "./assets/dev/blocks/index.js"
    },
    output: {
        path: path.resolve(__dirname, "assets/dist/blocks"),
        filename: "[name].js",
        clean: true
    },
    externals: {
        "@wordpress/blocks": "wp.blocks",
        "@wordpress/element": "wp.element",
        "@wordpress/components": "wp.components",
        "@wordpress/i18n": "wp.i18n"
    }
};

// Export configuration based on target
module.exports = (env = {}) => {
    if (env.target === 'react') {
        return reactConfig;
    }
    if (env.target === 'blocks') {
        return blocksConfig;
    }
    // Default: return both configurations
    return [reactConfig, blocksConfig];
};