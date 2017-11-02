(function(){
    "use strict";

    var app = angular.module('PecooniaApp');

    app.controller('BankController', ['$scope', '$rootScope', 'PecooniaApiService', '$stateParams', '$state', '$filter', function($scope, $rootScope, PecooniaApiService, $stateParams, $state, $filter) {

        $scope.currentPortfolio = PecooniaApiService.getCurrentPortfolio();

        $scope.page = 1;

        PecooniaApiService.getBank($stateParams.bank_id, function(res){
            $scope.bank  = res.item;

            $scope.loadMore();
        });


        $scope.bank_transactions = [];
        $scope.loadMore = function() {
            //event.preventDefault();

            if ($scope.page == -1) {
                toastr.error('Sorry, there is no more transactions.');
                return;
            }

            PecooniaApiService.getBankTransactions($stateParams.bank_id, $scope.page, function(result){
                $scope.bank_transactions = $scope.bank_transactions.concat(result.item);

                angular.forEach($scope.bank_transactions, function(v, k) {

                    v.tx_date    = $scope.formatTrDate(v);
                    v.tx_text    = $scope.formatText(v);
                    v.tx_amount  = $scope.formatAmount(v);
                    v.tx_balance = $scope.formatBalance(v, k);
                });

                $scope.total             = result.total;
                $scope.page              = result.next_page;
            });
        };

        $scope.showAll = function(){
            //event.preventDefault();
            if ($scope.page == -1) {
                toastr.error('Sorry, there is no more transactions.');
                return;
            }

            PecooniaApiService.getBankTransactions($stateParams.bank_id, -2, function(result){
                $scope.bank_transactions = result.item;
                $scope.total             = result.total;
                $scope.page              = -1;
            });
        };

        PecooniaApiService.getPortfolioStatistics($stateParams.id, function(res){
            $scope.portfolioStatistics = res.item;
        });

        $scope.formatTrDate = function(t) {
            return moment(t.date, 'YYYY-MM-DD').format($scope.currentPortfolio.date_format.toUpperCase());
        };

        $scope.formatText = function(t) {
            var transactionText = '';

            if (t.transaction_type === 'cash_deposit')
            {
                transactionText = 'Cash Deposit';
            }
            else if (t.transaction_type === 'cash_withdraw')
            {
                transactionText = 'Cash withdraw';
            }
            else if (t.transaction_type === 'buy')
            {
                transactionText = 'Buy ' + t.security.name;
            }
            else if (t.transaction_type === 'sell')
            {
                transactionText = 'Sell ' + t.security.name;
            }
            else if (t.transaction_type === 'dividend')
            {
                transactionText = 'Dividend ' + t.security.name;
            }

            return transactionText;
        };

        $scope.prevTransactionAmt = 0;
        $scope.formatAmount = function(t) {

            var transactionAmt = 0;

            var bankCurrencyId  = ($scope.bank) ? $scope.bank.currency.id : false;
            var portfolioCurrencyId = $scope.currentPortfolio ? $scope.currentPortfolio.currency.id : false;

            if (t.transaction_type === 'cash_deposit' || t.transaction_type === 'cash_withdraw')
            {
                transactionAmt = t.trade_value;

                if (t.transaction_type === 'cash_withdraw')
                {
                    transactionAmt = -Math.abs(transactionAmt);
                }
            }
            else if (t.transaction_type === 'buy' || t.transaction_type === 'sell')
            {
                transactionAmt = t.c_trade_value_base;

                if (bankCurrencyId != portfolioCurrencyId)
                {
                    transactionAmt = t.c_trade_value_local;
                }

                if (t.transaction_type === 'buy')
                {
                    transactionAmt = -Math.abs(transactionAmt);
                }
            }
            else if (t.transaction_type === 'dividend')
            {
                transactionAmt = t.c_net_dividend_base;

                if (bankCurrencyId != portfolioCurrencyId)
                {
                    transactionAmt = t.c_net_dividend_local;
                }
            }

            t.actual_transaction_amt = transactionAmt;

            return formatNumber(transactionAmt, 2);
        };

        $scope.prevBalance = 0;
        $scope.formatBalance = function(t, $index) {
            if ($index === 0)
            {
                $scope.prevBalance = $scope.bank.cash_amount;
            }
            else
            {
                $scope.prevBalance = ($scope.prevBalance - $scope.bank_transactions[($index - 1)].actual_transaction_amt);
            }

            return formatNumber($scope.prevBalance, 2);
        };

        function formatNumber(v, fraction_size) {
            if (v)
            {
                fraction_size = fraction_size || 2;

                v = $filter('number')(v, fraction_size);

                if ($scope.currentPortfolio.comma_separator === 0)
                {
                    var splitedNum = v.split('.');

                    v = splitedNum[0].split(",").join(".");
                    v = [v, splitedNum[1]].join(',');
                }

                return v;
            }
        }

        $scope.formatNumber = formatNumber;
    }]);

})();