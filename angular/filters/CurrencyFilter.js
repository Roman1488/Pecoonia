(function() {
    "use strict";

    angular.module("PecooniaApp")
        .filter('currFilter', ['$filter', function($filter) {
            return function(v, comma_separator_val, fraction_size) {

                if (typeof fraction_size == "undefined")
                {
                    fraction_size = 2;
                }

                return $filter('decimalSeparator')(comma_separator_val, v, fraction_size);
            };
        }]);
})();
