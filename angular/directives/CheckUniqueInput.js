(function(){
    "use strict";

    angular.module("app.directives").directive('uniqueCheck', ['PecooniaApiService', function (PecooniaApiService) {
        return {
            require: 'ngModel',
            link: function (scope, elem, attrs, ctrl) {
                elem.on('keyup', function () {
                    scope.$apply(function () {
                        var data = {};
                        data[attrs.name] = attrs.$$element[0].value;
                        PecooniaApiService.input(data, function(res){
                            var v = res.message !== 'error';
                            ctrl.$setValidity('uniqueMatch', v);
                        });
                    });
                });
            }
        };
    }]);
})();
