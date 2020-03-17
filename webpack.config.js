const path = require('path');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

module.exports = {
  mode: 'production',
  entry: [
    './build/media_source/plg_editors_pagebuilder/js/pagebuilder.js'
  ],
  output: {
    path: path.resolve(__dirname, './media/plg_editors_pagebuilder/js'),
    filename: 'pagebuilder.min.js',
  },
  module: {
    rules: [
      {
        test: /\.vue$/,
        loader: 'vue-loader',
      },
    ],
  },
  plugins: [
    new VueLoaderPlugin(),
  ],
  performance: {
    hints: false
  },
  devtool: 'eval-source-map',
};
