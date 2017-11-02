(function(){
    "use strict";

    angular.module('app.config').config( function(toastrConfig) {

        angular.extend(toastrConfig, {
            closeButton: true,
            allowHtml: true,
            autoDismiss: false,
            containerId: 'toast-container',
            maxOpened: 0,
            newestOnTop: true,
            positionClass: 'toast-top-right',
            preventDuplicates: false,
            preventOpenDuplicates: false,
            target: 'body'
        });

    });

})();