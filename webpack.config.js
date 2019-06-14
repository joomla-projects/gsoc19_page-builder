const path = require('path');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

// When in development mode, watch and lint files
module.exports = {
  mode: 'production',
  entry: [
    './build/media_source/com_templates/vue/pagebuilder.js'
  ],
  output: {
    path: path.resolve(__dirname, './media/com_templates/js'),
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
  }
};
