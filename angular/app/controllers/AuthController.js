(function(){
    "use strict";

    var app = angular.module('app.controllers');

    app.controller('AuthController', ['$scope', '$rootScope', 'toastr', 'PecooniaApiService', 'Idle', 'Title', '$cookies', '$state', '$stateParams', '$location', 'windowPopupService', '$window', function($scope, $rootScope, toastr, PecooniaApiService, Idle, Title, $cookies, $state, $stateParams, $location, windowPopupService, $window){

        $scope.timezones = timezones;
        $scope.data = {login: {}, register: {}, resetPwd: {}};

        /* Reset password variables */
        $scope.data.resetPwd.token = $stateParams.token;
        $scope.data.resetPwd.email = $location.search().email;
        /* end */

        $scope.login = function(callback){
            //event.preventDefault();
            if(!$scope.loginForm.$valid)
                return true;

            $rootScope.remember = ($scope.data.login.remember) ? $scope.data.login.remember : false;
            PecooniaApiService.auth($scope.data.login, function(res){
                if(!$rootScope.remember)
                {
                    Idle.watch();
                }

                Title.restore();

                $cookies.putObject('sessionStart', moment().valueOf());
                $rootScope.alerts = [];
                $rootScope.$emit('portfolios:update');
                var lastLogin = moment(res.item.user.last_login + ' +0000', 'YYYY-MM-DD HH:mm:ss Z').format('DD-MM-YYYY HH:mm:ss');
                toastr.success("Welcome back " + res.item.user.name + " you were last logged in at " + lastLogin);
                if (callback !== undefined)
                    callback();
            }, function(res){
                toastr.error(res.data.message);
                // toastr.error('User name and/or pass word is not correct. Please try again.');
            });
        };

        $scope.loginModal = function(){
            $scope.login(function(){
                $('.login-modal-md').modal('hide');
                $('.modal-backdrop').removeClass('in').addClass('out');
                // $('.modal-backdrop').removeClass('modal-backdrop');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            });
        };

        $scope.register = function(){
            //event.preventDefault();

            if (!$scope.registerForm.$valid)
            {
                $scope.registerForm.timezone.$dirty = true;
                return true;
            }

            // Set timezone value from object
            if ($scope.data.register.timezone)
            {
                $scope.data.register.timezone_code = $scope.data.register.timezone.utc[0];
                $scope.data.register.timezone = $scope.data.register.timezone.value;
            }

            PecooniaApiService.create($scope.data.register, function(res){
                toastr.success(res.message);
                $scope.reset();
                /*
                PecooniaApiService.auth($scope.data.register, function(res){
                    toastr.success('Your request has been sent successfully, an administrator will review your request.');
                    toastr.success('Welcome ' + $scope.data.register.name + ', thank you for signing up with Pecoonia.com. Please start out by creating a Portfolio by clicking on the tile below: CREATE NEW PORTFOLIO');
                });
                */
            }, function(res){
                toastr.error(res.data.message);
            });

        };

        $scope.forgotModal = function(){
            //event.preventDefault();

            if (!$scope.forgotForm.$valid)
                return;

            $scope.forgot_form_submit_clicked = true;

            var postData = {
                'email' : $scope.forgot_form_email,
            };

            PecooniaApiService.forgotUsernamePassword(postData, function(res){
                toastr.success(res.message);
                $('.modal').modal('hide');
                $scope.forgot_form_submit_clicked = false;
            }, function(res){
                toastr.error(res.data.message);
                $('.modal').modal('hide');
                $scope.forgot_form_submit_clicked = false;
            });
        };

        $scope.resetPassword = function(){
            //event.preventDefault();

            if (!$scope.resetPwdForm.$valid)
                return;

            PecooniaApiService.resetPassword($scope.data.resetPwd, function(res){
                toastr.success(res.message);
                $state.go('landing');
            }, function(res){
                toastr.error(res.data.message);
            });
        };

        $scope.openForgotModal = function(){
            //event.preventDefault();

            $('.modal').modal('hide');
            $('.forgot-modal-md').modal('show');
        };

        $scope.reset = function(){
            //event.preventDefault();
            $scope.data.register.name = '';
            $scope.data.register.user_name = '';
            $scope.data.register.password = '';
            $scope.data.register.repassword = '';
            $scope.data.register.email = '';
            $scope.data.register.reemail = '';
            $scope.data.register.timezone = [];
            $scope.registerForm.$setPristine();
        };

        $scope.openFacebookPopUp = function(from){
            $rootScope.remember = ($scope.data.login.remember) ? $scope.data.login.remember : false;
            windowPopupService.facebookOpen('/facebook/redirect/' + from + '/' + $rootScope.remember, 'Facebook Login', 900, 500);
        };

        $scope.openGooglePopUp = function(from){
            $rootScope.remember = ($scope.data.login.remember) ? $scope.data.login.remember : false;
            windowPopupService.googleOpen('/google/redirect/' + from + '/' + $rootScope.remember, 'Google Login', 900, 500);
        };

        $window.socialLoginCallback = function(res) {
            if (!res.error)
            {
                PecooniaApiService.setUser(res);
                if(!$rootScope.remember)
                {
                    Idle.watch();
                }

                Title.restore();

                $cookies.putObject('sessionStart', moment().valueOf());
                $rootScope.alerts = [];
                (res.from == 'signUp') ? $state.go('panel.profile_edit') : $state.go('panel.create');
            }
            else
            {
                toastr.error(res.message);
            }
        };
    }]);

})();

