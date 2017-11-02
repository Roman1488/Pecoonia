(function(){
    "use strict";

    var app = angular.module('PecooniaApp');

    app.controller('HeaderController', ['$scope', '$rootScope', 'PecooniaApiService', 'Idle', '$cookies', '$interval', function($scope, $rootScope, PecooniaApiService, Idle, $cookies, $interval){

        $rootScope.loading = false;
        $scope.sessionExpireIn = 30;

        $rootScope.alerts = [];

        $rootScope.user = null;

        $rootScope.isCompany = ['Private', 'Company'];
        $rootScope.decimalMark = [',', '.'];
        $rootScope.dateFormat = ['mm-dd-yyyy', 'dd-mm-yyyy'];

        $rootScope.enableOverdraft = ['No', 'Yes'];

        PecooniaApiService.check(function(res){
            $rootScope.user = res.item;
            if(!$rootScope.remember)
            {
                Idle.watch();
            }
            $scope.updatePortfolios();
        });

        $scope.updatePortfolios = function(refresh){
            var r = refresh === undefined ? false : refresh;
            PecooniaApiService.getPortfolios(function(res){
                $rootScope.userPortfolios = res.item;
            }, r);
        };

        $scope.preventEvent = function(event){
            event.preventDefault();
        };

        $scope.login = function(event){
            if (event) { event.preventDefault(); event.stopPropagation(); }
            $('.login-modal-md').modal('show');
            $('.modal-backdrop').addClass('in').removeClass('out');
            $('body').addClass('modal-open');
        };

        $scope.logout = function(){
            //if (event) event.preventDefault();
            PecooniaApiService.logout();
            // Empty the user portfolios list
            $rootScope.userPortfolios = null;
            Idle.unwatch();
            $cookies.remove('sessionStart');
            if (!$rootScope.isInactivityLogout)
            {
                window.location.href = global_site_url;
            }
        };

        $scope.userAccountDeleted = function(){
            PecooniaApiService.removeUserSession();
            Idle.unwatch();
            $cookies.remove('sessionStart');
        }

        $rootScope.$on('auth:logout', function(){
            $scope.logout();
        });

        $rootScope.$on('user:deleted', function(){
            $scope.userAccountDeleted();
        });

        $rootScope.$on('auth:refresh', function(){
            PecooniaApiService.refresh(function(res){
                $rootScope.alerts = [];
                toastr.success(res.message);
                $cookies.putObject('sessionStart', moment().valueOf());
            });
        });

        $rootScope.$on('portfolios:update', function(){
            $scope.updatePortfolios();
        });

        $rootScope.$on('portfolio:refresh', function(e, data){
            console.log("REFRESHED");
            var r = data === undefined ? false : data.refresh;
            $scope.updatePortfolios(r);
        });

        /*$interval(function() {
            if (!$cookies.getObject('sessionStart')) {
                return;
            }
            var sessionTime = Math.round((moment().valueOf() - $cookies.getObject('sessionStart')) / 1000);
            var exp = $scope.sessionExpireIn * 60;
            if (sessionTime == exp) {
                $rootScope.alerts.push({
                    title: 'Your session is expiring.',
                    msg: 'Your session will be expired in 60 seconds. Please <a href="javascript:;" ng-click="$root.$emit(\'auth:refresh\')">click</a> here to refresh the token.',
                    type: 'successmsg'
                });
            } else if (sessionTime == exp + 60) {
                $scope.logout();
            }
        }, 1000);*/

    }]);

})();

