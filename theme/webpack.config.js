const path = require('path');
const TerserPlugin = require('terser-webpack-plugin');

const theme = process.env.EB_THEME || 'openconext';

module.exports = (env, argv) => {
  const isProd = argv.mode === 'production';

  return {
    entry: path.resolve(__dirname, `${theme}/javascripts/application.js`),
    output: {
      path: path.resolve(__dirname, '../public/javascripts'),
      filename: 'application.min.js',
    },
    module: {
      rules: [
        {
          test: /\.js$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader',
          },
        },
      ],
    },
    optimization: isProd ? {
      minimizer: [
        new TerserPlugin({
          terserOptions: {
            compress: {
              passes: 2,
              inline: 1,
              dead_code: true,
              unused: false,
              reduce_funcs: false,
              reduce_vars: false,
            },
            mangle: true,
          },
        }),
      ],
    } : {},
    devtool: isProd ? false : 'source-map',
  };
};
