(function(){
    "use strict";

    angular.module("app.directives").directive('uiSelectRequired', function($timeout) {
        return {
            restrict: 'A',
            require: '^form',
            link: function (scope, el, attrs, formCtrl) {
                // find the text box element, which has the 'name' attribute
                var inputEl   = el[0].querySelector("[name]");
                // convert the native text box element to an angular element
                var inputNgEl = angular.element(inputEl);
                // get the name on the text box
                var inputName = inputNgEl.attr('name');

                // only apply the has-error class after the user leaves the text box
                var blurred = false;
                inputNgEl.bind('blur', function() {
                    blurred = true;
                    el.toggleClass('has-error', formCtrl[inputName].$invalid);
                });

                scope.$watch(function() {
                    return formCtrl[inputName].$invalid
                }, function(invalid) {
                    // we only want to toggle the has-error class after the blur
                    // event or if the control becomes valid
                    if (!blurred && invalid) { return }
                    el.toggleClass('has-error', invalid);
                });

                scope.$on('show-errors-check-validity', function() {
                    el.toggleClass('has-error', formCtrl[inputName].$invalid);
                });

                scope.$on('show-errors-reset', function() {
                    $timeout(function() {
                        el.removeClass('has-error');
                    }, 0, false);
                });
            }
        }})
        .directive('securityTags', function($timeout) {
            return {
                restrict: 'A',
                link: function(scope, el, attrs) {
                    $(el).find('input.ui-select-search').attr('maxlength', 20);
                }
            }
        })
        .directive('fabList', ['$uibModal', '$rootScope', function($uibModal,$rootScope) {
            return {
                restrict: 'A',
                link: function(scope, el, attrs) {
                    $('.fab-buttons.last').on('click', function(event) {

                        event.stopPropagation();

                        if ($('.fab-main-span').hasClass('fab-main-open'))
                        {
                            $(this).find('.tooltip').addClass('hide');
                            $('.fab-main-span').removeClass('fab-main-open').addClass('fab-main-close');
                        }
                        else
                        {
                            $(this).find('.tooltip').removeClass('hide');
                            $('.fab-main-span').removeClass('fab-main-close').addClass('fab-main-open');
                        }
                        //$('.fab-main-span').toggleClass('fab-main-open').toggleClass('fab-main-open');
                        $('div.fab-btn-container').slideToggle();
                    });
                    $('.fab-buttons:not(.last)').on('click', function(event) {
                        
                        event.stopPropagation();
                        if ($("#new_transaction").length) $("#new_transaction").click();
                        // Open transaction popup

                        var fabBtnType = $(this).data('fabbtn');

                        $rootScope.transactionModalInstance = $uibModal.open({
                            templateUrl: 'angular/forms/' + fabBtnType,
                            controller: 'TransactionsController',
                            size: 'xlg',
                            resolve:
                            {
                                currentModalTransactionType: function ()
                                {
                                    return 'panel.transactions.' + fabBtnType;
                                },
                                opened: function () {
                                    setTimeout(function () {
                                        $('body').addClass('modal-open');
                                    },1400);
                                },
                                closed: function () {
                                    $('body').removeClass('modal-open');
                                }
                            }
                        });

                        $rootScope.transactionModalInstance.result.then(
                            function(result) {
                                //console.log('called $rootScope.transactionModalInstance.close()');
                            },
                            function(result) {
                                //console.log('called $rootScope.transactionModalInstance.dismiss()');
                            }
                        );

                    });
                    $(document).on('click', function() {
                        $('.fab-buttons.last').find('.tooltip').addClass('hide');
                        $('.fab-main-span').removeClass('fab-main-open').addClass('fab-main-close');
                        $('div.fab-btn-container').slideUp();
                    });
                }
            }
        }])
        .directive('primaryMenu', function() {
            return {
                restrict: 'A',
                link: function(scope, el, attrs) {
                    // $(el).find('ul > li.current').find('ul').show();

                    // $(el).find('ul > li:not(.current)').hover(function() {
                    //     $(this).find('ul').slideDown('medium');
                    // }, function() {
                    //     if (!$(this).hasClass('current'))
                    //     {
                    //         $(this).find('ul').delay(300).slideUp('slow');
                    //     }
                    // });

                    // $(el).find('ul > li:not(.current)').find('ul').hide();
                }
            }
        });
})();