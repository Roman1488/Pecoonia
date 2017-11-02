(function() {
    "use strict";

    var app = angular.module('PecooniaApp',
        [
            'app.controllers',
            'app.filters',
            'app.services',
            'app.directives',
            'app.routes',
            'app.config',
            'ncy-angular-breadcrumb',
            'isteven-multi-select'
        ]);

    angular.module('app.routes', ['ui.router']);
    angular.module('app.controllers', ['ui.router', 'restangular', 'ngAnimate', 'ui.bootstrap', 'ui.select',
        'ngSanitize', 'ngIdle', 'ngCookies', 'daterangepicker', 'chart.js']);
    angular.module('app.filters', []);
    angular.module('app.services', ['toastr']);
    angular.module('app.directives', []);
    angular.module('app.config', []);

    app
        .config(function(IdleProvider, KeepaliveProvider, $breadcrumbProvider) {
            // configure Idle settings
            IdleProvider.idle(900); // in seconds
            IdleProvider.timeout(60); // in seconds
            IdleProvider.autoResume('notIdle');

            $breadcrumbProvider.setOptions({
                templateUrl: '/vendor/html/breadcrumb.html'
            });


        })
        .run(function ($state, $rootScope, PecooniaApiService) {

            $rootScope.$on('$viewContentLoaded',
                function(){
                    // SEMICOLON.widget.init();
            });

            $rootScope.home = function() {
                $rootScope.portfolio = null;
                PecooniaApiService.setCurrentPortfolio(false);

                $state.go('panel.create');
            };
    });

})();
