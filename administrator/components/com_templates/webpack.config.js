const path = require('path');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

const config = {
  entry: [
    './resources/scripts/pagebuilder.js',
    './resources/styles/pagebuilder.scss',
  ],
  output: {
    path: path.resolve(__dirname, './../../../media/com_templates/js'),
    filename: 'pagebuilder.min.js',
  },
  module: {
    rules: [
      {
        test: /\.vue$/,
        loader: 'vue-loader',
      },
      {
        test: /\.js$/,
        loader: 'babel-loader',
      },
      {
        test: /\.scss$/,
        use: [{ loader: 'css-loader' }, { loader: 'sass-loader' }],
      },
    ],
  },
  plugins: [
    new VueLoaderPlugin(),
  ],
};

// When in development mode, watch and lint files
module.exports = (env, argv) => {

  if (argv.mode === 'development') {
    config.watch = true;
    config.watchOptions = {ignored: /node_modules/};
  }

  return config;
};
