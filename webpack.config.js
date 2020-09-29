/**
 * WordPress dependencies
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );

/**
 * External dependencies
 */
const webpack = require( 'webpack' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const FixStyleOnlyEntriesPlugin = require( 'webpack-fix-style-only-entries' );
const OptimizeCssAssetsPlugin = require( 'optimize-css-assets-webpack-plugin' );

module.exports = {
	...defaultConfig,
	devtool: 'source-map',
	externals: {
		jquery: 'jQuery',
		$: 'jQuery',
	},
	resolve: {
		...defaultConfig.resolve,
		modules: [
			`${__dirname}/assets/js/src`,
			'node_modules',
		],
	},

	/**
	 * Add your entry points for CSS and JS here.
	 */
	entry: {
		batch: './assets/js/src/batch',
		batchStyle: './assets/css/src/batch.css',
		debug: './assets/js/src/debug',
		debugStyle: './assets/css/src/debug.css',
	},
	module: {
		rules: [
			...defaultConfig.module.rules,
			{
				test: /\.css$/,
				use: [
					MiniCssExtractPlugin.loader,
					{
						loader: 'css-loader',
						options: {
							importLoaders: 1,
						},
					},
					{
						loader: 'postcss-loader',
						options: {
							plugins: () => [
								require( 'autoprefixer' ),
							],
						},
					},
				],
			},
		],
	},
	output: {
		filename: 'assets/js/build/[name].min.js',
		path: __dirname,
	},
	plugins: [
		new FixStyleOnlyEntriesPlugin(),
		new OptimizeCssAssetsPlugin(),
		new webpack.ProvidePlugin( {
			Promise: 'es6-promise-promise',
			$: 'jquery',
		} ),
		new MiniCssExtractPlugin( {
			filename: 'assets/css/build/[name].min.css',
		} ),
	],
};