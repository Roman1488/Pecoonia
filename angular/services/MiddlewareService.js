(function () {
    'use strict';

    angular.module("app.services")
           .factory('AuthMiddleware', AuthMiddleware)
           .factory('RouteMiddleware', RouteMiddleware);

    function AuthMiddleware(PecooniaApiService, $state, $rootScope) {

        var AuthMiddleware = this;

        //if user is not logged in re-direct to login route
        AuthMiddleware.run = function(event){
            if(!PecooniaApiService.isLogged()){
                //event.preventDefault();
                console.log('You are not logged in, so you cant browse this');
                $state.go('landing');
            }
            else
            {
                // check if need to open force password change pop-up
                if (($rootScope.currentState.name != 'panel.create') && $rootScope.user && $rootScope.user.force_change_password)
                {
                    if (event)
                        event.preventDefault();

                    $state.go('panel.create', {}, {reload:true});
                }
            }
        };

        return {
            run : AuthMiddleware.run
        };
    }

    function RouteMiddleware(PecooniaApiService, $state, $rootScope) {

        var RouteMiddleware = this;

        RouteMiddleware.run = function(event){
            var currentPortfolio = PecooniaApiService.getCurrentPortfolio();

            // If it is Private Portfolio, don't allow Book Value form to open

            if (currentPortfolio && !currentPortfolio.is_company)
            {
                if (event)
                    event.preventDefault();

                $state.go('panel.create');
            }
        };

        return {
            run : RouteMiddleware.run
        };
    }

})();