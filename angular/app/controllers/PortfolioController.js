(function(){
    "use strict";

    var app = angular.module('PecooniaApp');


    app.controller('PortfolioController', ['$scope', '$rootScope', 'PecooniaApiService', '$stateParams', '$state', '$filter', '$uibModal', function($scope, $rootScope, PecooniaApiService, $stateParams, $state, $filter, $uibModal) {
        $scope.portfolioStatistics = [];
        // check if need to open force password change pop-up
        if ($rootScope.user && $rootScope.user.force_change_password)
        {
            $scope.openForcePwdChangeModal = function(size){

                var modalInstance = $uibModal.open({
                    animation: $scope.animationsEnabled,
                    templateUrl: '/vendor/html/modal/ForcePasswordChangeModalTemplate.html',
                    controller: 'CommonModalController',
                    size: size,
                    resolve: {
                        data: function () {
                            return {};
                        }
                    }
                });

                modalInstance.result.then(function(){
                    toastr.success('Password changed successfully.');
                    $rootScope.user.force_change_password = false;
                }, function(dismissed){});
            };

            $scope.openForcePwdChangeModal('md');
        }

        // check if need to show welcome message modal, for non-social signup user
        if ($rootScope.user && $rootScope.user.show_welcome_msg && !$rootScope.user.signup_source)
        {
            $scope.openWelcomeMsgModal = function(size){

                var wmmodalInstance = $uibModal.open({
                    animation: $scope.animationsEnabled,
                    templateUrl: '/vendor/html/modal/WelcomeMessageModalTemplate.html',
                    controller: 'CommonModalController',
                    size: size,
                    backdrop  : 'static',
                    keyboard  : false,
                    resolve: {
                        data: function () {
                            return {};
                        }
                    }
                });
            };

            $scope.openWelcomeMsgModal('md');
        }

        // Unset Portfolio selection
        $scope.currentPortfolio = PecooniaApiService.getCurrentPortfolio();
        if ($scope.currentPortfolio)
        {
            unselectCurrentPortfolio($scope.currentPortfolio.id);
        }

        $scope.getPortfolios = function(){
            PecooniaApiService.getPortfolios(function(res){
                $rootScope.userPortfolios = res.item;
                res.item.forEach(function(k,i){
                    PecooniaApiService.getPortfolioStatistics(k.id, function(responce){
                        (responce.item && responce.item.portfolio_value) ?
                            $rootScope.userPortfolios[i]['currency']['portfolio_value'] = responce.item.portfolio_value :
                            $rootScope.userPortfolios[i]['currency']['portfolio_value'] = null;
                    });
                });
            });
        };

        $scope.openCreatePortfolioModal = function(){
            $('.modal').modal('hide');
            $('.newPortfolio-modal-md').modal('show');
            $('body').css('overflow', 'hidden');
            $('body').css('position', 'fixed');
            $('body').css('width', '100%');
        };

        $(".newPortfolio-modal-md").on("hidden.bs.modal", function () {
            $('body').css('overflow', '');
            $('body').css('position', '');
            $('body').css('width', '100%');
        });

        $scope.deleteModalPortfolio = function(size, data){

            var modalInstance = $uibModal.open({
                animation: $scope.animationsEnabled,
                templateUrl: '/vendor/html/modal/DeletePortfolioModalTemplate.html',
                controller: 'EditModalController',
                size: size,
                resolve: {
                    data: function () {
                        return {
                            portfolio: data,
                        };
                    }
                }
            });

            modalInstance.result.then(function(){
                unselectCurrentPortfolio(data.id);

                console.log("portcontroller - pre", $rootScope.userPortfolios);

                $rootScope.userPortfolios.forEach(function(k,i){
                    if (k.id === data.id)
                    {
                        $rootScope.userPortfolios.splice(i, 1);
                    }
                });

                console.log("portcontroller - post", $rootScope.userPortfolios);

                $rootScope.$emit('portfolio:refresh');
                toastr.success('Portfolio deleted successfully.');
            }, function(res){

            });
        };

        function unselectCurrentPortfolio(portfolio_id)
        {
            if ($rootScope.portfolio == portfolio_id)
            {
                $rootScope.portfolio = null;
                PecooniaApiService.setCurrentPortfolio(false);
            }
        }

        $scope.getPortfolios();
    }]);
})();