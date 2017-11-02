(function(){
    "use strict";

    var app = angular.module('PecooniaApp');

    app.controller('CreateController', ['$scope', '$rootScope', 'PecooniaApiService', 'CommonFunctionsService', '$stateParams', '$state', '$filter', '$uibModal', '$q', 'toastr', '$location', function($scope, $rootScope, PecooniaApiService, CommonFunctionsService, $stateParams, $state, $filter, $uibModal, $q, toastr, $location){

        //Table Config
        $scope.currentPageCurrencies = 1;
        $scope.numPerPageForCurrencies = 5;
        $scope.maxSizeCurrencies = 5;

        $scope.portfolioDailyValues = [];

        $scope.currentPortfolio = PecooniaApiService.getCurrentPortfolio();

        // Set default dates for Key Figures table
        $scope.datePickerDate = {startDate: moment().subtract(1 ,'year'), endDate: moment()};

        //$scope.datePickerIncomeAnalysis = {startDate: moment().subtract(1 ,'year'), endDate: moment()};
        $scope.datePickerIncomeAnalysis = {};

        var srcParam = $location.search().src;

        function gatDailyValues () {
            var lastDays = 10;
            PecooniaApiService.getPortfolioDailyValues($scope.currentPortfolio.id, function(res){
                if (res.item.length && res.item[0]) {
                    $scope.marketLabels = [];
                    $scope.marketData = [];

                    for (var i = res.item.length - 1; i >= 0; i--) {

                        var dateString = res.item[i].stats_on_date;
                        var dateObj = new Date(dateString);
                        var momentObj = moment(dateObj);
                        var momentString = momentObj.format($scope.currentPortfolio.date_format.toUpperCase());

                        // add market value data
                        $scope.marketLabels.push(momentString);
                        $scope.marketData.push(parseFloat(res.item[i].portfolio_value) );
                    }

                    // Market Value data;
                    $scope.marketSeries = ['MARKET VALUE'];

                    //$scope.marketColors = ["rgba(70,191,189,1)"];

                    $scope.marketOptions = {
                        scales: {
                          yAxes: [
                            {
                              display: false,
                              id: 'y-axis-1',
                              type: 'linear',
                              position: 'left',
                              ticks: {
                                userCallback: function(value, index, values) {
                                    // Convert the number to a string and splite the string every 3 charaters from the end
                                    value = value.toString();
                                    value = value.split(/(?=(?:...)*$)/);

                                    // Convert the array to a string and format the output
                                    value = value.join('.');
                                    return value;
                                }
                              }
                            }
                          ],
                          xAxes: [{
                            display: false
                          }]
                        },
                        tooltips: {
                          callbacks: {
                            label: function(tooltipItem, data) {
                                var dataset = data.datasets[0];
                                var value = $filter('decimalSeparator')($scope.currentPortfolio.comma_separator, dataset.data[tooltipItem.index]);

                                return 'Portfolio Value:' + value;
                            }
                          }
                        }
                    };

                    // add cach data
                    $scope.cashLabels = ['Cash Share', 'Securities'];
                    $scope.cashData = [parseFloat($scope.portfolioStatistics.cash_share), (parseFloat($scope.portfolioStatistics.equity_share) + parseFloat($scope.portfolioStatistics.fund_share))];

                    $scope.cashDatasetOverride = {
                        backgroundColor: [
                          "rgba(220,220,220,1)",
                          "rgba(151,187,205,1)"
                        ]
                    };


                    $scope.cashOptions = {
                        tooltips: {
                          callbacks: {
                            label: function(tooltipItem, data) {
                              var label = data.labels[tooltipItem.index];
                              var dataset = data.datasets[0];
                              var value = label + ": " + $filter('decimalSeparator')($scope.currentPortfolio.comma_separator, dataset.data[tooltipItem.index]);

                              return value + "%";
                            }
                          }
                        }
                    }

                }
            }, undefined, lastDays);

        }

        function getGuidelineList () {
            PecooniaApiService.getGuidelinesList(
                function(res){
                    // if no cash in portfolio remove Cash alocation
                    if ($scope.portfolioStatistics && $scope.portfolioStatistics.total_marketvalue_base &&  $scope.portfolioStatistics.total_marketvalue_base == 0) {
                        if(res.item.guideline_fields) {
                            delete res.item.guideline_fields.cash_allocation;
                        }
                    }
                    res.item.guideline_fields[0] = {label: 'Choose Guideline'};

                    var guidelineArr = [];

                    for (var item in res.item.guideline_fields) {
                        guidelineArr.push({id: item, name: (res.item.guideline_fields[item].label) ? (res.item.guideline_fields[item].label) : "" });
                    }

                    $scope.guidelineOption = guidelineArr;
                    $scope.guidelineOptionOriginal = res.item.guideline_fields;
                    $scope.guideline = $scope.guidelineOption[0];

                    // Get Guideline Items data
                    getGuidelineItemsList();
                }
            );
        }

        function getGuidelineItemsList () {

            $scope.riscPPCollection = [];
            $scope.weightPPCollection = [];

            PecooniaApiService.getGuidelineItemsList($stateParams.id,
                function(res){

                    if (res.status == 'ok' && res.item) {

                        res.item.forEach(function (item, i, arr) {
                            item.guidelineText = $scope.guidelineOptionOriginal[item.guideline].label;
                            item.guideline_attributes.forEach(function (v) {

                                if (['risc_per_position', 'weight_per_position'].indexOf(item.guideline) >= 0)
                                {
                                    // For security specific guidelines, assign the security property
                                    if (v.attribute_type == "security_symbol")
                                    {
                                        item.security_symbol = v.attribute;

                                        var ppItemObj = {};
                                        ppItemObj.security_symbol = v.attribute;
                                        ppItemObj.warning_msg = v.warning_msg;
                                        ppItemObj.guidelineId = item.id;

                                        if (item.guideline == 'risc_per_position')
                                        {
                                            $scope.riscPPCollection.push(ppItemObj);
                                        }
                                        else if (item.guideline == 'weight_per_position')
                                        {
                                            $scope.weightPPCollection.push(ppItemObj);
                                        }
                                    }

                                    item.current_value = "-";
                                    item.variance = "-";
                                    item.gl_attr = "";
                                }
                                else
                                {
                                    if (item.guideline == "securities_in_portfolio")
                                    {
                                        item.current_value = (v.current_value) ? $filter('noDecimals')(v.current_value) : '-';
                                        item.variance = (v.variance) ? $filter('noDecimals')(v.variance) : '-';
                                    }
                                    else
                                    {
                                        item.current_value = (v.current_value) ? $filter('decimalSeparator')($scope.currentPortfolio.comma_separator, v.current_value) : '-';
                                        item.variance = (v.variance) ? $filter('decimalSeparator')($scope.currentPortfolio.comma_separator, v.variance) : '-';
                                    }

                                    item.gl_attr = v.attribute;
                                    item.warning_msg = v.warning_msg;
                                }
                            });
                        });
                    }

                    $scope.guidelineItems = res.item;
                }
            );

            return true;
        }

        function setDecimalPattAndChar() {
            var pattObj = CommonFunctionsService.getDecCharAndPattern();

            $scope.twoDecimalPattern  = pattObj.twoDecimalPattern;
            $scope.fourDecimalPattern = pattObj.fourDecimalPattern;
            $scope.decimalChar        = pattObj.decimalChar;
        }

        //add GuideLine
        $scope.showGuidelineFields = function()
        {
            if($('.guideline_item').is(':visible')) {
                toastr.error('Please fulfill active guidelines');
            } else {
                $('.guideline_item').show();
            }
        };

        $scope.hideGuidelineFields = function () {
            $scope.min = '';
            $scope.max = '';
            $scope.show_guideline_desc = false;
            $('.guideline_item').val('').hide();
        }

        $scope.addGuideline = function () {
            var error = false;
            $('.guideline_data_item:not("[disabled]")').each(function () {
                if ($(this).val() == '' || $scope.guideline == 0) {
                    toastr.error('Please fulfill all fields');
                    error = true;
                    return false;
                }
            });

            if ($scope.min && !$scope.twoDecimalPattern.test($scope.min))
            {
                toastr.error('Invalid Min value. Only numbers with two decimals or less are allowed (Decimal mark : "'+ $scope.decimalChar +'").');
                error = true;
            }

            if ($scope.max && !$scope.twoDecimalPattern.test($scope.max))
            {
                toastr.error('Invalid Max value. Only numbers with two decimals or less are allowed (Decimal mark : "'+ $scope.decimalChar +'").');
                error = true;
            }

            // Attributes
            var addGlAttr = null;

            if (!$('.guideline_data_item[name="attribute"]').is(':disabled')) {

                if ($scope.guideline_attr == "")
                {
                    toastr.error('Please select Guideline Attribute.');
                    error = true;
                }
                else
                {
                    addGlAttr = [{
                        'attribute_type': $('.guideline_data_item[name="attribute"] option:selected').attr('data-attrib_type'),
                        'attribute': $scope.guideline_attr
                    }];
                }
            }

            if (error)
            {
                return false;
            }

            var guidelineData = {
                'guideline': $scope.guideline.id,
                'attributes': addGlAttr,
                'min': $scope.min,
                'max': $scope.max
            }

            PecooniaApiService.createGuideline($scope.portfolioData.id, guidelineData,
                function(res){
                    $scope.min = '';
                    $scope.max = '';
                    var item = res.item;
                    if (res.status == 'ok') {

                        // Reload Guidelines data
                        getGuidelineItemsList();

                        $scope.show_guideline_desc = false;
                        $('.guideline_item').val('').hide();
                    } else {
                        toastr.error(res.message);
                    }
                },
                function(res){
                    $scope.min = '';
                    $scope.max = '';
                    toastr.error(res.data.message);
                }
            );
        }

        $scope.showGuidelineDesc = function () {
            $scope.show_guideline_desc = true;
        }
        // get guideline attribute depends on guideline

        $scope.getGuidelineAttr = function () {

            var guideline_item = $('.guideline_item[name="attribute"]');
            var guidelineMinInput = $('.guideline_item[name="min"]');
            switch ($scope.guideline.id) {
                case 'currency_allocation':
                    guideline_item.removeAttr('disabled');
                    guidelineMinInput.removeAttr('disabled');
                    PecooniaApiService.getGuidelinesCurrencies($scope.portfolioData.id,
                        function(res){
                            $scope.guidelineAttrOption = res.item;
                            $scope.guidelineAttrOption.unshift({attrName: "", attribute: $scope.guideline.id, subAttrName: "Select"});
                        }
                    );

                    break;

                case 'security_type_allocation':
                    guideline_item.removeAttr('disabled');
                    guidelineMinInput.removeAttr('disabled');
                    PecooniaApiService.getGuidelinesSecurityType($scope.portfolioData.id,
                        function(res){
                            $scope.guidelineAttrOption = res.item;
                            $scope.guidelineAttrOption.unshift({attrName: "", attribute: $scope.guideline.id, subAttrName: "Select"});
                        }
                    );

                    break;

                case 'tag_allocation':
                    guideline_item.removeAttr('disabled');
                    guidelineMinInput.removeAttr('disabled');
                    PecooniaApiService.getGuidelinesTags($scope.portfolioData.id,
                        function(res){
                            $scope.guidelineAttrOption = res.item;
                            $scope.guidelineAttrOption.unshift({attrName: "", attribute: $scope.guideline.id, subAttrName: "Select"});
                        }
                    );

                    break;

                case 'risc_per_position':
                    //guideline_item.removeAttr('disabled','disabled');

                    guideline_item.attr('disabled','disabled');
                    guideline_item.find('option').remove();

                    guidelineMinInput.attr('disabled','disabled');

                    /*
                    $scope.guidelineAttrOption = angular.copy($scope.riscPerPositionAttrs);
                    $scope.guidelineAttrOption.unshift({attrName: "--all--", attribute: $scope.guideline.id, subAttrName: "Select All"});
                    */

                    break;

                default:
                    guidelineMinInput.removeAttr('disabled');
                    guideline_item.attr('disabled','disabled');
                    guideline_item.find('option').remove();
                    break;
            }
        }

        $scope.removeGuideline = function (index) {

            var guidelineItemToRemove = $scope.guidelineItems[index];
            var portfolio_id = $scope.portfolioData.id;

            PecooniaApiService.removeGuideline(portfolio_id, guidelineItemToRemove.id, function(res) {
                // Remove from guidelines table
                $scope.guidelineItems.splice(index, 1);

                // Remove from security position specific guideline collections
                if (['risc_per_position', 'weight_per_position'].indexOf(guidelineItemToRemove.guideline) >= 0)
                {
                    $scope.riscPPCollection = $.grep($scope.riscPPCollection, function(e){
                         return e.guidelineId != guidelineItemToRemove.id;
                    });

                    $scope.weightPPCollection = $.grep($scope.weightPPCollection, function(e){
                         return e.guidelineId != guidelineItemToRemove.id;
                    });
                }
            });
        }

        $scope.localLang = {
            selectAll       : "Select All",
            selectNone      : "Select None",
            reset           : "Reset",
            search          : "Search...",
            nothingSelected : "Choose Parameter"
        };

        $scope.portfolioHoldingsDateOptions = {
            singleDatePicker: true,
            maxDate: moment(),
            locale: {
                format : CommonFunctionsService.getCurrPortfolioDateFormat()
            },eventHandlers:
            {
                'show.daterangepicker': function(ev, picker)
                {
                    //alert("Triggered when the picker is shown");
                },
                'hide.daterangepicker': function(ev, picker)
                {
                    //alert("Triggered when the picker is hidden");
                },
                'showCalendar.daterangepicker': function(ev, picker)
                {
                    //alert("Triggered when the calendar is shown");
                },
                'hideCalendar.daterangepicker': function(ev, picker)
                {
                    //alert("Triggered when the calendar is hidden");
                },
                'apply.daterangepicker': function(ev, picker)
                {
                    $('#portfolioHoldingsDate').val('Holdings on '+ ev.model.format(CommonFunctionsService.getCurrPortfolioDateFormat()));
                    $('#portfolioHoldingsResetBtn').hide(0);
                    $('#portfolioHoldingsSetBtn').show(0);
                },
                'cancel.daterangepicker': function(ev, picker)
                {
                    //alert("Triggered when the cancel button is clicked");
                }
            }
        };


        $scope.incomeAnalysisDatePickerOptions = {

            applyClass: 'btn-primary',
            locale: {
                applyLabel       : "Apply",
                fromLabel        : "From",
                format           : CommonFunctionsService.getCurrPortfolioDateFormat(),
                toLabel          : "To",
                cancelLabel      : 'Cancel',
                customRangeLabel : 'Custom range'
                },
            ranges: {
                'Last 365 days': [moment().subtract(1 ,'year'), moment()],
                'Year To Date': [moment().startOf('year'), moment()]
            },
            eventHandlers:
            {
                'show.daterangepicker': function(ev, picker)
                {
                    //alert("Triggered when the picker is shown");
                },
                'hide.daterangepicker': function(ev, picker)
                {
                    //alert("Triggered when the picker is hidden");
                },
                'showCalendar.daterangepicker': function(ev, picker)
                {
                    //alert("Triggered when the calendar is shown");
                },
                'hideCalendar.daterangepicker': function(ev, picker)
                {
                    //alert("Triggered when the calendar is hidden");
                },
                'apply.daterangepicker': function(ev, picker)
                {
                    var selection = [];
                    var postRequestPayload = {};
                    var postAttr = '';
                    angular.forEach($scope.inputModelIncomeAnalysis, function (v, k) {
                        if (v.group_name)
                        {
                            postAttr = v.group_name.replace(' ', '_');
                        }
                        else if(v.attribute && v.attrName && v.selected)
                        {
                            if (!postRequestPayload[postAttr])
                                postRequestPayload[postAttr] = {};
                            postRequestPayload[postAttr][k] = v.attrName;
                            selection[k] = v;
                        }
                    });

                    if (!$scope.datePickerIncomeAnalysis.startDate) {
                        $scope.datePickerIncomeAnalysis.startDate = moment().subtract(1, 'year');
                        $scope.datePickerIncomeAnalysis.endDate = moment();
                    }

                    postRequestPayload['from'] = $scope.datePickerIncomeAnalysis.startDate.format("YYYY-MM-DD");
                    postRequestPayload['to']  = $scope.datePickerIncomeAnalysis.endDate.format("YYYY-MM-DD");

                    getIncomeAnalysis(postRequestPayload);
                    //alert("Triggered when the apply button is clicked");
                },
                'cancel.daterangepicker': function(ev, picker)
                {
                    //alert("Triggered when the cancel button is clicked");
                },
            }
        }

        $scope.datePickerOptions = {
            applyClass: 'btn-primary',
            locale: {
                applyLabel       : "Apply",
                fromLabel        : "From",
                format           : CommonFunctionsService.getCurrPortfolioDateFormat(),
                toLabel          : "To",
                cancelLabel      : 'Cancel',
                customRangeLabel : 'Custom range'
                },
            ranges: {
                'Last 365 days': [moment().subtract(1 ,'year'), moment()],
                'Year To Date': [moment().startOf('year'), moment()]
            },
            eventHandlers:
            {
                'show.daterangepicker': function(ev, picker)
                {
                    //alert("Triggered when the picker is shown");
                },
                'hide.daterangepicker': function(ev, picker)
                {
                    //alert("Triggered when the picker is hidden");
                },
                'showCalendar.daterangepicker': function(ev, picker)
                {
                    //alert("Triggered when the calendar is shown");
                },
                'hideCalendar.daterangepicker': function(ev, picker)
                {
                    //alert("Triggered when the calendar is hidden");
                },
                'apply.daterangepicker': function(ev, picker)
                {
                    getKeyFigures();
                    //alert("Triggered when the apply button is clicked");
                },
                'cancel.daterangepicker': function(ev, picker)
                {
                    //alert("Triggered when the cancel button is clicked");
                },
            }
        }

        $scope.filter = function(key){
            var begin, end;
            begin = (($scope['currentPage' + key] - 1) * $scope['numPerPageFor' + key]);
            end = begin + $scope['numPerPageFor' + key];
            $scope['filtered' + key] = $scope['filter' + key].slice(begin, end);
        };

        // $scope.$watchGroup(['currentPageCurrencies'], function (newValues, oldValues, scope) {
        //
        //     if (newValues[0] != oldValues[0]) {
        //         $scope.filter('Currencies');
        //     }
        //
        // });

        $scope.animationsEnabled = true;

        $scope.openCreateBankModal = function(){
            console.log($scope.currencies);
            console.log($stateParams.id);
            $('.modal').modal('hide');
            $('.newBank-modal-md').modal('show');
        };

        if ($stateParams.id !== undefined) {
            PecooniaApiService.getCurrency(function(res){
                $scope.currencies = res.item;
            });
            if ($state.current.name == 'panel.show.portfolio_banks')
            {
                $scope.portfolioData = PecooniaApiService.getCurrentPortfolio();

                if (!$scope.portfolioData)
                {
                    PecooniaApiService.getPortfolio($stateParams.id, function(res){
                        $scope.portfolioData = res.item;
                        $rootScope.portfolio = res.item.id;
                        PecooniaApiService.setCurrentPortfolio(res.item);

                        if (typeof srcParam !== undefined && srcParam == 'new_portfolio') {
                            $scope.openCreateBankModal();
                            $location.search('src', null);
                        }
                    });
                }
                else
                {
                    if (typeof srcParam !== undefined && srcParam == 'new_portfolio') {
                        $scope.openCreateBankModal();
                        $location.search('src', null);
                    }
                }
            }
            else
            {
                PecooniaApiService.getPortfolio($stateParams.id, function(res){
                    $scope.portfolioData = res.item;
                    $rootScope.portfolio = res.item.id;

                    PecooniaApiService.setCurrentPortfolio(res.item);

                    $scope.currentPortfolio = PecooniaApiService.getCurrentPortfolio();

                    $rootScope.portfolioObj = res.item;

                    setOptionsAndValues();

                    if (res.item.currency)
                    {
                        // Get Currency Pairs
                        getCurrencyPairs(res.item.currency.symbol, res.item.id);
                    }

                    if ($state.current.name == 'panel.show.portfolio')
                    {
                        // Get Portfolio Holdings data
                        getPortfolioHoldings();

                        // Get Key Figures data
                        getKeyFigures();

                        // Get Watchlist data
                        getSecurityWatchlist();

                        getAnalysisAttributes();

                        // Get Guideline list data
                        getGuidelineList();

                        // Set Decimal Pattern and Char
                        setDecimalPattAndChar();

                        gatDailyValues();

                        $('.key_figures_input').val('');
                    }
                });
            }

            PecooniaApiService.getPortfolioBanks($stateParams.id, function(res){
                $scope.portfolioBanks = res.item;
            });

            PecooniaApiService.getPortfolioStatistics($stateParams.id, function(res){
                $scope.portfolioStatistics = res.item;
            });

        }

        else if($state.current.name == 'panel.portfolio' || $state.current.name  == 'panel.bank' || $state.current.name  == 'panel.create')
        {
            PecooniaApiService.getCurrency(function(res){
                $scope.currencies = res.item;
            });
        }

        $scope.portfolio = {
            is_company: 1,
            date_format: 0,
            comma_separator: 0
        };

        $scope.bank = {
            enable_overdraft: 0,
            portfolio_id: $rootScope.portfolio
        };

        function convertToInt(val){
            return (val && isNaN(val) === false)? parseInt(val) : val;
        }

        $scope.createPortfolio = function(){
            //event.preventDefault();

            if (!$scope.portfolioForm.$valid) return;

            $scope.portfolio.date_format = $rootScope.dateFormat[$scope.portfolio.date_format];

            var portfolioCount = $rootScope.userPortfolios.length;
            if (portfolioCount >= 20){
                toastr.error('For now you can create only 20 portfolios.');
            } else {
                // convert text values to integer
                $scope.portfolio.comma_separator = convertToInt($scope.portfolio.comma_separator);
                $scope.portfolio.is_company = convertToInt($scope.portfolio.is_company);

                PecooniaApiService.createPortfolio($scope.portfolio, function(res){

                    if (portfolioCount === 0) {
                        $rootScope.alerts.push({msg: getPortfolioCreatedMessage(true, res.item.id, $scope.portfolio.name), type: "successmsg"});
                    } else {
                        $rootScope.alerts.push({msg: getPortfolioCreatedMessage(false, res.item.id, $scope.portfolio.name), type: "successmsg"});
                    }

                    $scope.portfolio = res.item;
                    $rootScope.userPortfolios.push($scope.portfolio);

                    if ($scope.portfolioForm) {
                        $scope.portfolioForm.$setPristine();
                    }
                    $rootScope.$emit('portfolio:refresh');
                }, function(res){
                    toastr.error(res.data.message);
                });
            }

            $('.modal').modal('hide');
            $('.newPortfolio-modal-md').modal('hide');
            $state.reload();
        };

        $scope.createPortfolioFromModal = function(){
            $scope.createPortfolio(function(){
                $('.newPortfolio-modal-md').modal('hide');
                $('.modal-backdrop').removeClass('in').addClass('out');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            });
        };

        function getPortfolioCreatedMessage(is_first, portfolio_id, portfolio_name)
        {
            var msg = '';
            if (is_first)
            {
                msg += 'Your first portfolio "' + portfolio_name + '" was successfully created. You can always create more.';
            }
            else
            {
                msg += 'Your portfolio "' + portfolio_name + '" was successfully created.';
            }

            msg += 'It is not required to have a bank account to make transactions; you can add one or more at any time by going to the Bank Accounts page. You can update the bank account on the settings page under the bank account section. Please note that having only one bank account for a portfolio, means that bank account will be chosen by default for all transactions.  If you now wish to create a bank account for the portfolio you have just created, please <a href="/#!/portfolio/' + portfolio_id + '/banks?src=new_portfolio">click here</a>.'

            return msg;
        }

        $scope.createBank = function(){
            //event.preventDefault();

            if (!$scope.bankForm.$valid)
                return;

            $scope.bank.enable_overdraft = ( $scope.bank.enable_overdraft ) ? 1 : 0;
            $scope.bank.portfolio_id  = $rootScope.portfolio;

            PecooniaApiService.getPortfolioBanks($scope.bank.portfolio_id, function(res){
                var bankCount = res.item.length;
                if (bankCount >= 20){
                    toastr.error('For now you can create only 20 bank accounts.');
                } else {
                    PecooniaApiService.createBank($scope.bank, function(res){
                        $state.reload();
                        $('.newBank-modal-md').modal('hide');
                        $('.modal-backdrop').removeClass('in').addClass('out');
                        $('body').removeClass('modal-open');
                        $('.modal-backdrop').remove();
                        toastr.success('Your bank account "' + $scope.bank.name + '" for portfolio id "' + $scope.bank.portfolio_id + '" was successfully created. You can always create more.');
                        $scope.bank = {
                            enable_overdraft: 0,
                            portfolio_id: $rootScope.portfolio
                        };
                        $rootScope.$emit('bank:refresh');
                        $scope.bankForm.$setPristine();
                    }, function(res){
                        toastr.error(res.data.message);
                    });
                }
            });

        };

        $scope.createBankFromModal = function(){
            $scope.createBank(function(){
                $('.newBank-modal-md').modal('hide');
                $('.modal-backdrop').removeClass('in').addClass('out');
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            });
        };

        $scope.addNewSecuritiesModal = function(size){
            /*if (event)
                event.preventDefault();*/

            var deferred = $q.defer();

            if ($scope.portfolioDailyValues[$scope.currentPortfolio.id])
            {
                deferred.resolve($scope.portfolioDailyValues[$scope.currentPortfolio.id]);
            }
            else
            {
                PecooniaApiService.getPortfolioDailyValues($scope.currentPortfolio.id, function(res){
                    $scope.portfolioDailyValues[$scope.currentPortfolio.id] = res.item;
                    deferred.resolve(res.item);
                });
            }

            var modalInstance = $uibModal.open({
                animation: $scope.animationsEnabled,
                templateUrl: 'angular/forms/securities',
                controller: 'TransactionsController',
                size: size,
                resolve: {
                    data: function() {
                        return deferred.promise;
                    }
                }
            });
        };

        $scope.portfolioDailyValuesModal = function(size){
            /*if (event)
                event.preventDefault();*/

            var deferred = $q.defer();

            if ($scope.portfolioDailyValues[$scope.currentPortfolio.id])
            {
                deferred.resolve($scope.portfolioDailyValues[$scope.currentPortfolio.id]);
            }
            else
            {
                PecooniaApiService.getPortfolioDailyValues($scope.currentPortfolio.id, function(res){
                    $scope.portfolioDailyValues[$scope.currentPortfolio.id] = res.item;
                    deferred.resolve(res.item);
                });
            }

            var modalInstance = $uibModal.open({
                animation: $scope.animationsEnabled,
                templateUrl: '/vendor/html/modal/PortfDailyValuesModalTemplate.html',
                controller: 'CommonModalController',
                size: size,
                resolve: {
                    data: function() {
                        return deferred.promise;
                    }
                }
            });
        };

        // display the holding transactions table

        $scope.showHoldingTransactions = [];
        $scope.displayRow = [];
        $scope.transactions = [];
        $scope.dateFilterNotApplied = true;

        $scope.formatTDate = function(i){
            return CommonFunctionsService.formatTDate(i);
        };

        $scope.formatTName = function(i){
            return CommonFunctionsService.formatTrName(i);
        };

        initLoadTransactions();
        function initLoadTransactions()
        {
            $scope.page  = 1;
            $scope.total = 0;
            PecooniaApiService.getTransactions('portfolio', $stateParams.id, 1, '',
                function(res){
                    $scope.allPortfolioTransactions = res.item;
                }
            );
        };

        $scope.displayHoldingTransactions = function(index, security_id, transaction_count, event) {

            if(!$scope.dateFilterNotApplied)
            {
                return;
            }

            if (event) {
                event.stopPropagation();
            }

            if (transaction_count <= 1)
            {
                return;
            }

            if (!$scope.transactions[security_id])
            {
                PecooniaApiService.getHoldingTransactions($scope.portfolioData.id, security_id, function(res){
                    $scope.transactions[security_id] = res.item;
                    toggleHoldingsTransactions(index);
                });
            }
            else
            {
                toggleHoldingsTransactions(index);
            }
        };

        $scope.displayHoldingTransactions = function(index, security_id, transaction_count, event) {

            if(!$scope.dateFilterNotApplied)
            {
                return;
            }

            if (event) {
                event.stopPropagation();
            }

            if (transaction_count <= 1)
            {
                return;
            }

            if (!$scope.transactions[security_id])
            {
                PecooniaApiService.getHoldingTransactions($scope.portfolioData.id, security_id, function(res){
                    $scope.transactions[security_id] = res.item;
                    toggleHoldingsTransactions(index);
                });
            }
            else
            {
                toggleHoldingsTransactions(index);
            }
        };

        // Watchlist functions

        $scope.securitySymbols = [];
        $scope.watchlist = [];
        $scope.currentSecuritiesWatchlist = [];

        function getSecurityWatchlist()
        {
            var portfolio_id = $scope.portfolioData.id;

            //fetch securities to show in select box
            if (!$scope.securitySymbols[portfolio_id])
            {
                PecooniaApiService.getUnwatchedSecurities(portfolio_id, function(res){
                    $scope.securitySymbols[portfolio_id] = res.item;

                    if (res.item.length > 0 && !$scope.lastUpdatedSecurityData) {
                        $scope.lastUpdatedSecurityData = $scope.formatTrDateTime(res.item[0].data.updated_at);
                    }
                });
            }

            //fetch object to display in watchlist on portfolio page
            if (!$scope.currentSecuritiesWatchlist.length > 0)
            {
                PecooniaApiService.getSecurityWatchlist(portfolio_id, function(res) {

                    // Apply formatting
                    $scope.currentSecuritiesWatchlist = applyFormatting(res.item);

                    if (res.item.length > 0 && !$scope.lastUpdatedSecurityData) {
                        $scope.lastUpdatedSecurityData = $scope.formatTrDateTime(res.item[0].data.updated_at);
                    }
                });
            }
        };

        function addNewSecurities(securitiesToAdd)
        {
            PecooniaApiService.addNewSecurities({securitiesToAdd}, function(res) {
                var newSecuritiesAddedToDB = res.item;

                angular.forEach($scope.watchlist.securities, function(v, k){
                    if(!v.id)
                        $scope.watchlist.securities.splice(k, 1);
                });

                //Add New Added Securities To Selector Control
                angular.forEach(newSecuritiesAddedToDB, function(v, k){
                    $scope.watchlist.securities.push(v);
                });
                //end

                addToWatchlist();
            });
        }

        //add New Securities To WatchList
        $scope.addSecuritiesToWatchlist = function()
        {
            // if (event) {
            //     event.stopPropagation();
            //     event.preventDefault();
            // }

            var newSecurities = [];

            //Add New Securities To DB
            angular.forEach($scope.watchlist.securities, function(v, k) {
                if(!v.id)
                    newSecurities.push(v);
            });

            if (newSecurities.length == 0) {
                addToWatchlist();
            } else {
                addNewSecurities(newSecurities);
            }
        };

        function addToWatchlist()
        {
            var addToWatchlist             = {};
            addToWatchlist['security_ids'] = [];
            var portfolio_id               = $scope.portfolioData.id;
            var securitiesToRemove        = [];

            addToWatchlist['portfolio_id'] = portfolio_id;

            angular.forEach($scope.watchlist.securities, function(val, key){
                if (val.id && val.id != null)
                {
                    addToWatchlist['security_ids'].push(val.id);
                    securitiesToRemove.push(val.symbol);
                }
            });

            PecooniaApiService.addSecuritiesToWatchlist({addToWatchlist}, function(res) {

                // Add to watchlist table
                angular.forEach(res.item, function(val, key) {
                    val = applyFormatting(val);
                    $scope.currentSecuritiesWatchlist.push(val);
                });

                //Remove from watchlist available security list
                angular.forEach($scope.securitySymbols[portfolio_id], function(val, key) {
                    if (securitiesToRemove.indexOf(val.symbol) > -1) {
                        delete $scope.securitySymbols[portfolio_id][key];
                    }
                });

                // Reset the select box
                $scope.watchlist.securities = [];
            });
        }

        $scope.removeSecFromWatchlist = function(index) {

            /*
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            */

            var securityToRemove = $scope.currentSecuritiesWatchlist[index];
            var portfolio_id = $scope.portfolioData.id;

            PecooniaApiService.removeSecurityFromWatchlist(portfolio_id, securityToRemove.id, function(res) {
                // Add to watchlist available security list
                $scope.securitySymbols[portfolio_id].push(securityToRemove);
                // Remove from watchlist table
                $scope.currentSecuritiesWatchlist.splice(index, 1);
            });
        };

        // Fetch and show securites from yahoo
        $scope.fetchSecurity = function(security_keyword, $select, portfolio_id) {
            var portfolio_id =  portfolio_id || $rootScope.portfolioObj;
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

                    var found = $filter('filter')($scope.securitySymbols[portfolio_id], {symbol: resultVal.symbol}, true);

                    if (!found.length)
                    {
                        $scope.securitySymbols[portfolio_id].push(resultVal);
                    }
                }

            }, function(res){
                $select.searchInput.attr('disabled', false);
                $select.searchInput.focus();
                toastr.error(res.data.message);
            });
        };

        // end

        function toggleHoldingsTransactions(index)
        {
            $scope.showHoldingTransactions[index] = !$scope.showHoldingTransactions[index];

            if ($scope.showHoldingTransactions[index])
            {
                $scope.displayRow[index] = false;
            }
        }

        /* Different Options and Values to set once the current Portfolio object is available */

        function setOptionsAndValues()
        {
            $scope.datePickerOptions.locale.format = CommonFunctionsService.getCurrPortfolioDateFormat();

            return true;
        }

        function getCurrencyPairs(currencySymbol, portfolioId)
        {
            PecooniaApiService.getCurrencyPairs(currencySymbol, portfolioId, function(res){

                $scope.filterCurrencies = res.item.currencies;
                $scope.filterCurrencyNames = res.item.currencyNames;
                $scope.filterCurrencyNamesAlias = res.item.currencyAlias;
                $scope.lastUpdatedCurrencies = $scope.formatTrDateTime(res.item.currencies[0].updated_at);
                $scope.filter('Currencies');
            }, true);

            return true;
        }

        function generateSumOfColumn(dataSet, colName)
        {
            var sum = 0;

            angular.forEach(dataSet, function(val, key) {
                sum += parseFloat(dataSet[key][colName]);
            });

            return sum;
        }

        var zeroFractionSizeProps = ['total_inventory', 'volume', 'average_daily_volume'];
        var skipFormatting        = ['transaction_count', 'id'];

        function applyFormatting(dataObject)
        {
            if(angular.isObject(dataObject) || angular.isArray(dataObject))
            {
                angular.forEach(dataObject, function(value, key) {
                    if(angular.isObject(value) || angular.isArray(value))
                    {
                        dataObject[key] = applyFormatting(value);
                    }
                    else
                    {
                        dataObject[key] = applyValueFormat(key, value);
                    }
                });
            }

            return dataObject;
        }

        function applyValueFormat(key, value)
        {
            if (value && (isNaN(value) === false) && (skipFormatting.indexOf(key) == -1))
            {
                if(zeroFractionSizeProps.indexOf(key) > -1)
                {
                    return $filter('decimalSeparator')($scope.currentPortfolio.comma_separator, value, 0);
                }
                else
                {
                    return $filter('decimalSeparator')($scope.currentPortfolio.comma_separator, value);
                }
            }

            return value;
        }

        function getPortfolioHoldings()
        {
            PecooniaApiService.getPortfolioHoldings($stateParams.id, function(res){
                handleHoldingsResponse(res.item);

                // Set Risc Per Position Guideline Attributes
                // setRiscPerPositionGAttrs();
            });

            return true;
        }

        function setRiscPerPositionGAttrs()
        {
            $scope.riscPerPositionAttrs = [];

            $scope.portfolioHoldings.forEach(function(v){
                $scope.riscPerPositionAttrs.push({
                    attrName    : v.security.symbol,
                    attribute   : "risc_per_position",
                    subAttrName : v.security.name
                });
            });
        }

        function getKeyFigures()
        {
            var fromToDateSet = {
                from : moment().subtract(1 ,'year'),
                to  : moment()
            };

            PecooniaApiService.getKeyFigures($scope.portfolioData.id, fromToDateSet, function(res) {
                $scope.keyFigures = res.item;
            });

            return true;
        }

        $scope.analysisAttributes = [];

        $scope.inputModel = [];
        $scope.outputModel = [];

        $scope.inputModelIncomeAnalysis  = [];
        $scope.outputModelIncomeAnalysis = [];

        function generateToMultiSelectGroupModel(input_obj){

            var grp_names = [],
                obj = angular.copy(input_obj);
            angular.forEach(obj, function (v, k) {
                if ('attribute' in v) grp_names.push(v['attribute']);
                v['disabled'] = false;
                v['selected'] = false;
            });
            grp_names = grp_names.filter(function(item, pos) {
                return grp_names.indexOf(item) == pos;
            });

            grp_names.forEach(function(val, item){
              var index = (obj.map(function(o) { return o.attribute; }).indexOf(val));
              obj.splice(index, 0, {"group_name": val, "is_group": true});
            });
            grp_names.forEach(function(val, item){
              var index = (obj.map(function(o) { return o.group_name; }).indexOf(val));
              if (index>0) obj.splice(index, 0, {"is_group": false});
            });
            obj.push({"is_group": false});

            return obj;

        }

        function getAnalysisAttributes()
        {
            var portfolio_id = $scope.portfolioData.id;

            PecooniaApiService.getHoldingsAnalysisAttributes(portfolio_id, function(res){

                $scope.analysisAttributes = res.item;

                if (res.item.length <= 0)
                {
                    return;
                }

                setHoldingsAnalysisAttributes();
                setIncomeAnalysisAttributes();
            });

            return true;
        }

        function setHoldingsAnalysisAttributes()
        {
            $scope.inputModel = generateToMultiSelectGroupModel($scope.analysisAttributes);
            $scope.outputModel = $scope.inputModel;

            //Assign default all sub-attributes of "Security Type" attribute from response, and disable sub-attributes of other attributes

            defaultAttrSelection("Security Types");
        }

        function setIncomeAnalysisAttributes()
        {
            $scope.inputModelIncomeAnalysis = generateToMultiSelectGroupModel($scope.analysisAttributes);
            $scope.outputModelIncomeAnalysis = $scope.inputModelIncomeAnalysis;

            //Assign default all sub-attributes of "Security Type" attribute from response, and disable sub-attributes of other attributes

            defaultAttrSelectionIncomeAnalysis("Security Types");
        }

        function defaultAttrSelection(defaultAttribute)
        {
            angular.forEach($scope.outputModel, function(v, k) {
                if ('attribute' in v && v.attribute == defaultAttribute) {
                    v.selected = true;
                };
            });

            disableOtherSubAttributes(defaultAttribute);
            fetchGainLossData(defaultAttribute);
        }

        function defaultAttrSelectionIncomeAnalysis(defaultAttribute)
        {
            angular.forEach($scope.outputModelIncomeAnalysis, function(v, k) {
                if ('attribute' in v && v.attribute == defaultAttribute) {
                    v.selected = true;
                };
            });

            disableOtherSubAttrIncomeAnalysis(defaultAttribute);
            fetchIncomeAnalysisData(defaultAttribute);
        }

        $scope.clearMultiSelection = function(){
            $scope.gainLossSet = null;
            enableAllSubAttr();
        }

        $scope.clearMultiSelectionIncomeAnalysis = function(){
            $scope.profitLossSet = null;
            enableAllIncAnalysisSubAttr();
        }

        $scope.generateInputHoldingsAnalysis = function( target )
        {
            if (! $scope.outputModel.length ) return $scope.clearMultiSelection();

            var attr = ('is_group' in target) ? target.group_name : target.attribute;

            disableOtherSubAttributes(attr);
            fetchGainLossData(attr);
        };

        function fetchGainLossData(attr)
        {
            var data            = {},
                filterAttr      = attr.replace(' ', '_');

            data[filterAttr]    = {};

            angular.forEach($scope.outputModel, function(v, k) {
                if ('attrName' in v && v.selected) data[filterAttr][k] = v.attrName;
            });

            getHoldingsAnalysis(data);
        }

        $scope.generateInputIncomeAnalysis = function( target )
        {
            if (! $scope.outputModelIncomeAnalysis.length ) return $scope.clearMultiSelectionIncomeAnalysis();

            var attr = ('is_group' in target) ? target.group_name : target.attribute;

            disableOtherSubAttrIncomeAnalysis(attr);
            fetchIncomeAnalysisData(attr);
        };

        function fetchIncomeAnalysisData(attr)
        {
            var data            = {},
                filterAttr      = attr.replace(' ', '_');

            data[filterAttr]    = {};

            angular.forEach($scope.outputModelIncomeAnalysis, function(v, k) {
                if ('attrName' in v && v.selected) data[filterAttr][k] = v.attrName;
            });

            if (!$scope.datePickerIncomeAnalysis.startDate) {
                $scope.datePickerIncomeAnalysis.startDate = moment().subtract(1, 'year');
                $scope.datePickerIncomeAnalysis.endDate = moment();
            }

            data['from'] = $scope.datePickerIncomeAnalysis.startDate.format("YYYY-MM-DD");
            data['to']  = $scope.datePickerIncomeAnalysis.endDate.format("YYYY-MM-DD");

            getIncomeAnalysis(data);
        }

        function disableOtherSubAttributes(selectedAttribute)
        {
            angular.forEach($scope.inputModel, function(v, k) {
                if (v.attribute == selectedAttribute || v.group_name == selectedAttribute)
                {
                    v.disabled = false;
                }
                else
                {
                    v.disabled = true;
                }
            });
        }


        function disableOtherSubAttrIncomeAnalysis(selectedAttribute)
        {
            angular.forEach($scope.inputModelIncomeAnalysis, function(v, k) {
                if (v.attribute == selectedAttribute || v.group_name == selectedAttribute)
                {
                    v.disabled = false;
                }
                else
                {
                    v.disabled = true;
                }
            });
        }

        function enableAllSubAttr()
        {
            angular.forEach($scope.inputModel, function(v, k) {
                v.disabled = false;
            });
        }

        function enableAllIncAnalysisSubAttr()
        {
            angular.forEach($scope.inputModelIncomeAnalysis, function(v, k) {
                v.disabled = false;
            });
        }

        function getHoldingsAnalysis(data)
        {
            var portfolio_id = $scope.portfolioData.id;
            if(!data)
                data = {};

            PecooniaApiService.getHoldingsAnalysis(portfolio_id, data, function(res){

                var gainLossSet = res.item;

                // Apply formatting
                gainLossSet = applyFormatting(gainLossSet);
                $scope.gainLossSet = gainLossSet;
            });
            return true;
        }


        function getIncomeAnalysis(data)
        {
            var portfolio_id = $scope.portfolioData.id;
            if(!data)
                data = {};

            PecooniaApiService.getIncomeAnalysis(portfolio_id, data, function(res){

                var profitLossSet = res.item;

                // Apply formatting
                profitLossSet = applyFormatting(profitLossSet);
                $scope.profitLossSet = profitLossSet;
            });
            return true;
        }


        $scope.portfolioHoldingsHistory = function(setCurentDate)
        {
            var portfolioHoldingsDate = $scope.portfolioHoldingsDate;


            if (setCurentDate) {
                portfolioHoldingsDate = moment();
                $('#portfolioHoldingsDate').val('').removeClass('active');
                $('#portfolioHoldingsResetBtn').hide(0);
                $('#portfolioHoldingsSetBtn').show(0);
            } else if (!setCurentDate && portfolioHoldingsDate) {
                $('#portfolioHoldingsResetBtn').show(0);
                $('#portfolioHoldingsSetBtn').hide(0);
                $('#portfolioHoldingsDate').addClass('active')
            }

            if (!portfolioHoldingsDate)
            {
                alert("please choose date first to continue.");
            }
            else
            {
                var requestSet = {
                    date : portfolioHoldingsDate.format("YYYY-MM-DD"),
                };

                if (requestSet.date == moment().format("YYYY-MM-DD"))
                {
                    $scope.dateFilterNotApplied = true;
                    // Get Portfolio Holdings data
                    getPortfolioHoldings();
                }
                else
                {
                    PecooniaApiService.portfolioHoldingsHistory($stateParams.id, requestSet, function(res){

                        handleHoldingsResponse(res.item);

                        $scope.dateFilterNotApplied = false;
                    });
                }
                return true;
            }
        };

        function handleHoldingsResponse(holdings) {

            holdings.total_market_value_in_base = generateSumOfColumn(holdings, 'market_value_in_base');
            holdings.total_gain_loss_in_base    = generateSumOfColumn(holdings, 'gain_loss_in_base');
            holdings.total_weight               = Math.round(generateSumOfColumn(holdings, 'weight'));

            // Apply formatting
            holdings = applyFormatting(holdings);

            $scope.portfolioHoldings = holdings;
        }

        $scope.displayHoldingData = function(index) {

            $scope.displayRow[index] =! $scope.displayRow[index];

            if($scope.displayRow[index])
            {
                $scope.showHoldingTransactions[index] = false;
            }

        };

        function capitalize(input) {
          return input.replace(/^./, function (match) {
            return match.toUpperCase();
          });
        };

        $scope.closeModal = function (name) {
            var target_modal = '.new' + capitalize(name) + '-modal-md';
            if (target_modal) $(target_modal).modal('hide');
        };

        $scope.reset = function(obj){
            $scope[obj] = {};
            $scope[obj + 'Form'].$setPristine();
            $scope.closeModal(obj);
        };

        $scope.formatTrDate = function(date) {
            return CommonFunctionsService.formatTrDate(date);
        };

        $scope.formatTrDateTime = function(date_time) {
            return CommonFunctionsService.formatTrDateTime(date_time);
        };

        $scope.deleteBank = function(size, data, index) {

            var modalInstance = $uibModal.open({
                animation: $scope.animationsEnabled,
                templateUrl: '/vendor/html/modal/DeleteBankModalTemplate.html',
                controller: 'EditModalController',
                size: size,
                resolve: {
                    data: function () {
                        return {
                            bank: data,
                        };
                    }
                }
            });

            modalInstance.result.then(function(){
                PecooniaApiService.deleteBank(data.id, function(res){
                    $rootScope.$emit('bank:refresh', {refresh : true});
                    $state.reload();
                    toastr.success('Bank deleted successfully.');
                });
            }, function(res){

            });
        };

        $scope.deReActivateBank = function(size, data) {

            var modalInstance = $uibModal.open({
                animation: $scope.animationsEnabled,
                templateUrl: '/vendor/html/modal/DeReActivateBankModalTemplate.html',
                controller: 'EditModalController',
                size: size,
                resolve: {
                    data: function () {
                        return {
                            bank: data,
                        };
                    }
                }
            });

            modalInstance.result.then(function() {
                var bankStatus = (data.status === 1) ? 0 : 1;

                PecooniaApiService.updateBank(data.id, {status: bankStatus, name: data.name}, function(res){
                    var toastMsg = '';

                    data.status = bankStatus;

                    if (data.status) {
                        toastMsg = 'You have reactivated Bank Account ' + data.name;
                    } else {
                        toastMsg = 'You have deactivated Bank Account ' + data.name;
                    }

                    $state.reload();
                    toastr.success(toastMsg);
                });
            }, function(res){

            });
        };

        $scope.formatValue = function(v, fraction_size) {
            return CommonFunctionsService.formatValue(v, fraction_size);
        };

    }]);

})();
