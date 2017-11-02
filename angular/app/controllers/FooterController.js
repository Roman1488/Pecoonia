(function(){
    "use strict";

    var app = angular.module('PecooniaApp');

    app.controller('FooterController', ['Restangular', '$scope', '$rootScope', 'PecooniaApiService', 'Idle', '$cookies', '$interval', 'toastr', function(Restangular, $scope, $rootScope, PecooniaApiService, Idle, $cookies, $interval, toastr){

        //Load Footer Content
        $rootScope.cmsContent = {};
        Restangular.one('api/cms/content')
            .get().then(function(res){
                if (res.status == 'ok'){
                    $rootScope.cmsContent = res.response;
                }
            },
            function(res) {
                $rootScope.cmsContent.about_us = {};
                $rootScope.cmsContent.about_us.content = "About Us";
                $rootScope.cmsContent.terms_of_use = {};
                $rootScope.cmsContent.terms_of_use.content = "Terms Of Use";
                $rootScope.cmsContent.privacy = {};
                $rootScope.cmsContent.privacy.content = "Privacy";
            }
        );

    	// Feedback modal window

        $scope.openFeedbackModal = function () {
            $('body').addClass('contact-form-active')
        };

        $scope.closeFeedbackModal = function () {
            $('body').removeClass('contact-form-active');
            jQuery('.form-success-send').hide(100);
        };

        // footer links modal content

        $rootScope.closeGenModal = function() {
            $('.gen-modal-wrap').removeClass('gen-modal-active');
        };

        $rootScope.openAboutUsModal = function () {
            $('#aboutus_modal').find(".contact-form-inner").find("#about_us").html($rootScope.cmsContent.about_us.content);
            $('#aboutus_modal').addClass('gen-modal-active');
        };

        $rootScope.openTermsOfUseModal = function () {
            $('#termsofuse_modal').find(".contact-form-inner").find("#terms_of_use").html($rootScope.cmsContent.terms_of_use.content);
            $('#termsofuse_modal').addClass('gen-modal-active');
        };

        $rootScope.openPrivacyModal = function () {
            $('#privacy_modal').find(".contact-form-inner").find("#privacy").html($rootScope.cmsContent.privacy.content);
            $('#privacy_modal').addClass('gen-modal-active');
        };

        // end


        $scope.sendFeedback = function(callback){
            //event.preventDefault();
            if(!$scope.feedbackForm.$valid)
                return true;

            console.log($scope.feedbackForm);

            var data = jQuery('form[name="feedbackForm"]').serializeArray();

            Restangular.all('api/send-feedback')
	            .post(data)
	            .then(function(res){
	            	console.log(res);
	            	if (res.status == 'ok') {
	            		$('.form-success-send').fadeIn(300);
	            	}

	            }, function(res){
	            	console.log(res);
                    toastr.error('Something went wrong. Please reload page and try again');
	            });

        }

    }]);

})();