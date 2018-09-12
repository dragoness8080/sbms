require.config({
    urlArgs: 'v=20180824001',
    baseUrl: '../addons/sbms/static/js',
    paths: {
        'jquery': '../jquery-1.11.1.min',
        'jquery.ui': '../jquery-ui-1.10.3.min',
        'bootstrap': '../bootstrap.min',
        'tpl':'tmodjs'
    },
    shim: {
        'jquery.ui': {
            exports: "$",
            deps: ['jquery']
        },
        'bootstrap': {
            exports: "$",
            deps: ['jquery']
        }
    }
});