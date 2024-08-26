const defaultConfig = require('@wordpress/scripts/config/webpack.config.js');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const path = require('path');
const fs = require('fs');

class MoveRtlStylePlugin {
    apply(compiler) {
        compiler.hooks.emit.tapAsync('MoveRtlStylePlugin', (compilation, callback) => {
            const assets = Object.keys(compilation.assets);

            assets.forEach((asset) => {
                if (asset.includes('rtl.css')) {
                    const targetPath = path.join('assets/blocks/post-summary', path.basename(asset));
                    const content = compilation.assets[asset].source();

                    fs.mkdirSync(path.dirname(path.resolve(__dirname, targetPath)), { recursive: true });
                    fs.writeFileSync(path.resolve(__dirname, targetPath), content);

                    // Remove the old asset
                    delete compilation.assets[asset];
                }
            });

            callback();
        });
    }
}

module.exports = {
    ...defaultConfig,
    entry: {
        ...defaultConfig.entry(),
        'post-summary': './assets/dev/blocks/post-summary/index.js',
    },
    output: {
        ...defaultConfig.output,
        filename: 'post-summary/[name].js',
    },
    plugins: [
        ...defaultConfig.plugins.filter(
            (plugin) => !(plugin instanceof MiniCssExtractPlugin)
        ),
        new MiniCssExtractPlugin({
            filename: 'post-summary/[name].css',
        }),
        new MoveRtlStylePlugin(),
    ],
    module: {
        rules: [
            ...defaultConfig.module.rules,
            {
                test: /\.css$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    {
                        loader: 'postcss-loader',
                        options: {
                            postcssOptions: {
                                plugins: [
                                    require('autoprefixer'),
                                    require('rtlcss')(), // Generate RTL CSS
                                ],
                            },
                        },
                    },
                ],
            },
        ],
    },
};
