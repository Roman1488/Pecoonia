(function(){
    "use strict";

    var app = angular.module('app.controllers');

    app.controller('CommonModalController', ['$scope', '$rootScope', 'toastr', 'PecooniaApiService', 'CommonFunctionsService', '$uibModalInstance', 'data', function($scope, $rootScope, toastr, PecooniaApiService, CommonFunctionsService, $uibModalInstance, data){

        $scope.modalData = data;
        $scope.currentPortfolio = PecooniaApiService.getCurrentPortfolio();

        $scope.showCurrencyDistribution = [];

        $scope.submitForceChangePasswdForm = function (event) {
            event.preventDefault();
            if (!$scope.forceChangePasswordForm.$valid)
                return;

            var postData = {
                'currpwd' : $scope.currPassword,
                'newpwd'  : $scope.newPassword
            };

            PecooniaApiService.forceChangePassword(postData, function(res){
                $uibModalInstance.close({});
            }, function(res){
                toastr.error(res.data.message);
            });
        };

        $scope.proceedDeleteTrans = function () {
            $uibModalInstance.close({});
        };

        $scope.proceedDeleteTag = function () {
            $uibModalInstance.close({});
        };

        $scope.cancel = function () {
            $uibModalInstance.dismiss({});
        };

        $scope.closeWelcomeMsg = function() {
            PecooniaApiService.closeWelcomeMsg(function(res){
                $rootScope.user.show_welcome_msg = 0;
                $uibModalInstance.close({});
            }, function(res){
                toastr.error(res.data.message);
            });
        }

        $scope.formatTrDate = function(date) {
            return CommonFunctionsService.formatTrDate(date);
        };

        $scope.formatValue = function(v, fraction_size) {
            return CommonFunctionsService.formatValue(v, fraction_size);
        };

        $scope.displayCurrencyDistribution = function(index)
        {
            $scope.showCurrencyDistribution[index] =! $scope.showCurrencyDistribution[index];
        };


    }]);

})();