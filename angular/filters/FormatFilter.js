(function() {
    "use strict";

    angular.module("PecooniaApp")
        .filter('decimalSeparator', ['$filter', function($filter) {
            return function(comma_separator_val, v, fraction_size) {

                if (typeof fraction_size == "undefined")
                {
                    fraction_size = 2;
                }

                v = $filter('number')(v, fraction_size);

                if (comma_separator_val === 0 && v != null)
                {
                    var splitedNum = v.split('.');

                    if (splitedNum[0] && splitedNum[1])
                    {
                        v = splitedNum[0].replace(new RegExp(",", 'g'), ".");
                        v = [v, splitedNum[1]].join(',');
                    }
                    else
                    {
                        v = v.replace(new RegExp(",", 'g'), ".");
                    }
                }

                return v;
            };
        }])
        .filter('capitalize', function() {
            // if "all" is "true", it capitalizes each word in the input, otherwise only first
            return function(input, all) {
                var reg = (all) ? /([^\W_]+[^\s-]*) */g : /([^\W_]+[^\s-]*)/;
                return (!!input) ? input.replace(reg, function(txt) {
                    return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
                }) : '';
            }
        }).filter('noDecimals', function($filter) {
            // if "ceil" is "true", it ceils the input, otherwise floor.
            return function(input, ceil) {
                return (ceil) ? Math.ceil(input) : Math.floor(input);
            };
        })
})();
