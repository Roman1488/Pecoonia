(function(){
    "use strict";

    angular.module("app.directives").
    directive('boostrapPopover', function() {
        return {
            restrict: 'A',
            link: function(scope, elem) {
                $(elem).popover();
            }
        };
    });
})();