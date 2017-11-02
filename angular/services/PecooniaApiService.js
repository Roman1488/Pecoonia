(function(){
    "use strict";

    angular.module("app.services").factory('PecooniaApiService', function(Restangular, $window, $rootScope, $state){

        var cache            = {};
        var isLoggedIn       = false;
        var currentPortfolio = false;

        function setCurrentPortfolio(portfolio)
        {
            currentPortfolio = portfolio;
        }

        function getCurrentPortfolio()
        {
            return currentPortfolio;
        }

        function setHeader(){
            var token = getAuthToken();
            Restangular.setDefaultHeaders({Authorization: 'bearer ' + token});
        }

        function cleanCache(name){
            if (name !== undefined){
                cache[name] = null;
            } else {
                currentPortfolio = null;
                cache = {};
            }
        }

        function isLogged(){
            return isLoggedIn;
        }

        function getCache(name){
            return cache[name];
        }

        function getAuthToken(){
            if (cache.token)
                return cache.token;
            if ($window.localStorage.jwtToken) {
                cache.token = $window.localStorage.jwtToken;
                return $window.localStorage.jwtToken;
            } else {
                return;
            }
        }

        function getCurrency(onSuccess, refresh){
            var r = refresh === undefined ? false : refresh;
            if (cache.currency && !r){
                if (onSuccess !== undefined)
                    onSuccess(cache.currency);
                return;
            }
            setHeader();
            Restangular.one('api/currency').get().then(function(res){
                cache.currency = res;
                if (onSuccess !== undefined)
                    onSuccess(res);
            });
        }

        function getCurrencyPairs(symbol, portfolioId, onSuccess, refresh){
            var r = refresh === undefined ? false : refresh;
            if (cache.currencyPairs && !r){
                if (onSuccess !== undefined)
                    onSuccess(cache.currencyPairs);
                return;
            }
            setHeader();
            Restangular.one('api/currency_pair/' + symbol + '/' + portfolioId).get().then(function(res){
                cache.currencyPairs = res;
                if (onSuccess !== undefined)
                    onSuccess(res);
            });
        }

        function auth(data, onSuccess, onError){
            $rootScope.loading = true;
            Restangular.all('api/authenticate')
                .post(data)
                .then(function(res){
                    setUser(res.item);

                    $state.go('panel.create');
                    $rootScope.loading = false;

                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function setUser(res) {
            if (res) {
                $window.localStorage.jwtToken = res.token;
                $rootScope.user = res.user;
                cache.token = res.token;

                isLoggedIn = true;

                return true;
            }

            return false;
        }

        function refresh(onSuccess){
            setHeader();
            Restangular.all('api/refreshtoken')
                .post()
                .then(function(res){
                    $window.localStorage.jwtToken = res.item.new_token;
                    cache.token = res.item.new_token;
                    isLoggedIn = true;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function logout(onSuccess, onError){
            setHeader();
            Restangular.all('api/endtoken')
                .post()
                .then(function(res){
                    removeUserSession();
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function removeUserSession()
        {
            $window.localStorage.removeItem('jwtToken');
            $rootScope.user = null;
            $rootScope.portfolio = null;
            cleanCache();
            $state.go('landing');
            isLoggedIn = false;
        }

        function check(onSuccess, onError){
            var token = getAuthToken();
            if (!token)
                return true;
            Restangular.one('api/me')
                .get({token: token})
                .then(function(res){
                    isLoggedIn = true;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function create(data, onSuccess, onError){
            $rootScope.loading = true;
            Restangular.one('api/user')
                .customPUT(data)
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function update(data, onSuccess, onError){
            $rootScope.loading = true;
            setHeader();
            Restangular.all('api/me')
                .post(data)
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function input(data, onSuccess){
            setHeader();
            Restangular.one('api/input')
                .get(data)
                .then(function(res){
                    if (onSuccess !== undefined)
                        onSuccess(res);
                });
        }

        function getPortfolioStatistics(id, onSuccess){
            setHeader();
            Restangular.one('api/portfolio/' + id + '/statistics')
                .get()
                .then(function(res){
                    if (onSuccess !== undefined)
                        onSuccess(res);
                });
        }

        function getPortfolio(id, onSuccess){
            setHeader();
            Restangular.one('api/portfolio/' + id)
                .get()
                .then(function(res){
                    if (onSuccess !== undefined)
                        onSuccess(res);
                });
        }


        function getPortfolioBanks(id, onSuccess){
            setHeader();
            Restangular.one('api/portfolio/' + id + '/banks')
                .get()
                .then(function(res){
                    if (onSuccess !== undefined)
                        onSuccess(res);
                });
        }

        function getPortfolios(onSuccess, refresh){
            var r = refresh === undefined ? false : refresh;
            if (cache.portfolios && !r){
                if (onSuccess !== undefined)
                    onSuccess(cache.portfolios);
                return;
            }
            setHeader();
            Restangular.one('api/portfolio')
                .get()
                .then(function(res){
                    cache.portfolios = res;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                });
        }

        function getAllPortfolios(onSuccess){
            setHeader();
            Restangular.one('api/portfolio/all')
                .get()
                .then(function(res){
                    if (onSuccess !== undefined)
                        onSuccess(res);
                });
        }

        function updatePortfolio(id, data, onSuccess){
            setHeader();
            Restangular.all('api/portfolio/' + id)
                .post(data)
                .then(function(res){
                    if (onSuccess !== undefined)
                        onSuccess(res);
                });
        }

        function deletePortfolio(id, data, onSuccess, onError){
            $rootScope.loading = true;
            setHeader();
            Restangular.all('api/portfolio/' + id + '/delete')
                .post(data)
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function createPortfolio(data, onSuccess, onError){
            $rootScope.loading = true;
            setHeader();
            Restangular.one('api/portfolio')
                .put(data)
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function deleteUserAccount(data, onSuccess, onError){
            $rootScope.loading = true;
            setHeader();
            Restangular.all('api/me/delete')
                .post(data)
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function getBanks(onSuccess, refresh){
            var r = refresh === undefined ? false : refresh;
            if (cache.banks && !r){
                if (onSuccess !== undefined)
                    onSuccess(cache.banks);
                return;
            }
            setHeader();
            Restangular.one('api/bank')
                .get()
                .then(function(res){
                    cache.banks = res;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                });
        }

        function createBank(data, onSuccess, onError){
            $rootScope.loading = true;
            setHeader();
            Restangular.one('api/bank')
                .put(data)
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function updateBank(id, data, onSuccess){
            setHeader();
            Restangular.all('api/bank/' + id)
                .post(data)
                .then(function(res){
                    if (onSuccess !== undefined)
                        onSuccess(res);
                });
        }

        function createTransaction(type, data, onSuccess, onError){
            $rootScope.loading = true;
            setHeader();
            Restangular.one('api/transaction/' + type)
                .customPUT(data)
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function addNewSecurities(data, onSuccess, onError){
            $rootScope.loading = true;
            Restangular.all('api/add_new_securities/')
                .post(data)
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function getSecurities(onSuccess, refresh){
            var r = refresh === undefined ? false : refresh;
            if (cache.securities && !r){
                if (onSuccess !== undefined)
                    onSuccess(cache.securities);
                return;
            }
            setHeader();
            Restangular.one('api/securities')
                .get()
                .then(function(res){
                    cache.securities = res;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                });
        }

        function postSecurities(data, onSuccess){
            setHeader();
            Restangular.all('api/securities')
                .post(data)
                .then(function(res){
                    if (onSuccess !== undefined)
                        onSuccess(res);
                });
        }

        function findSecurity(security_keyword, onSuccess, onError){
            if (!security_keyword)
            {
                return;
            }
            setHeader();
            $rootScope.loading = true;
            Restangular.one('api/security/find/' + security_keyword)
                .get()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function getTransactions(by, id, page, search_term, onSuccess, onError){
            setHeader();
            $rootScope.loading = true;
            Restangular.one('api/' + by + '/' + id + '/transactions')
                .get({page: page, search: search_term})
                .then(function(res){
                    $rootScope.loading = false;
                    cache.transactions = res;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res) {
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }


        function createGuideline(portfolioId, data, onSuccess, onError){
            $rootScope.loading = true;
            setHeader();
            Restangular.all('api/portfolio_guidelines_add/' + portfolioId)
                .post(data)
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function removeGuideline (portfolioId, guidelineId, onSuccess, onError) {
            $rootScope.loading = true;
            setHeader();
            Restangular.all('api/portfolio_guidelines/' + portfolioId + '/' + guidelineId)
                .customDELETE()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function getGuidelinesList (onSuccess, onError) {
            setHeader();

            $rootScope.loading = true;
            Restangular.one('api/portfolio_guidelines_list')
                .get()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res) {
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function getGuidelineItemsList (portfolioId, onSuccess, onError) {
            setHeader();

            $rootScope.loading = true;
            Restangular.one('api/portfolio_guidelines/' + portfolioId)
                .get()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res) {
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function getGuidelinesCurrencies (portfolioId, onSuccess, onError) {
            setHeader();

            $rootScope.loading = true;
            Restangular.one('api/portfolio/'+ portfolioId +'/currencies')
                .get()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res) {
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function getGuidelinesSecurityType (portfolioId, onSuccess, onError) {
            setHeader();

            $rootScope.loading = true;
            Restangular.one('api/portfolio/'+ portfolioId +'/security_types')
                .get()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res) {
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }


        function getGuidelinesTags (portfolioId, onSuccess, onError) {
            setHeader();

            $rootScope.loading = true;
            Restangular.one('api/portfolio/'+ portfolioId +'/tags')
                .get()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                    {
                        var response = {};
                        response.status = res.status;
                        response.message = res.message;
                        response.item = [];
                        $.each(res.item, function(k, v){
                            response.item.push({'attrName':v, 'attribute':'tag', 'subAttrName':v});
                        });
                        onSuccess(response);
                    }
                }, function(res) {
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function getBankTransactions(bank_id, page, onSuccess, onError){
            var r = refresh === undefined ? false : refresh;
            if (cache.bank_transacations && !r){
                if (onSuccess !== undefined)
                    onSuccess(cache.bank_transacations);
                return;
            }
            setHeader();
            $rootScope.loading = true;
            Restangular.one('api/bank/' + bank_id + '/transactions')
                .get({page: page})
                .then(function(res){
                    $rootScope.loading = false;
                    cache.bank_transacations = res;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                });
        }

        function getPortfolioTags(id, onSuccess){
            setHeader();
            Restangular.one('api/portfolio/' + id + '/tags')
                .get()
                .then(function(res){
                    if (onSuccess !== undefined)
                        onSuccess(res);
                });
        }

        function getBank(bank_id, onSuccess, onError){
            var r = refresh === undefined ? false : refresh;
            if (cache.bank && !r){
                if (onSuccess !== undefined)
                    onSuccess(cache.bank);
                return;
            }
            setHeader();
            $rootScope.loading = true;
            Restangular.one('api/bank/' + bank_id)
                .get()
                .then(function(res){
                    $rootScope.loading = false;
                    cache.bank = res;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                });
        }

        function deleteBank(id, onSuccess, onError){
            setHeader();
            $rootScope.loading = true;
            Restangular.all('api/bank/' + id)
                .customDELETE()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res) {
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function getUnwatchedSecurities(portfolio_id, onSuccess, onError){
            setHeader();
            $rootScope.loading = true;
            Restangular.one('api/get_unwatched_securities/' + portfolio_id)
                .get()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res) {
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function getSecurityWatchlist(portfolio_id, onSuccess, onError){
            setHeader();
            $rootScope.loading = true;
            Restangular.one('api/get_security_watchlist/' + portfolio_id)
                .get()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res) {
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }


        function addSecuritiesToWatchlist(data, onSuccess, onError)
        {
            $rootScope.loading = true;
            Restangular.all('api/add_securities_to_watchlist/')
                .post(data)
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function removeSecurityFromWatchlist(portfolio_id, security_id, onSuccess, onError)
        {
            $rootScope.loading = true;
            Restangular.one('api/remove_security_from_watchlist/' + portfolio_id + '/' + security_id)
                .get()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function getPortfolioHoldings(id, onSuccess, onError){
            setHeader();
            $rootScope.loading = true;
            Restangular.one('api/portfolio/' + id + '/holdings')
                .get()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res) {
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }


        function portfolioHoldingsHistory(id, data, onSuccess, onError)
        {
            $rootScope.loading = true;
            Restangular.all('api/portfolio/' + id + '/holdings')
                .post(data)
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
            });
        }

        function getIncomeAnalysis(portfolio_id, data, onSuccess, onError)
        {
            $rootScope.loading = true;
            Restangular.all('api/get_income_analysis/' + portfolio_id)
                .post(data)
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function getHoldingsAnalysis(portfolio_id, data, onSuccess, onError)
        {
            $rootScope.loading = true;
            Restangular.all('api/get_holdings_analysis/' + portfolio_id)
                .post(data)
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }


        function getHoldingsAnalysisAttributes(portfolio_id, onSuccess, onError)
        {
            setHeader();
            $rootScope.loading = true;
            Restangular.one('api/get_holdings_analysis_attributes/' + portfolio_id)
                .get()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res) {
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function forceChangePassword(data, onSuccess, onError) {
            $rootScope.loading = true;
            setHeader();
            Restangular.all('api/user/force_change_password')
                .post(data)
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function getBookValueAmount(data, onSuccess, onError) {
            $rootScope.loading = true;
            setHeader();
            Restangular.one('api/transaction/bookvalue/count')
                .customPUT(data)
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function getPortfolioDailyValues(id, onSuccess, onError, days) {
            var $url;
            $rootScope.loading = true;
            setHeader();

            if (days !== undefined) {
                $url = 'api/portfolio/' + id + '/daily_values/' + days;
            } else {
                $url = 'api/portfolio/' + id + '/daily_values';
            }

            Restangular.one($url)
                .get()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function forgotUsernamePassword(data, onSuccess, onError) {
            $rootScope.loading = true;
            setHeader();
            Restangular.all('api/password/email')
                .post(data)
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function resetPassword(data, onSuccess, onError) {
            $rootScope.loading = true;
            setHeader();
            Restangular.all('api/password/reset')
                .post(data)
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function deleteTransaction(id, onSuccess, onError){
            setHeader();
            $rootScope.loading = true;
            Restangular.one('api/transaction/' + id)
                .customDELETE()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res) {
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function getAllTags(onSuccess, onError){
            setHeader();
            $rootScope.loading = true;
            Restangular.one('api/all_tags')
                .get()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res) {
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function getHoldingTransactions(portfolio_id, security_id, onSuccess, onError)
        {
            $rootScope.loading = true;
            setHeader();
            Restangular.one('api/holding_transactions/'+portfolio_id+'/'+security_id)
                .get()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res) {
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function getKeyFigures(portfolio_id, data, onSuccess, onError)
        {
            $rootScope.loading = true;
            Restangular.all('api/get_key_figures/'+portfolio_id)
                .post(data)
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res){
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function deleteTag(tag, onSuccess, onError){
            setHeader();
            $rootScope.loading = true;
            Restangular.one('api/tag/' + tag)
                .customDELETE()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res) {
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        function closeWelcomeMsg(onSuccess, onError){
            setHeader();
            $rootScope.loading = true;
            Restangular.one('api/user/close_welcome_msg')
                .get()
                .then(function(res){
                    $rootScope.loading = false;
                    if (onSuccess !== undefined)
                        onSuccess(res);
                }, function(res) {
                    $rootScope.loading = false;
                    if (onError !== undefined)
                        onError(res);
                });
        }

        return {
            auth: auth,
            refresh: refresh,
            check: check,
            logout: logout,
            setUser: setUser,
            removeUserSession: removeUserSession,
            create: create,
            update: update,
            input: input,
            getCurrency: getCurrency,
            getCurrencyPairs: getCurrencyPairs,
            getCache: getCache,
            cleanCache: cleanCache,
            isLogged: isLogged,
            getPortfolio: getPortfolio,
            getPortfolioBanks: getPortfolioBanks,
            getPortfolioTags: getPortfolioTags,
            getAllTags: getAllTags,
            getHoldingTransactions: getHoldingTransactions,
            getKeyFigures: getKeyFigures,
            getPortfolios: getPortfolios,
            getAllPortfolios: getAllPortfolios,
            updatePortfolio: updatePortfolio,
            deletePortfolio: deletePortfolio,
            createPortfolio: createPortfolio,
            deleteUserAccount: deleteUserAccount,
            getBanks: getBanks,
            getBank: getBank,
            createBank: createBank,
            updateBank: updateBank,
            createTransaction: createTransaction,
            addNewSecurities: addNewSecurities,
            getSecurities: getSecurities,
            postSecurities: postSecurities,
            findSecurity: findSecurity,
            getTransactions: getTransactions,
            getGuidelinesList: getGuidelinesList,
            getGuidelineItemsList: getGuidelineItemsList,
            getGuidelinesCurrencies: getGuidelinesCurrencies,
            getGuidelinesSecurityType: getGuidelinesSecurityType,
            getGuidelinesTags: getGuidelinesTags,
            createGuideline: createGuideline,
            removeGuideline: removeGuideline,
            getBankTransactions: getBankTransactions,
            getCurrentPortfolio: getCurrentPortfolio,
            setCurrentPortfolio: setCurrentPortfolio,
            getPortfolioStatistics:getPortfolioStatistics,
            deleteBank: deleteBank,
            getUnwatchedSecurities: getUnwatchedSecurities,
            getSecurityWatchlist: getSecurityWatchlist,
            addSecuritiesToWatchlist: addSecuritiesToWatchlist,
            removeSecurityFromWatchlist: removeSecurityFromWatchlist,
            getPortfolioHoldings: getPortfolioHoldings,
            portfolioHoldingsHistory: portfolioHoldingsHistory,
            getIncomeAnalysis: getIncomeAnalysis,
            getHoldingsAnalysis: getHoldingsAnalysis,
            getHoldingsAnalysisAttributes: getHoldingsAnalysisAttributes,
            forceChangePassword: forceChangePassword,
            getBookValueAmount: getBookValueAmount,
            getPortfolioDailyValues: getPortfolioDailyValues,
            forgotUsernamePassword: forgotUsernamePassword,
            resetPassword: resetPassword,
            deleteTransaction: deleteTransaction,
            deleteTag: deleteTag,
            closeWelcomeMsg: closeWelcomeMsg,
        };

    });
})();
