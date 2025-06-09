const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
    entry: {
        main: './src/js/main.js',
        style: './src/scss/style.scss'
    },
    output: {
        filename: 'js/[name].js',
        path: path.resolve(__dirname, 'assets'),
        assetModuleFilename: 'icons/[name][ext]'
    },
    devtool: process.env.NODE_ENV === 'development' ? 'source-map' : false,
    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            ['@babel/preset-env', {
                                targets: {
                                    browsers: [
                                        'last 2 versions',
                                        'not dead',
                                        'ie >= 11',
                                        'safari >= 10',
                                        'ios >= 10'
                                    ]
                                }
                            }]
                        ]
                    }
                }
            },
            {
                test: /\.scss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            sourceMap: process.env.NODE_ENV === 'development',
                            url: true
                        }
                    },
                    {
                        loader: 'postcss-loader',
                        options: {
                            sourceMap: process.env.NODE_ENV === 'development',
                            postcssOptions: {
                                plugins: [
                                    ['autoprefixer', {
                                        grid: true,
                                        browsers: [
                                            'last 2 versions',
                                            'not dead',
                                            'ie >= 11',
                                            'safari >= 10',
                                            'ios >= 10'
                                        ]
                                    }],
                                    'cssnano'
                                ]
                            }
                        }
                    },
                    {
                        loader: 'sass-loader',
                        options: {
                            sourceMap: process.env.NODE_ENV === 'development',
                            sassOptions: {
                                outputStyle: 'compressed'
                            }
                        }
                    }
                ]
            },
            {
                test: /\.svg$/,
                type: 'asset/resource',
                generator: {
                    filename: 'icons/[name][ext]'
                }
            }
        ]
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: 'css/[name].css'
        })
    ]
}; 