const path = require('path')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')

module.exports = {
  entry: {
    index: path.resolve(__dirname, 'src/assets/index/src/scripts/main.ts'),
    modal: path.resolve(__dirname, 'src/assets/modal/src/scripts/main.ts')
  },
  output: {
    path: path.resolve(__dirname, 'src/assets'),
    filename: '[name]/dist/scripts/[name].js'
  },
  externals: {
    jquery: 'jQuery',
    craft: 'Craft',
    garnish: 'Garnish'
  },
  resolve: {
    extensions: ['.ts', '.tsx']
  },
  module: {
    rules: [
      {
        use: ['ts-loader'],
        include: [path.resolve(__dirname, 'src')],
        test: /\.tsx?$/
      },
      {
        use: ['source-map-loader'],
        enforce: 'pre',
        test: /\.js$/
      },
      {
        use: [MiniCssExtractPlugin.loader, 'css-loader'],
        test: /\.css$/
      },
      {
        use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
        test: /\.scss$/
      }
    ]
  },
  devtool: 'source-map',
  plugins: [new MiniCssExtractPlugin({
    filename: '[name]/dist/styles/[name].css'
  })]
}
