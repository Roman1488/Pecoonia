(function(){
    "use strict";

    var app = angular.module('app.controllers');

    app.controller('EditModalController', ['$rootScope', '$scope', 'PecooniaApiService', '$uibModalInstance', 'data', '$state', function($rootScope, $scope, PecooniaApiService, $uibModalInstance, data, $state){

        $scope.var              = data;
        $scope.check_passwd_err = false;

        $scope.ok = function () {
            if (!$scope.varForm.$valid)
                return;
            $uibModalInstance.close($scope.var);
        };

        $scope.submitPasswdForm = function () {
            var postData = {};
            if(!$rootScope.user.signup_source)
            {
                if (!$scope.passwdForm.$valid)
                return;

                postData = {
                    'password' : $scope.var.checkPassword
                };
            }

            PecooniaApiService.deletePortfolio($scope.var.portfolio.id, postData, function(res){
                $uibModalInstance.close($scope.var);
            }, function(res){
                $scope.check_passwd_err = true;
            });
        };

        $scope.cancel = function () {
            $uibModalInstance.dismiss('Cancel');
        };

        $scope.proceedDeleteBank = function () {
            $uibModalInstance.close($scope.var);
        };

        $scope.proceedDeReactivateBank = function () {
            $uibModalInstance.close($scope.var);
        };

        $scope.proceedDeReactivatePortfolio = function () {
            $uibModalInstance.close($scope.var);
        };

    }]);

})();

