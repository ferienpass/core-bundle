const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('../public/theme')
    .setPublicPath('/bundles/ferienpasscore/theme')
    .setManifestKeyPrefix('')
    .addEntry('js/main', './files/js/main.js')
    .disableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSassLoader()
    .addStyleEntry('css/main', `./files/styles/main.scss`)
    .enablePostCssLoader()
;

module.exports = Encore.getWebpackConfig();
