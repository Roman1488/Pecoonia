(function(){
    "use strict";

    var app = angular.module('app.controllers');

    app.controller('AlertController', ['$scope', '$rootScope', 'Idle', 'toastr', '$uibModal', 'Title', function($scope, $rootScope, Idle, toastr, $uibModal, Title){

        $rootScope.isInactivityLogout = false;

        $scope.proceedStayLoggedIn = function() {
            Idle.watch();
            Title.restore();
            toastr.success('Session refreshed.');
            $rootScope.inactivityModal.dismiss({});
        };

        $scope.cancel = function () {
            Idle.unwatch();
            $rootScope.isInactivityLogout = true;
            $rootScope.inactivityModal.dismiss({});
            $rootScope.$emit('auth:logout');
        };

        $scope.openInactivityWarning = function(){
            $rootScope.inactivityModal = $uibModal.open({
                animation: $scope.animationsEnabled,
                templateUrl: '/vendor/html/modal/SessionInactivityModalTemplate.html',
                controller: 'AlertController',
                size: 'lg',
            });
        };

        $scope.$on('IdleStart', function() {
            $scope.openInactivityWarning();
        });

        $scope.$on('IdleTimeout', function() {
            $rootScope.isInactivityLogout = true;
            if ($rootScope.inactivityModal) {$rootScope.inactivityModal.dismiss({});}
            $rootScope.$emit('auth:logout');
        });

    }]);

})();
