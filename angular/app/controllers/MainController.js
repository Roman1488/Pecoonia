(function(){
    "use strict";

    var app = angular.module('app.controllers');

    app.controller('MainController', ['$scope', '$rootScope', '$state', 'toastr', function($scope, $rootScope, $state, toastr){
        var self = this;

        switch($rootScope.currentState.name)
        {
            case "user-activated":
                toastr.success('Activation successful. Please log in.');
                $state.go('landing');
                break;
            case "404":
                toastr.error('Invalid page request');
                $state.go('landing');
                break;
            default:
                $state.go('landing');
                break;
        }
    }]);

})();
