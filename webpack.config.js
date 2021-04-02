const path = require("path");
const webpack = require("webpack");

// plugins
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");

module.exports = {
  watch: true,
  devtool : 'inline-source-map',
  entry: {
    js: "./web/frontend/js/react/jsx/TRD.jsx",
    sass: './web/frontend/sass/trd.scss'
  },
  mode: "development",
  module: {
    rules: [
      {
        test: /\.(js|jsx)$/,
        exclude: /(node_modules|bower_components)/,
        loader: "babel-loader",
        options: { presets: ["@babel/react"] }
      },
      {
        test: /\.css$/,
        use: ["style-loader", "css-loader"]
      },
      {
        test: /\.scss$/,
        use: [
          {
            loader: MiniCssExtractPlugin.loader
          },
          {
            // Interprets CSS
            loader: "css-loader",
            options: {
              importLoaders: 2
            }
          },
          {
            loader: 'sass-loader' // 将 Sass 编译成 CSS
          },
          {
            loader: 'postcss-loader', // Run post css actions
            options: {
              plugins: function () { // post css plugins, can be exported to postcss.config.js
                return [
                  //require('precss'),
                  require('autoprefixer')
                ];
              }
            }
          }
        ]
      },
      {
        test: /\.woff(2)?$/,
          use: [
            {
              loader: 'url-loader',
              options: {
                limit: 10000,
                name: './font/[hash].[ext]',
                mimetype: 'application/font-woff',
                esModule: false
              }
            }
          ]
      }
    ]
  },
  resolve: { extensions: ["*", ".js", ".jsx"] },
  output: {
    path: path.resolve(__dirname, "web/frontend/js/dist/"),
    publicPath: "/web/frontend/js/dist/",
    filename: "bundle.js",
    hotUpdateChunkFilename: "web/frontend/js/dev/hot-update.js",
    hotUpdateMainFilename: "web/frontend/js/dev/hot-update.json"
  },
  devServer: {
    contentBase: path.join(__dirname, "web/frontend/js/dist/"),
    port: 3000,
    publicPath: "http://localhost:5000/web/frontend/js/dist/",
    hotOnly: true
  },
  plugins: [
    new webpack.HotModuleReplacementPlugin(),
    new MiniCssExtractPlugin({
      filename: 'index.css',
      allChunks: true,
    })
  ],
  optimization: {
    minimizer: [
      new OptimizeCSSAssetsPlugin({
        cssProcessorOptions: {
          safe: true
        }
      })
    ]
  },
};
