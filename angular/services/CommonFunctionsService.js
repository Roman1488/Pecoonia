(function () {
    'use strict';

    angular.module("app.services").factory('CommonFunctionsService', ['$rootScope', '$filter', function($rootScope, $filter) {

        function getCurrPortfolioDateFormat()
        {
            return ($rootScope.portfolioObj && $rootScope.portfolioObj.date_format) ? ($rootScope.portfolioObj.date_format).toUpperCase() : "YYYY-MM-DD";
        }

        function formatTrDate(dateVal, currentPortfolioObj)
        {
            var portfolioObj = currentPortfolioObj ? currentPortfolioObj : $rootScope.portfolioObj;
            return (portfolioObj) ? (moment(dateVal, 'YYYY-MM-DD').format(portfolioObj.date_format.toUpperCase())) : false;
        };

        function formatTDate(t)
        {
            var portfolioObj = $rootScope.portfolioObj;
            return (portfolioObj) ? (moment(t.date, 'YYYY-MM-DD').format(portfolioObj.date_format.toUpperCase())) : false;
        };

        function formatTrDateTime(dateVal, currentPortfolioObj)
        {
            var portfolioObj = currentPortfolioObj ? currentPortfolioObj : $rootScope.portfolioObj;
            return (portfolioObj) ? (moment(dateVal, 'YYYY-MM-DD HH:mm:ss').format(portfolioObj.date_format.toUpperCase() + ' HH:mm:ss')) : false;
        };

        function formatTrNumber(v, currentPortfolio, fraction_size ) {
            return (currentPortfolio) ? $filter('decimalSeparator')(currentPortfolio.comma_separator, v, fraction_size) : v;
        }

        function formatTrName(t, currentPortfolio){

            var portfolioObj = currentPortfolio ? currentPortfolio : $rootScope.portfolioObj;

            var result = '',
                d   = (t.c_net_dividend_base)? formatTrNumber(t.c_net_dividend_base, portfolioObj) : '',
                tq  = (t.c_trade_quote_base)? formatTrNumber(t.c_trade_quote_base, portfolioObj) : '',
                b   = (t.c_book_value_local)? formatTrNumber(t.c_book_value_local, portfolioObj) : '',
                tv  = (t.trade_value)? formatTrNumber(t.trade_value, portfolioObj) : '',
                bkb = (t.c_book_value_base)? formatTrNumber(t.c_book_value_base, portfolioObj) : '';

                if (t.transaction_type == 'buy')
                {
                    var q = formatTrNumber(t.original_quantity, portfolioObj, 0);
                }
                else
                {
                    var q = formatTrNumber(t.quantity, portfolioObj, 0);
                }

            var securityCurrencyId  = (t.security) ? t.security.currency.id : false;
            var portfolioCurrencyId = portfolioObj ? portfolioObj.currency.id : false;

            if (portfolioCurrencyId && securityCurrencyId && portfolioCurrencyId != securityCurrencyId)
            {
                tq  = formatTrNumber(t.c_trade_quote_local, portfolioObj);
                d   = formatTrNumber(t.c_net_dividend_local, portfolioObj);
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
                            result += ' equal to ' + bkb + ' ' + portfolioObj.currency.symbol;
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

        function formatValue(v, fraction_size, currentPortfolioObj)
        {
            var portfolioObj = currentPortfolioObj ? currentPortfolioObj : $rootScope.portfolioObj;

            if (!v || isNaN(v))
            {
                v = 0;
            }
            return $filter('decimalSeparator')(portfolioObj.comma_separator, v, fraction_size);
        }

        function getDecCharAndPattern(currentPortfolioObj)
        {
            var portfolioObj = currentPortfolioObj ? currentPortfolioObj : $rootScope.portfolioObj;

            var pattObj = {};

            if (portfolioObj && portfolioObj.comma_separator == 0)
            {
                pattObj.twoDecimalPattern  = /^[0-9]+(\,[0-9]{1,2})?$/;
                pattObj.fourDecimalPattern = /^[0-9]+(\,[0-9]{1,4})?$/;
                pattObj.decimalChar        = ',';
            }
            else
            {
                pattObj.twoDecimalPattern  = /^[0-9]+(\.[0-9]{1,2})?$/;
                pattObj.fourDecimalPattern = /^[0-9]+(\.[0-9]{1,4})?$/;
                pattObj.decimalChar        = '.';
            }

            return pattObj;
        }

        return {
            getCurrPortfolioDateFormat: getCurrPortfolioDateFormat,
            formatTrDate: formatTrDate,
            formatTrDateTime: formatTrDateTime,
            formatValue: formatValue,
            formatTDate:formatTDate,
            formatTrName:formatTrName,
            formatTrNumber:formatTrNumber,
            getDecCharAndPattern:getDecCharAndPattern
        }
    }]);

})();