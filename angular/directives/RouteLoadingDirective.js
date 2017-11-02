(function(){
    "use strict";

    angular.module("app.directives").directive('routeLoadingIndicator', function($rootScope, $timeout) {
        return {
            restrict: 'E',
            template: "<div ng-show='isRouteLoading' class='pecoonia-spinner' min-loader-display='300'>" +
            "<div class='pecoonia-spinner-bounce1'></div>" +
            "<div class='pecoonia-spinner-bounce2'></div>" +
            "<div class='pecoonia-spinner-bounce3'></div>" +
            "</div>",
            replace: true,
            link: function(scope, elem, attrs) {
                scope.data = {
                    startTime: undefined
                };
                var minLoaderDisplayTime = attrs.minLoaderDisplay || 300;
                scope.isRouteLoading = false;

                $rootScope.$on('$routeChangeStart', function() {
                    scope.data.startTime = new Date();
                    scope.isRouteLoading = true;
                });
                $rootScope.$on('$routeChangeSuccess', function() {
                    var transitionTime = new Date() - scope.data.startTime;
                    var loaderTimeout = minLoaderDisplayTime - transitionTime;
                    loaderTimeout = loaderTimeout > 0 ? loaderTimeout : 100;
                    var hideLoaderTimeout = $timeout(function () {
                        scope.isRouteLoading = false;
                    }, loaderTimeout);
                });
            }
        };
    });
})();