/**
 * @description Shared config values for dist and dev builds
 */
const config = {
  // entry points
  entry: './js/index.js',
  scssEntry: './scss/style.scss',
  clientModules: [
    './node_modules',
    '../../../../../../../ucdlib-theme-wp/src/public/node_modules',
    '../../../../../themes/ucdlib-theme-wp/src/public/node_modules'
  ],

  // output
  fileName: 'ucdlib-datalab',
  cssFileName: 'ucdlib-datalab',
  publicDir: '../../assets',
  jsDevDir: 'js/public-dev',
  jsDistDir: 'js/public-dist',


  loaderOptions: {
    css: {
      loader: 'css-loader',
      options : {
        url: false
      }
    },
    scss: {
      loader: 'sass-loader',
      options: {
        sassOptions: {
          includePaths: [
            "node_modules/@ucd-lib/theme-sass",
            "node_modules/breakpoint-sass/stylesheets",
            "node_modules/sass-toolkit/stylesheets"]
        }
      }
    }
  },
};

export default config;
