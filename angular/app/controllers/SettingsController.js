(function(){
    "use strict";

    var app = angular.module('PecooniaApp');

    app.controller('SettingsController', ['$scope', '$rootScope', 'PecooniaApiService', 'toastr', '$uibModal', '$filter', function($scope, $rootScope, PecooniaApiService, toastr, $uibModal, $filter){

        $scope.timezones = timezones;

        $scope.animationsEnabled = true;
        $scope.check_passwd_err = false;

        // If timezone string is set for user, select that object in select box
        if ($scope.user.timezone && _.isString($scope.user.timezone))
        {
            $scope.user.timezone = _.find(timezones, function(item) {
                return (item.value == $scope.user.timezone);
            });
        }

        $scope.changeUserSettings = function(){

            if(!$scope.changeUser.$valid)
                return true;

            var changedUserData = angular.copy($rootScope.user);

            if (changedUserData.change_password)
            {
                changedUserData.password = changedUserData.password.new;
            }
            else
            {
                changedUserData = _.omit(changedUserData, 'password');
            }

            // Set timezone value from object
            if ($scope.user.timezone && _.isObject($scope.user.timezone))
            {
                changedUserData.timezone = $scope.user.timezone.value;
                changedUserData.timezone_code = $scope.user.timezone.utc[0];
            }

            PecooniaApiService.update(changedUserData, function(res){
                // If email address is changed, show message and log the user out.
                if (res.item.emailChanged) {
                    toastr.success('You must verify your updated email address. Please check your email.');
                    $rootScope.$emit('auth:logout');
                } else {
                    toastr.success(res.message);
                }

                if (changedUserData.change_password && changedUserData.password)
                {
                    $scope.user.password.new = "";
                    $scope.user.password.old = "";
                    $scope.user.password.confirm = "";
                    $scope.changeUser.password = "";
                    $scope.changeUser.new_password = "";
                    $scope.changeUser.confirm_password = "";
                    toastr.success("The password was successfully updated.");
                }

                // Check and open welcome message modal
                openWelcomeMsgModal();

            }, function(res){
                toastr.error(res.data.message);
            });
        };

        function openWelcomeMsgModal()
        {
            // For social signup user
            if ($rootScope.user && $rootScope.user.show_welcome_msg && $rootScope.user.signup_source)
            {
                var wmmodalInstance = $uibModal.open({
                    animation: $scope.animationsEnabled,
                    templateUrl: '/vendor/html/modal/WelcomeMessageModalTemplate.html',
                    controller: 'CommonModalController',
                    size: 'md',
                    backdrop  : 'static',
                    keyboard  : false,
                    resolve: {
                        data: function () {
                            return {};
                        }
                    }
                });
            }
        }

        $scope.changePassword = function(event){

            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            if(!$scope.changeUserPassword.$valid)
                return true;

            var data = {
                password: $scope.password.new
            };

            PecooniaApiService.update(data, function(res){
                $scope.password = {};
                $scope.changeUserPassword.$setPristine();
                toastr.success("The password was successfully updated.");
            }, function(res){
                toastr.error(res.data.message);
            });
        };

        // $scope.currentPagePortfolios = 1;
        // $scope.numPerPageForPortfolios = 5;
        // $scope.maxSizePortfolios = 5;
        //
        // $scope.currentPageBanks = 1;
        // $scope.numPerPageForBanks = 5;
        // $scope.maxSizeBanks = 5;

        $scope.filter = function(key){
            var begin, end;
            begin = (($scope['currentPage' + key] - 1) * $scope['numPerPageFor' + key]);
            end = begin + $scope['numPerPageFor' + key];
            $scope['filtered' + key] = $scope['user' + key].slice(begin, end);
        };

        $scope.getPortfolios = function(){
            PecooniaApiService.getAllPortfolios(function(res){
                $scope.userAllPortfolios = res.item;
                // $scope.filter('Portfolios');
            });
        };

        $scope.getBanks = function(refresh){
            PecooniaApiService.getBanks(function(res){
                $scope.userBanks = res.item;
                // $scope.filter('Banks');
            }, refresh);
        };

        $scope.getPortfolios();
        $scope.getBanks();

        // $scope.$watchGroup(['currentPagePortfolios', 'currentPageBanks'], function (newValues, oldValues, scope) {
        //
        //     if (newValues[0] != oldValues[0]) {
        //         $scope.filter('Portfolios');
        //     }
        //
        //     if (newValues[1] != oldValues[1]) {
        //         $scope.filter('Banks');
        //     }
        //
        // });

        $scope.editModalPortfolios = function(size, data){

            var modalInstance = $uibModal.open({
                animation: $scope.animationsEnabled,
                templateUrl: '/vendor/html/modal/EditPortfolioModalTemplate.html',
                controller: 'EditModalController',
                size: size,
                resolve: {
                    data: function () {
                        return {
                            name: data.name,
                            comma_separator: data.comma_separator,
                            date_format: data.date_format
                        };
                    }
                }
            });

            modalInstance.result.then(function(edited){
                PecooniaApiService.updatePortfolio(data.id, edited, function(res){
                    $rootScope.$emit('portfolio:refresh', {refresh : true});
                    toastr.success(res.message);
                })
            }, function(res){

            });

        };

        // Delete Account functions

        $scope.deleteModalUserAccount = function(size){

            $scope.deleteAccountModalInstance = $uibModal.open({
                animation: $scope.animationsEnabled,
                templateUrl: '/vendor/html/modal/DeleteUserAccountModalTemplate.html',
                scope: $scope,
                controller: 'SettingsController',
                size: size
            });

            $scope.deleteAccountModalInstance.result.then(function(){
                toastr.success('You have deleted your account ' + $rootScope.user.name + '.');
                // Remove user session data
                $rootScope.$emit('user:deleted');
            }, function(res){

            });
        };

        $scope.submitDelAccntPasswdForm = function () {
            var postData = {};

            if(!$rootScope.user.signup_source)
            {
                if (!$scope.passwdForm.$valid)
                    return;

                postData = {
                    'password' : $scope.checkPassword
                };
            }

            PecooniaApiService.deleteUserAccount(postData, function(res){
                $scope.deleteAccountModalInstance.close($scope.var);
            }, function(res){
                $scope.check_passwd_err = true;
            });
        };

        $scope.cancel = function () {
            $scope.deleteAccountModalInstance.dismiss('Cancel');
        };

        // end

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
                $rootScope.$emit('portfolio:refresh', {refresh : true});
                toastr.success('Portfolio deleted successfully.');
            }, function(res){

            });
        };

        $scope.deReActivateModalPortfolio = function(size, data) {

            var modalInstance = $uibModal.open({
                animation: $scope.animationsEnabled,
                templateUrl: '/vendor/html/modal/DeReActivatePortfolioModalTemplate.html',
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

            modalInstance.result.then(function() {
                var portfolioStatus = (data.status === 1) ? 0 : 1;

                PecooniaApiService.updatePortfolio(data.id, {status: portfolioStatus}, function(res){
                    var toastMsg = '';

                    unselectCurrentPortfolio(data.id);

                    $rootScope.$emit('portfolio:refresh', {refresh : true});

                    data.status = portfolioStatus;

                    if (data.status) {
                        toastMsg = 'You have reactivated Portfolio ' + data.name;
                    } else {
                        toastMsg = 'You have deactivated Portfolio ' + data.name;
                    }

                    toastr.success(toastMsg);
                });
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

        $scope.deleteBank = function(size, data, index) {

            var modalInstance = $uibModal.open({
                animation: $scope.animationsEnabled,
                templateUrl: '/vendor/html/modal/DeleteBankModalTemplate.html',
                controller: 'EditModalController',
                size: size,
                resolve: {
                    data: function () {
                        return {
                            bank: data,
                        };
                    }
                }
            });

            modalInstance.result.then(function(){
                PecooniaApiService.deleteBank(data.id, function(res){

                    $scope.userBanks.splice(index,1);
                    toastr.success(res.message);
                });
            }, function(res){

            });
        };

        $scope.deReActivateBank = function(size, data) {

            var modalInstance = $uibModal.open({
                animation: $scope.animationsEnabled,
                templateUrl: '/vendor/html/modal/DeReActivateBankModalTemplate.html',
                controller: 'EditModalController',
                size: size,
                resolve: {
                    data: function () {
                        return {
                            bank: data,
                        };
                    }
                }
            });

            modalInstance.result.then(function() {
                var bankStatus = (data.status === 1) ? 0 : 1;

                PecooniaApiService.updateBank(data.id, {status: bankStatus, name: data.name}, function(res){
                    var toastMsg = '';

                    data.status = bankStatus;

                    if (data.status) {
                        toastMsg = 'You have reactivated Bank Account ' + data.name;
                    } else {
                        toastMsg = 'You have deactivated Bank Account ' + data.name;
                    }

                    toastr.success(toastMsg);
                });
            }, function(res){

            });
        };

        $scope.editModalBanks = function(size, data){

            var modalInstance = $uibModal.open({
                animation: $scope.animationsEnabled,
                templateUrl: '/vendor/html/modal/EditBankModalTemplate.html',
                controller: 'EditModalController',
                size: size,
                resolve: {
                    data: function () {
                        return {
                            name: data.name,
                            enable_overdraft: data.enable_overdraft
                        };
                    }
                }
            });

            modalInstance.result.then(function(edited){
                PecooniaApiService.updateBank(data.id, edited, function(res){
                    $scope.getBanks(true);
                    toastr.success(res.message);
                })
            }, function(res){
                console.log(res);
            });

        };

        // Show and delete tags

        PecooniaApiService.getAllTags(function(res){
            $scope.allTags = res.item;
        });

        // Confirm tag delete popup

        $scope.confirmTagDelete = function(tag, t_index, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            var modalInstance = $uibModal.open({
                animation: $scope.animationsEnabled,
                templateUrl: '/vendor/html/modal/ConfirmTagDelete.html',
                controller: 'CommonModalController',
                size: 'md',
                resolve: {
                    data: function () {
                        return tag;
                    }
                }
            });

            modalInstance.result.then(function(){
                PecooniaApiService.deleteTag(tag, function(res){
                    // Remove the tag from model
                    $scope.allTags.splice(t_index, 1);
                }, function(res){
                    toastr.error(res.data.message);
                });
            }, function(dismissed){});
        };

        // end

        $rootScope.$on('portfolio:refresh', function(){
            $scope.getPortfolios();
        });

        $rootScope.$on('bank:refresh', function(){
            $scope.getBanks(true);
        });

    }]);

})();

