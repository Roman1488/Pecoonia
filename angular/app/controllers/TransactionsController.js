(function(){
    "use strict";

    var app = angular.module('app.controllers');

    app.value('currentModalTransactionType', null);


    app.controller('TransactionsController', ['$scope', '$state', '$stateParams', 'PecooniaApiService', 'CommonFunctionsService', 'toastr', '$rootScope', '$filter', '$uibModal', 'currentModalTransactionType', function($scope, $state, $stateParams, PecooniaApiService, CommonFunctionsService, toastr, $rootScope, $filter, $uibModal, currentModalTransactionType){

        $scope.isModalRequested = true;
        $scope.transactionsSearch = '';

        $scope.transactionsSearchArray = [];

        $scope.templates = {};
        $scope.options = [
            {id: 'Buy', name: 'Buy'},
            {id: 'Sell', name: 'Sell'}
        ];



        $scope.closeModal = function () {
            if ($rootScope.transactionModalInstance) $rootScope.transactionModalInstance.close();
        };

        $scope.showSecurities = function(item){
            name = item.name.toLowerCase();
            switch(name) {
                case 'buy':
                    $scope.getSecurities();
                    $scope.resetSecurityQuantity();
                    $scope.securities.action = name;
                    break;
                default:
                    $scope.postSecurities('sell');
                    $scope.resetSecuritySelection();
                    $scope.securities.action = name;
                }

        };

        if (currentModalTransactionType == null)
        {
            $scope.isModalRequested = false;
            currentModalTransactionType = $state.current.name;
        }

        $scope.currentPortfolio = PecooniaApiService.getCurrentPortfolio();

        var portfolioCurrencySym = ($scope.currentPortfolio && $scope.currentPortfolio.currency) ? $scope.currentPortfolio.currency.symbol : false;

        var pattObj = CommonFunctionsService.getDecCharAndPattern($scope.currentPortfolio);

        $scope.twoDecimalPattern  = pattObj.twoDecimalPattern;
        $scope.fourDecimalPattern = pattObj.fourDecimalPattern;
        $scope.decimalChar        = pattObj.decimalChar;

        $scope.cash = {
            action: 'deposit'
        };

        $scope.securities = {
            is_commision: 1
        };

        $scope.dividend = {
            is_tax_included: 0
        };

        $scope.bookValue = {};

        $scope.formatForDatePicker = CommonFunctionsService.getCurrPortfolioDateFormat().toLowerCase().replace('mm', 'MM');

        // Set default Date to Today
        $scope.securities.date =
        $scope.dividend.date =
        $scope.bookValue.date =
        $scope.cash.date =
        new Date();

        PecooniaApiService.getPortfolioStatistics($stateParams.id, function(res){
            $scope.portfolioStatistics = res.item;
        });

        $scope.goToTransactionList = function(p_id) {
            if ($rootScope.transactionModalInstance)
            {
                $rootScope.transactionModalInstance.close();
            }
            $state.go("panel.transactions.portfolio", {id : p_id});
        };

        $scope.getTags = function(search) {
            if ((search.indexOf(' ') >= 0) || !$scope.tags) return;
            var newTags = $scope.tags.slice();
            if (search && newTags.indexOf(search) === -1) {
                newTags.unshift(search);
            }
            return newTags;
        };

        //here we find open modal transaction type & allow that modal to access scope as like normal state

        $scope.currentModel = false;

        if (currentModalTransactionType == 'panel.transactions.securities')
        {
            $scope.currentModel = 'securities';

            $scope.$watch('securities.tags.length', function(length) {
                if (length)
                {
                    var valid = length <= 20;
                    $scope.securitiesForm.tags.$setValidity("maxUiLength", valid);
                }
            });
        }
        else if (currentModalTransactionType == 'panel.transactions.dividend')
        {
            $scope.currentModel = 'dividend';
        }
        else if (currentModalTransactionType == 'panel.transactions.book_value')
        {
            $scope.currentModel = 'bookValue';
        }
        else if (currentModalTransactionType == 'panel.transactions.cash')
        {
            $scope.currentModel = 'cash';
        }


        $scope.localCurrencyText = 'Local currency';
        $scope.isCurrencyOk = false;

        $scope.format = 'dd-MM-yyyy';

        $scope.displayBankDropdown = true;
        $scope.displayBankName     = false;

        // Fetch and set Banks

        if($scope.currentModel !== 'bookValue')
        {
            if($scope.currentPortfolio && $scope.currentPortfolio.id)
            {
                PecooniaApiService.getPortfolioBanks($scope.currentPortfolio.id, function(res){
                    $scope.setBankList(res);
                });
            }
            else
            {
                PecooniaApiService.getBanks(function(res){
                    $scope.setBankList(res);
                });
            }
        }

        // end

        // Fetch and set Tags

        if($scope.currentModel == 'securities')
        {
            if($scope.currentPortfolio && $scope.currentPortfolio.id)
            {
                PecooniaApiService.getPortfolioTags($scope.currentPortfolio.id, function(res){
                    $scope.tags = res.item;
                });
            }
        }

        // end

        $scope.setBankList = function (res) {

            var res = res || false;

            if (res)
            {
                $scope.banks = res.item;
            }

            if ($scope.banks && $scope.currentModel)
            {
                if ($scope.banks.length === 1)
                {
                    $scope[$scope.currentModel].bank = $scope.banks[0];
                    $scope.displayBankName           = $scope[$scope.currentModel].bank.name;

                    if ($scope.currentModel === 'cash')
                    {
                        $scope[$scope.currentModel].currency =  $scope[$scope.currentModel].bank.currency;
                    }
                }
                else if (0 === $scope.banks.length)
                {
                    if (($scope.currentModel === 'cash') && $scope.currentPortfolio)
                    {
                        var noBankMsg = 'You currently don\'t have a bank account for your portfolio. Please <a href="/#!/create/bank?portfolio_id=' + $scope.currentPortfolio.id + '">create a bank account</a> if you want to make a cash transaction.';
                        $rootScope.alerts.push({msg: noBankMsg, type: "alert-warning", title: "No Bank Account created."});
                    }

                    $scope.displayBankDropdown = false;
                }
            }
        };

        $scope.allPortfolios = $rootScope.userPortfolios;

        if ($stateParams.id !== undefined) {
            PecooniaApiService.getPortfolio($stateParams.id, function(res){
                $scope.currentPortfolio = res.item;
                $scope.format = $scope.currentPortfolio.date_format;
                $scope.decimalChar = $rootScope.decimalMark[$scope.currentPortfolio.comma_separator];
                var separatorIndex = $scope.currentPortfolio.comma_separator ? 0 : 1;
                $scope.thousandsDecimalChar = $rootScope.decimalMark[separatorIndex];
                initLoadTransactions();
            });
        }

        function initLoadTransactions()
        {
            $scope.page  = 1;
            $scope.total = 0;
            PecooniaApiService.getTransactions('portfolio', $stateParams.id, $scope.page, '',
                function(res){
                    $scope.currentPortfolioId = $stateParams.id;
                    $scope.total = res.total;
                    $scope.page = res.next_page;
                    $scope.allPortfolioTransactions = res.item;
                    $scope.transactionFields = res.transaction_fields;
                    $scope.transactionTypes = res.transaction_types;
                }
            );
        }

        $scope.postSecurities = function(pageName) {
            var postData = {
                portfolio_id: $scope.currentPortfolio.id,
                page: pageName
            };

            PecooniaApiService.postSecurities(postData, function(res){
                $scope.allSecurities = res.item;
            });
        };

        $scope.getSecurities = function() {
            PecooniaApiService.getSecurities(function(res){
                $scope.allSecurities = res.item;
            });
        };

        $scope.resetSecuritySelection = function() {
            $scope.securities.securities = null;
        };

        $scope.resetSecurityQuantity = function() {
            $scope.securities.quantity = null;
        };

        if ($scope.currentModel === 'dividend' || $scope.currentModel === 'bookValue')
        {
            $scope.postSecurities($scope.currentModel);
        }
        else
        {
            $scope.getSecurities();
        }

        $scope.loadMore = function(){

            if (!$scope.currentPortfolioId || $scope.page == -1) {
                toastr.error('Sorry, there is no more transactions.');
                return;
            }
            PecooniaApiService.getTransactions('portfolio', $scope.currentPortfolioId, $scope.page, $scope.transactionsSearch.trim(), function(res) {
                $scope.total = res.total;
                $scope.page = res.next_page;
                $scope.allPortfolioTransactions = $scope.allPortfolioTransactions.concat(res.item);
            });
        };

        $scope.goToTop = function(){

            $('body,html').stop(true).animate({
                'scrollTop': 0
            }, 700, 'easeOutQuad');
            return false;
        };

        $scope.showAll = function(){

            if (!$scope.currentPortfolioId) {
                toastr.error('Choose portfolio first.');
                return;
            } else if ($scope.page == -1) {
                toastr.success('All transactions up to date.');
                return;
            }
            PecooniaApiService.getTransactions('portfolio', $scope.currentPortfolioId, -2, $scope.transactionsSearch.trim(), function(res) {
                $scope.total = res.total;
                $scope.page = -1;
                $scope.allPortfolioTransactions = res.item;
            });
        };

        function formatTrNumber(v, fraction_size) {
            return ($scope.currentPortfolio) ? $filter('decimalSeparator')($scope.currentPortfolio.comma_separator, v, fraction_size) : v;
        }

        $scope.formatTrDate = function(t){
            return moment(t.date, 'YYYY-MM-DD').format($scope.format.toUpperCase());
        };

        $scope.formatTrName = function(t){
            var result = '',
                d   = formatTrNumber(t.c_net_dividend_base),
                tq  = formatTrNumber(t.c_trade_quote_base),
                b   = formatTrNumber(t.c_book_value_local),
                tv  = formatTrNumber(t.trade_value),
                bkb = formatTrNumber(t.c_book_value_base);

                if (t.transaction_type == 'buy')
                {
                    var q = formatTrNumber(t.original_quantity, 0);
                }
                else
                {
                    var q = formatTrNumber(t.quantity, 0);
                }

            var securityCurrencyId  = (t.security) ? t.security.currency.id : false;
            var portfolioCurrencyId = $scope.currentPortfolio ? $scope.currentPortfolio.currency.id : false;

            if (portfolioCurrencyId && securityCurrencyId && portfolioCurrencyId != securityCurrencyId)
            {
                tq  = formatTrNumber(t.c_trade_quote_local);
                d   = formatTrNumber(t.c_net_dividend_local);
            }

            switch(t.transaction_type) {
                case 'buy':
                    result = '<strong>' + q + '</strong> shares of <strong>' + t.security.name
                        + '</strong> were bought at <strong>' + tq + ' '
                        + t.security.currency.symbol + '</strong>';
                    break;

                case 'sell':
                    result = '<strong>' + q + '</strong> shares of <strong>' + t.security.name
                        + '</strong> were sold at <strong>' + tq + '</strong> '
                        + t.security.currency.symbol;
                    break;
                case 'dividend':
                    result = '<strong>' + t.security.name + '</strong> paid <strong>' + d + ' '
                        + t.security.currency.symbol + '</strong> in net dividends';
                    break;
                case 'bookvalue':
                    result = 'Book value of <strong>' + t.security.name + '</strong> set at <strong>'
                        + b + ' ' + t.security.currency.symbol + '</strong>';

                        if (portfolioCurrencyId && securityCurrencyId && portfolioCurrencyId != securityCurrencyId)
                        {
                            result += ' equal to ' + bkb + ' ' + $scope.currentPortfolio.currency.symbol;
                        }
                    break;
                case 'cash_deposit':
                    result = 'Cash deposit of <strong>' + tv + ' ' + t.bank.currency.symbol
                        + '</strong> made to <strong>' + t.bank.name + '</strong>';
                    break;
                case 'cash_withdraw':
                    result = 'Cash withdraw of <strong>' + tv + ' ' + t.bank.currency.symbol
                        + '</strong> made to <strong>' + t.bank.name + '</strong>';
                    break;
            }
            return result;
        };

        $scope.tableRowExpanded = false;
        $scope.tableRowIndexExpandedCurr = '';
        $scope.tableRowIndexExpandedPrev = '';
        $scope.storeIdExpanded = '';

        $scope.tableDataCollapseFn = function () {
            $scope.tableDataCollapse = [];
            for (var i = 0; i < $scope.allPortfolioTransactions.length; i += 1) {
                $scope.tableDataCollapse.push(false);
            }
        };

        $scope.selectTableRow = function (index, storeId) {
            if (typeof $scope.tableDataCollapse === 'undefined') {
                $scope.tableDataCollapseFn();
            }

            if ($scope.tableRowExpanded === false && $scope.tableRowIndexExpandedCurr === '' && $scope.storeIdExpanded === '') {
                $scope.tableRowIndexExpandedPrev = '';
                $scope.tableRowExpanded = true;
                $scope.tableRowIndexExpandedCurr = index;
                $scope.storeIdExpanded = storeId;
                $scope.tableDataCollapse[index] = true;
            } else if ($scope.tableRowExpanded === true) {
                if ($scope.tableRowIndexExpandedCurr === index && $scope.storeIdExpanded === storeId) {
                    $scope.tableRowExpanded = false;
                    $scope.tableRowIndexExpandedCurr = '';
                    $scope.storeIdExpanded = '';
                    $scope.tableDataCollapse[index] = false;
                } else {
                    $scope.tableRowIndexExpandedPrev = $scope.tableRowIndexExpandedCurr;
                    $scope.tableRowIndexExpandedCurr = index;
                    $scope.storeIdExpanded = storeId;
                    $scope.tableDataCollapse[$scope.tableRowIndexExpandedPrev] = false;
                    $scope.tableDataCollapse[$scope.tableRowIndexExpandedCurr] = true;
                }
            }

        };

        $scope.checkCurrency = function(s){
            var c = 'currency';
            if ($scope[s].bank && $scope[s].securities && $scope[s].bank.portfolio) {
                var bankCurrencyId      = ($scope[s].bank) ? $scope[s].bank[c].id : false;
                var securityCurrencyId  = $scope[s].securities[c].id;
                var portfolioCurrencyId = $scope[s].bank.portfolio[c].id;

                if (bankCurrencyId && securityCurrencyId && portfolioCurrencyId)
                {
                    $scope.isCurrencyOk = (bankCurrencyId == securityCurrencyId || bankCurrencyId == portfolioCurrencyId) ? false : true;
                }

                // $scope.isCurrencyOk = (![$scope[s].bank[c].id, $scope[s].securities[c].id, $scope[s].bank.portfolio[c].id].every(function (v, i, a) {
                //         return (
                //             v === a[0] &&
                //             v !== null
                //         );
                //     }));
            }
        };

        $scope.displayLocalCurrency = true;
        $scope.setCurrency = function(obj, key){
            $scope[key].currency = obj.currency;

            if (key == 'securities' || key == 'dividend' || key == 'bookValue')
            {
                $scope.localCurrencyText = obj.currency.symbol + ' rate';

                var securityCurrencyId  = $scope[key].securities['currency'].id;
                var portfolioCurrencyId = $scope.currentPortfolio ? $scope.currentPortfolio.currency.id : false;

                if (securityCurrencyId && portfolioCurrencyId && securityCurrencyId == portfolioCurrencyId)
                {
                    $scope[key].local_currency_rate = 1;
                    $scope.displayLocalCurrency = false;
                }
                else
                {
                    $scope.displayLocalCurrency = true;
                    $scope[key].local_currency_rate = '';
                }

                if(key !== 'bookValue')
                    $scope.checkCurrency(key);

            }
        };

        // Set Quantity field default value
        $scope.fetchBookValueQuantity = function() {

            if (!$scope.bookValue.date || !$scope.bookValue.securities) {
                return;
            }

            var putData = {};
            putData.portfolio_id = $scope.currentPortfolio.id;
            putData.security_id  = $scope.bookValue.securities.id;
            putData.date         = $scope.bookValue.date;

            PecooniaApiService.getBookValueAmount(putData, function(res){
                $scope.bookValue.quantity = res.item.amount;
            }, function(res){
                toastr.error(res.data.message);
            });
        }

        // Set Quantity field default value
        $scope.fetchSecurityQuantity = function() {

            if (!$scope.securities.securities ||
                !$scope.securities.securities.id ||
                ($scope.securities.action == 'buy')
            )
            {
                $scope.securities.quantity = null;
                return;
            }

            var putData = {};
            putData.portfolio_id = $scope.currentPortfolio.id;
            putData.security_id  = $scope.securities.securities.id;
            putData.date         = new Date(); // Temporarily set as needed by API action

            PecooniaApiService.getBookValueAmount(putData, function(res){

                if (res.item.amount > 0)
                {
                    $scope.securities.action = 'sell';
                    $scope.securities.quantity = res.item.amount;
                }

            }, function(res){
                toastr.error(res.data.message);
            });
        }

        $scope.reset = function(obj){

            $scope[obj] = {};
            $scope[obj + 'Form'].$setPristine();
            $scope.closeModal();
            $state.reload();
        };

        $scope.transaction_reset = function (obj){

            $scope[obj] = {};
            $scope[obj + 'Form'].$setPristine();
            closeTransactionBox();
        };

        $scope.dateOptions = {
            formatYear: 'yy',
            maxDate: new Date(),
            startingDay: 1
        };

        // Disable weekend selection
        function disabled(data) {
            var date = data.date,
                mode = data.mode;
            return mode === 'day' && (date.getDay() === 0 || date.getDay() === 6);
        }

        $scope.open = function() {
            $scope.popup.opened = true;
        };

        $scope.popup = {
            opened: false
        };

        $scope.cashFormSubmitted = false;
        $scope.cashTransaction = function(){


            if (!$scope.cashForm.$valid)
                return;

            var data = {
                bank_id: ($scope.cash.bank) ? $scope.cash.bank.id : null,
                portfolio_id: $scope.currentPortfolio.id,
                amount: $scope.cash.amount,
                date: moment($scope.cash.date).format('YYYY-MM-DD'),
                action: $scope.cash.action,
                text: $scope.cash.text !== undefined ? $scope.cash.text : ''
            };

            PecooniaApiService.createTransaction('cash', data, function(res){
                toastr.success(res.message);

                $scope.cash = {
                    action: 'deposit'
                };

                $scope.setBankList();

                $scope.cashForm.$setPristine();

                $scope.cashFormSubmitted = true;

                closeTransactionBox();
            }, function(res){
                toastr.error(res.data.message);
                closeTransactionBox();
            });
        };
        //date picker for transaction form
        $scope.dateOptions = {
            singleDatePicker: true,
            maxDate: moment(),
            locale: {
                format : CommonFunctionsService.getCurrPortfolioDateFormat()
            }
        };
        $scope.securitiesFormSubmitted = false;
        $scope.securitiesTransaction = function(){

            if (!$scope.securitiesForm.$valid)
                return;

            var s = $scope.securities;

            var data = {
                portfolio_id: $scope.currentPortfolio.id,
                bank_id: (s.bank) ? s.bank.id : null,
                date: moment(s.date).format('YYYY-MM-DD'),
                quantity: s.quantity + '',
                trade_value: s.trade_value,
                local_currency_rate: s.local_currency_rate,
                commision: s.commision + '',
                is_commision: s.is_commision,
                tags: s.tags
            };

            if (s.securities.id)
            {
                data.security_id = s.securities.id;
            }
            else
            {
                data.security = s.securities;
            }

            PecooniaApiService.createTransaction(s.action, data, function(res){
                toastr.success(res.message);
                $scope.securities = {
                    is_commision: 1
                };

                $scope.setBankList();

                $scope.securitiesForm.$setPristine();

                $scope.securitiesFormSubmitted = true;

                angular.forEach(data.tags, function(tag){
                    if ($scope.tags.indexOf(tag) == -1) {
                        $scope.tags.push(tag);
                    }
                });

                closeTransactionBox();

            }, function(res){
                closeTransactionBox();
                toastr.error(res.data.message);
            });
        };

        $scope.dividendFormSubmitted = false;
        $scope.dividendTransaction = function(){


            if (!$scope.dividendForm.$valid)
                return;

            var d = $scope.dividend;

            var data = {
                portfolio_id: $scope.currentPortfolio.id,
                bank_id: (d.bank) ? d.bank.id : null,
                security_id: d.securities.id,
                date: moment(d.date).format('YYYY-MM-DD'),
                dividend: d.dividend,
                is_tax_included: d.is_tax_included,
                tax: d.tax !== undefined ? d.tax : 0,
                local_currency_rate: d.local_currency_rate
            };

            PecooniaApiService.createTransaction('dividend', data, function(res){
                toastr.success(res.message);
                $scope.dividend = {
                    is_tax_included: 0
                };

                $scope.setBankList();

                $scope.dividendForm.$setPristine();

                $scope.dividendFormSubmitted = true;

                closeTransactionBox();
            }, function(res){
                closeTransactionBox();
                toastr.error(res.data.message);
            });
        };

        $scope.bookValueFormSubmitted = false;
        $scope.bookValueTransaction = function(){


            if (!$scope.bookValueForm.$valid)
                return;

            var b = $scope.bookValue;

            var data = {
                portfolio_id: $scope.currentPortfolio.id,
                bank_id: null,
                security_id: b.securities.id,
                date: moment(b.date).format('YYYY-MM-DD'),
                book_value: b.book_value,
                local_currency_rate: b.local_currency_rate
            };

            PecooniaApiService.createTransaction('bookvalue', data, function(res){
                toastr.success(res.message);
                $scope.bookValue = {};
                $scope.bookValueForm.$setPristine();

                $scope.bookValueFormSubmitted = true;
                closeTransactionBox();
            }, function(res){
                toastr.error(res.data.message);
                closeTransactionBox();
            });
        };

        $scope.fetchSecurity = function(security_keyword, $select) {

            if (!security_keyword) {
                return;
            }

            $select.searchInput.attr('disabled', true);

            PecooniaApiService.findSecurity(security_keyword, function(res){
                $select.searchInput.attr('disabled', false);
                $select.searchInput.focus();

                var resItem = res.item;
                for (var resIndex in resItem)
                {
                    var resultVal = resItem[resIndex];

                    var found = $filter('filter')($scope.allSecurities, {symbol: resultVal.symbol}, true);

                    if (!found.length)
                    {
                        $scope.allSecurities.push(resultVal);
                    }
                }

            }, function(res){
                $select.searchInput.attr('disabled', false);
                $select.searchInput.focus();
                toastr.error(res.data.message);
            });
        };

        function closeTransactionBox()
        {
            $scope.closeModal();
            $('.transaction_button').click();
            setTimeout(function () {
                $('body').addClass('modal-open');
            }, 1500);
            $state.reload();
        }

        $scope.shouldDisplayField = function(key, transaction_type, security_currency) {
            if ($scope.transactionFields[key] && $scope.transactionFields[key][transaction_type])
            {
                /**
                In case security currency equal to base currency, local fields values are stored as 0, so skip displaying them. Also skip displaying some other fields specified in configuration.
                **/

                if (
                    (portfolioCurrencySym && security_currency && security_currency == portfolioCurrencySym)
                    && (
                        $scope.transactionFields[key]['display_currency'] ||
                        $scope.transactionFields[key]['hide_if_sec_curr_eq_base_curr']
                    )
                )
                {
                    return false;
                }

                // Check if private portfolio then hide book value fields
                if ($scope.transactionFields[key]['is_bookvalue_field'] &&
                    (0 == $scope.currentPortfolio.is_company)
                    )
                {
                    return false;
                }

                return true;
            }
            else
            {
                return false;
            }
        };

        $scope.displayColumnValue = function(k, v) {
                if ($scope.transactionTypes[v])
                {
                    return $scope.transactionTypes[v]['display_name'];
                }
                else if (isNaN(v) === false)
                {
                    if ($scope.transactionFields[k]['is_currency_rate_field'])
                    {
                        v = formatTrNumber(v, 4);
                    }
                    else if ($scope.transactionFields[k]['is_non_decimal_field'])
                    {
                        v = formatTrNumber(v, 0);
                    }
                    else
                    {
                        v = formatTrNumber(v);
                    }

                    // Add % to applicable fields

                    if ($scope.transactionFields[k]['add_percentage_sign'])
                    {
                        v = v + " %";
                    }

                    return v;
                }
                else
                {
                    if($scope.transactionFields[k]['is_date_field'])
                    {
                        v = CommonFunctionsService.formatTrDate(new Date(v));
                    }

                    return v;
                }
        };

        $scope.displayColumnKey = function(k, v, security_currency) {
            if ($scope.transactionFields[k])
            {
                var displayName = $scope.transactionFields[k]['display_name'];

                if (security_currency && $scope.transactionFields[k]['display_currency'])
                {
                    displayName += ' (' + security_currency + ')';
                }

                return displayName;
            }
            else
            {
                return k;
            }
        };

        // Confirm transaction delete popup

        $scope.confirmTransDelete = function(transaction_id, t_index, event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            var modalInstance = $uibModal.open({
                animation: $scope.animationsEnabled,
                templateUrl: '/vendor/html/modal/ConfirmTransDelete.html',
                controller: 'CommonModalController',
                size: 'md',
                resolve: {
                    data: function () {
                        return {};
                    }
                }
            });

            modalInstance.result.then(function(){
                PecooniaApiService.deleteTransaction(transaction_id, function(res){
                    toastr.success(res.message);
                    initLoadTransactions();

                    PecooniaApiService.getPortfolioStatistics($stateParams.id, function(res){
                        $scope.portfolioStatistics = res.item;
                    });

                }, function(res){
                    toastr.error(res.data.message);
                });
            }, function(dismissed){});
        };

        // Search Transactions

        $scope.searchTransactions = function() {

            $scope.transactionsSearch = $scope.transactionsSearch.trim();
            if ($scope.transactionsSearch && $scope.transactionsSearchArray.indexOf($scope.transactionsSearch) == -1) {
                $scope.transactionsSearchArray.push($scope.transactionsSearch);
                this.transactionsSearch = '';
            }

            PecooniaApiService.getTransactions('portfolio',
                    $scope.currentPortfolioId,
                    1,
                    $scope.transactionsSearchArray.join(), function(res){

                // Filter transactions list
                $scope.total = res.total;
                $scope.page = res.next_page;
                $scope.allPortfolioTransactions = res.item;

            }, function(res){
                toastr.error(res.data.message);
            });

        }

        $scope.removeSearchTerm = function (index) {
            $scope.removeElements($scope.transactionsSearchArray, index);
            $scope.searchTransactions();
        }

        $scope.clearSearchTransiction = function () {
            $scope.transactionsSearchArray = [];
            $scope.searchTransactions();
        }

        $scope.removeElements = function (arr) {
            var what, a = arguments, L = a.length, ax;
            while (L > 1 && arr.length) {
                what = a[--L];
                while ((ax= arr.indexOf(what)) !== -1) {
                    arr.splice(ax, 1);
                }
            }
            return arr;
        }

    }]);

})();
