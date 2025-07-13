const defaultConfig = require('@wordpress/scripts/config/webpack.config.js');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const path = require('path');
const fs = require('fs');

class MoveRtlStylePlugin {
    apply(compiler) {
        compiler.hooks.emit.tapAsync('MoveRtlStylePlugin', (compilation, callback) => {
            const assets = Object.keys(compilation.assets);

            // Change output folder only for `post-summary` block CSS files
            assets.forEach((asset) => {
                if (asset.includes('post-summary')) {
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
        filename: (pathData) => {
            // Apply custom output folder only to `post-summary`
            if (pathData.chunk.name === 'post-summary') {
                return 'post-summary/[name].js';
            }
            return '[name].js'; // Default output for all other blocks
        },
    },
    plugins: [
        ...defaultConfig.plugins.filter(
            (plugin) => !(plugin instanceof MiniCssExtractPlugin)
        ),
        new MiniCssExtractPlugin({
            filename: ({ chunk }) => {
                // Only apply custom folder for `post-summary` CSS files
                if (chunk.name === 'post-summary') {
                    return 'post-summary/[name].css';
                }
                return '[name].css'; // Default for other blocks
            },
        }),
        new MoveRtlStylePlugin(), // Handles moving 'post-summary' RTL files
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
