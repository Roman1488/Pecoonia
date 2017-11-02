(function(){
    "use strict";

    angular.module('app.routes').config( function($stateProvider, $urlRouterProvider) {

        var getView = function( viewName ){
            return './vendor/html/' + viewName + '/' + viewName + '.html';
        };

        $urlRouterProvider.otherwise('/');

        $stateProvider
            .state('landing', {
                url: '/',
                templateUrl: 'angular/index',
                controller: 'MainController as MainCtrl',
                ncyBreadcrumb: {
                    skip: true
                }
            })
            .state('user-activated', {
                url: '/user-activated',
                templateUrl: 'angular/index',
                controller: 'MainController as MainCtrl',
                ncyBreadcrumb: {
                    skip: true
                }
            })
            .state('404', {
                url: '/404',
                templateUrl: 'angular/index',
                controller: 'MainController as MainCtrl',
                ncyBreadcrumb: {
                    skip: true
                }
            })
            .state('register', {
                url: '/register',
                templateUrl: 'angular/auth/register',
                controller: 'AuthController as Auth',
                ncyBreadcrumb: {
                    skip: true
                }
            })
	        .state('forgot', {
                url: '/forgot',
                templateUrl: 'angular/auth/forgot',
                controller: 'AuthController as Auth',
                ncyBreadcrumb: {
                    skip: true
                }
            })
            .state('update_password', {
                url: '/update_password',
                templateUrl: 'angular/auth/update_password',
                controller: 'AuthController as Auth',
                middleware: ['AuthMiddleware'],
                ncyBreadcrumb: {
                    skip: true
                }
            })
            .state('reset_password', {
                url: '/password/reset/:token',
                templateUrl: 'angular/auth/passwords/reset',
                controller: 'AuthController as Auth',
                ncyBreadcrumb: {
                    skip: true
                }
            })
            .state('panel', {
                abstract: true,
                templateUrl: 'angular/panel'
            })
            .state('panel.create', {
                url: '/create',
                templateUrl: 'angular/pages/create',
                controller: 'PortfolioController',
                middleware: ['AuthMiddleware'],
                ncyBreadcrumb: {
                    label: ' Home ( Portfolios )'
                }
            })
            .state('panel.settings', {
                url: '/settings',
                templateUrl: 'angular/pages/settings',
                controller: 'SettingsController',
                middleware: ['AuthMiddleware'],
                ncyBreadcrumb: {
                    parent: 'panel.create',
                    label: 'Settings'
                }
            })
            .state('panel.profile', {
                url: '/profile',
                templateUrl: 'angular/pages/profile',
                controller: 'SettingsController',
                middleware: ['AuthMiddleware'],
                ncyBreadcrumb: {
                    parent: 'panel.create',
                    label: 'Profile'
                }
            })
            .state('panel.profile_edit', {
                url: '/profile_edit',
                templateUrl: 'angular/pages/profile_edit',
                controller: 'SettingsController',
                middleware: ['AuthMiddleware'],
                ncyBreadcrumb: {
                    parent: 'panel.create',
                    label: 'Edit Profile'
                }
            })
            .state('panel.portfolio', {
                url: '/create/portfolio',
                templateUrl: 'angular/pages/portfolio',
                controller: 'CreateController',
                middleware: ['AuthMiddleware'],
                ncyBreadcrumb: {
                    parent: 'panel.create',
                    label: 'Create New Portfolio'
                }
            })
            .state('panel.bank', {
                url: '/create/bank?portfolio_id',
                templateUrl: 'angular/pages/bank',
                controller: 'CreateController',
                middleware: ['AuthMiddleware'],
                ncyBreadcrumb: {
                    parent: 'panel.show.portfolio({id: currentPortfolio.id})',
                    label: 'Create New Bank'
                }
            })
            .state('panel.show', {
                abstract: true,
                template: '<ui-view/>'
            })
            .state('panel.show.portfolio', {
                url: '/portfolio/:id',
                templateUrl: 'angular/pages/portfolio-single',
                controller: 'CreateController',
                middleware: ['AuthMiddleware'],
                ncyBreadcrumb: {
                    parent: 'panel.create',
                    label: 'Portfolio {{currentPortfolio.name}}'
                }
            })
            .state('panel.show.portfolio_banks', {
                url: '/portfolio/:id/banks',
                templateUrl: 'angular/pages/portfolio-bank',
                controller: 'CreateController',
                middleware: ['AuthMiddleware'],
                ncyBreadcrumb: {
                    parent: 'panel.show.portfolio({id: currentPortfolio.id})',
                    label: 'Bank Accounts'
                }
            })
            .state('panel.show.banks_balance', {
                url: '/portfolio/:id/bank/:bank_id/balance',
                templateUrl: 'angular/pages/bank-balance',
                controller: 'BankController',
                middleware: ['AuthMiddleware'],
                ncyBreadcrumb: {
                    parent: 'panel.show.portfolio_banks({id: currentPortfolio.id})',
                    label: '{{bank.name}} Bank'
                }
            })
            .state('panel.transactions', {
                url: '/transactions',
                abstract: true,
                template: '<ui-view/>'
            })
            .state('panel.transactions.cash', {
                url: '/cash',
                templateUrl: 'angular/forms/cash',
                controller: 'TransactionsController',
                middleware: ['AuthMiddleware'],
                ncyBreadcrumb: {
                    parent: 'panel.show.portfolio({id: currentPortfolio.id})',
                    label: 'Cash Transaction'
                }
            })
            .state('panel.transactions.securities', {
                url: '/securities',
                templateUrl: 'angular/forms/securities',
                controller: 'TransactionsController',
                middleware: ['AuthMiddleware'],
                ncyBreadcrumb: {
                    parent: 'panel.show.portfolio({id: currentPortfolio.id})',
                    label: 'Securities Transaction'
                }
            })
            .state('panel.transactions.dividend', {
                url: '/dividend',
                templateUrl: 'angular/forms/dividend',
                controller: 'TransactionsController',
                middleware: ['AuthMiddleware'],
                ncyBreadcrumb: {
                    parent: 'panel.show.portfolio({id: currentPortfolio.id})',
                    label: 'Dividend Transaction'
                }
            })
            .state('panel.transactions.book_value', {
                url: '/book_value',
                templateUrl: 'angular/forms/book_value',
                controller: 'TransactionsController',
                middleware: ['AuthMiddleware', 'RouteMiddleware'],
                ncyBreadcrumb: {
                    parent: 'panel.show.portfolio({id: currentPortfolio.id})',
                    label: 'Book Value Transaction'
                }
            })
            .state('panel.transactions.portfolio', {
                url: '/portfolio/:id',
                templateUrl: 'angular/pages/transactions',
                controller: 'TransactionsController',
                middleware: ['AuthMiddleware'],
                ncyBreadcrumb: {
                    parent: 'panel.show.portfolio({id: currentPortfolio.id})',
                    label: 'Transactions Page'
                }
        });

    }).run( function($stateParams, $rootScope, $transitions, $injector, $state, PecooniaApiService) {

        $transitions.onStart({}, function(transition, event) {
            var currentState = transition.$to();
            var currentPortfolio = PecooniaApiService.getCurrentPortfolio();

            if (currentState.name==='landing'&&$rootScope.user) return $state.target('panel.create');

            $rootScope.portfolio =  (currentPortfolio) ? currentPortfolio.id : false; //(currentState.name == 'panel.show.portfolio' || currentState.name == 'panel.transactions.portfolio') ? $stateParams.id : false;

            $rootScope.currentState = currentState;

            callMiddlewares(event, currentState);

            function callMiddlewares(event, state){
                if(state && state.hasOwnProperty('middleware')){
                    if (typeof currentState.middleware === 'object') {
                        angular.forEach(state.middleware, function (middleWare) {
                            callMiddleware(middleWare, event);
                        });
                        return;
                    }
                }
            }

            function callMiddleware(middleWare, event) {
                try{
                    $injector.get(middleWare).run(event);
                }catch(e){
                    console.error('the factory : ' + middleWare + ' does not exist. ' + e);
                }
            }

            document.body.scrollTop = document.documentElement.scrollTop = 0;
        });

        // document functions
        $(document).on('click', function(e) {
            if (!$(e.target).parents('#nav_bar').length) $('body.open-header.close-header-on-scroll').removeClass("side-header-open");
        });
    });
})();

