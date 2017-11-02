<div class="container-fluid portfolio_page">

    <div class="container-fluid portfolio_cards card card-p">
        <div class="row">
            <div class="stat col-sm-4">
                <div class="fancy-title title-dotted-border">
                    <div class="row chart-wrap">
                        <div class="col-lg-6 col-sm-12 col-xs-6">
                            <p class="stat-header">  PORTFOLIO VALUE </p>
                            <h1> @{{portfolioStatistics.portfolio_value | currFilter:currentPortfolio.comma_separator }}</h1>
                            <h4 ng-class="portfolioStatistics.marketvalue_change > 0 ?
                                'v-up' : (portfolioStatistics.marketvalue_change < 0 ? 'v-down' : '')">
                                @{{portfolioStatistics.marketvalue_change | currFilter:currentPortfolio.comma_separator }}
                                ( @{{portfolioStatistics.marketvalue_percent_change | currFilter:currentPortfolio.comma_separator}} % )
                            </h4>
                        </div>
                        <div class="col-lg-6 col-sm-12 col-xs-6">
                            <canvas id="line" class="chart chart-line" chart-data="marketData"
                                chart-labels="marketLabels" chart-colors="marketColors" chart-series="marketSeries" chart-options="marketOptions"
                                chart-dataset-override="marketDatasetOverride" >
                            </canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="stat cash-stat col-sm-4">
                <span class="devider"></span>
                <div class="fancy-title title-dotted-border">
                    <div class="row chart-wrap">
                        <div class="col-lg-5 col-sm-12 col-xs-6">
                            <p class="stat-header">  CASH </p>
                            <h1> @{{portfolioStatistics.total_all_bank_balance | currFilter:currentPortfolio.comma_separator }} </h1>
                            <h4 ng-class="portfolioStatistics.total_all_bank_balance > 0 ?
                                'v-up' : (portfolioStatistics.total_all_bank_balance < 0 ? 'v-down' : '')">
                                Share: @{{portfolioStatistics.cash_share | currFilter:currentPortfolio.comma_separator }} %
                            </h4>
                        </div>
                        <div class="col-lg-7 col-sm-12 col-xs-6">
                            <canvas id="pie" class="chart chart-doughnut" chart-data="cashData" chart-options="cashOptions" chart-dataset-override="cashDatasetOverride" chart-labels="cashLabels" >
                            </canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
                <span class="devider hide-mobile"></span>
                {{--<div class="fancy-title title-dotted-border">
                    <p class="stat-header">  TODAY'S CHANGE  </p>
                    <h1> @{{portfolioStatistics.marketvalue_change | currFilter:currentPortfolio.comma_separator }} </h1>
                </div>--}}
            </div>
            <div class="col-sm-3">
                {{--<span class="devider hide-mobile"></span>--}}
                {{--<div class="fancy-title title-dotted-border">
                    <p> TOTAL STATEMENT </p>
                    <h1> @{{portfolioStatistics.total_all_bank_balance | currFilter:currentPortfolio.comma_separator}} </h1>
                </div>   --}}
            </div>
        </div>
    </div>

    <!-- PORTFOLIO SECURITY BLOCK -->
    <div class="container-fluid portfolio_cards card card-p">
        <div class="row">
            <div class="fancy-title title-dotted-border">
                <div>
                    <span class="holdings-h3"><h3 class="title_bold">@{{ portfolioData.name}} Holdings</h3></span>
                </div>
                <div class="tag"></div>
                <span>
                    <span class="holdings-label-h5 pull-right">
                        <input date-range-picker="" name="portfolioHoldingsDate" id="portfolioHoldingsDate" class="form-control date-picker ng-pristine ng-valid ng-isolate-scope ng-touched" type="text" ng-model="portfolioHoldingsDate" options="portfolioHoldingsDateOptions" required="" placeholder="View Historic Holdings">
                        <button type="button" id="portfolioHoldingsSetBtn" ng-click="portfolioHoldingsHistory(false)" class="holdinds-action-btn btn btn-sm btn-primary align-top">GO</button>
                        <button type="button" id="portfolioHoldingsResetBtn" ng-click="portfolioHoldingsHistory(true)" class="holdinds-action-btn btn btn-sm btn-primary align-top">Reset</button>
                    </span>
                </span>
            </div>
            <div class="row">
                <div class="col-xs-12 table table-responsive">
                    <table class="table table-hover portfolio-holdings__table table-striped table_no_borders tr_rows_stripe">
                        <thead>
                            <tr>
                                <th></th>
                                <th>SECURITY</th>
                                <th>QNT</th>
                                <th>PURCHASE V.</th>
                                <th>APP</th>
                                <th>PRICE</th>
                                <th>MARKET V.</th>
                                <th>GAIN/LOSS</th>
                                <th>RETURN %</th>
                                <th>MARKET V.in @{{ portfolioData.currency.symbol }}</th>
                                <th>GAIN/LOSS in @{{ portfolioData.currency.symbol }}</th>
                                <th>WEIGHT %</th>
                                <!-- <th>SYMBOL</th>
                                <th>SECURITY TYPE</th> -->
                                <th colspan="3"></th>
                            </tr>
                        </thead>
                        <tbody ng-repeat="p in portfolioHoldings | orderBy:'security.name'" class="card holdings_pointer">
                            <tr>
                                <td ng-click="displayHoldingTransactions($index, p.security.id, p.transaction_count, $event)">
                                    @{{ (p.transaction_count > 1) ? (showHoldingTransactions[$index] ? '-' : '+') : '' }}
                                </td>
                                <td>@{{p.security.name}} <img ng-repeat="item in riscPPCollection" ng-if="item.security_symbol == p.security.symbol && item.warning_msg" class="guideline-alert" src="/images/icons/alert-icon.png" boostrap-popover data-content="@{{item.warning_msg}}" data-trigger="hover" />
                                <img ng-repeat="item in weightPPCollection" ng-if="item.security_symbol == p.security.symbol && item.warning_msg" class="guideline-alert" src="/images/icons/alert-icon.png" boostrap-popover data-content="@{{item.warning_msg}}" data-trigger="hover" /></td>
                                <td>@{{p.total_inventory}}</td>
                                <td>@{{p.purchase_value}}</td>
                                <td>@{{p.app}}</td>
                                <td>@{{p.price}}</td>
                                <td>@{{p.market_value}}</td>
                                <td>@{{p.gain_loss}}</td>
                                <td>@{{p.return}} %</td>
                                <td>@{{p.market_value_in_base}}</td>
                                <td>@{{p.gain_loss_in_base}}</td>
                                <td>@{{p.weight}} %</td>
                               <!--  <td>@{{p.security.currency.symbol}}</td>
                                <td>@{{p.security.security_type}}</td> -->
                                <td class="options_tb">
                                    <div class="row opt_menu_tb">
                                        <div ng-show="(p.transaction_count > 1)" ng-click="displayHoldingTransactions($index, p.security.id, p.transaction_count, $event)" class="col-xs-12 o_text-info other_opt" >
                                            TRADES
                                        </div>
                                        <div class="col-xs-12 det"  ng-click="displayHoldingData($index)">
                                            TRADING INFO
                                        </div>
                                        <!-- <div class="col-xs-12 other_opt o_text-grey">
                                            SELL
                                        </div>
                                        <div class="col-xs-12 other_opt o_text-grey">
                                            BUY
                                        </div> -->
                                    </div>
                                </td>
                                <td class="opt_trigger_tb">
                                    <img src="images/mode-circular-button.png">
                                </td>
                                <td></td>
                            </tr>

                            <tr ng-show="displayRow[$index] && dateFilterNotApplied">
                                <td colspan="18" class="t-table-sub">
                                    <table class="table table-hover table-condensed table-striped port-trans-table small-first-column-table">
                                        <thead class="levelTwo">
                                        <tr>
                                            <th>Field</th>
                                            <th>Value</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Security</td>
                                                <td>@{{p.security.name}}</td>
                                            </tr>
                                            <tr>
                                                <td>Symbol</td>
                                                <td>@{{p.security.symbol}}</td>
                                            </tr>
                                            <tr>
                                                <td>Exchange</td>
                                                <td>@{{p.security.exchange}}</td>
                                            </tr>
                                            <tr>
                                                <td>Security Type</td>
                                                <td>@{{p.security.security_type}}</td>
                                            </tr>
                                            <!--<tr style="display: none;">-->
                                                <!-- <td>Last Updated At</td> -->
                                                    <!--<td ng-bind-html="formatTrDate(p.security.data.updated_at)"></td> -->
                                            <!--</tr> -->
                                            <tr>
                                                <td>Volume</td>
                                                <td>@{{p.security.data.volume}}</td>
                                            </tr>
                                            <tr>
                                                <td>Average Daily Volume</td>
                                                <td>@{{p.security.data.average_daily_volume}}</td>
                                            </tr>
                                            <tr>
                                                <td>Previous Close</td>
                                                <td>@{{p.security.data.previous_close}}</td>
                                            </tr>
                                            <tr>
                                                <td>Open</td>
                                                <td>@{{p.security.data.open}}</td>
                                            </tr>
                                            <tr>
                                                <td>Change</td>
                                                <td>@{{p.security.data.change}}</td>
                                            </tr>
                                            <tr>
                                                <td>% Change</td>
                                                <td>@{{p.security.data.percentage_change}} %</td>
                                            </tr>
                                            <tr>
                                                <td>Last Trade Price Only</td>
                                                <td>@{{p.security.data.last_trade_price_only}}</td>
                                            </tr>
                                            <tr>
                                                <td>Last Trade Date Time</td>
                                                <td ng-bind-html="formatTrDate(p.security.data.last_trade_date) + ' ' + p.security.data.last_trade_time"></td>
                                            </tr>
                                            <tr>
                                                <td>Day's Low</td>
                                                <td>@{{p.security.data.days_low}}</td>
                                            </tr>
                                            <tr>
                                                <td>Day's High</td>
                                                <td>@{{p.security.data.days_high}}</td>
                                            </tr>
                                            <tr>
                                                <td>Year Low</td>
                                                <td>@{{p.security.data.year_low}}</td>
                                            </tr>
                                            <tr>
                                                <td>Year High</td>
                                                <td>@{{p.security.data.year_high}}</td>
                                            </tr>
                                            <tr>
                                                <td>Market Capitalization</td>
                                                <td>@{{p.security.data.market_capitalization}}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr ng-show="showHoldingTransactions[$index] && dateFilterNotApplied">
                                <td colspan="10" class="t-table-sub">
                                    <table class="table table-hover table-bordered table-condensed table-striped port-trans-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Quantity</th>
                                                <th>Purchase Value</th>
                                                <th>Price</th>
                                                <th>Gain / Loss</th>
                                                <th>Return %</th>
                                                <th>Days Owned</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr ng-repeat="transaction in transactions[p.security.id] | orderBy:'date'">
                                                <td ng-bind-html="formatTrDate(transaction.date)"></td>
                                                <td ng-bind-html="transaction.quantity"></td>
                                                <td ng-bind-html="formatValue(transaction.c_trade_value_local, 2)"></td>
                                                <td ng-bind-html="formatValue(transaction.c_trade_quote_local, 2)"></td>
                                                <td ng-bind-html="formatValue(transaction.gain_loss, 2)"></td>
                                                <td ng-bind-html="formatValue(transaction.return_percentage, 2) + ' %'"></td>
                                                <td ng-bind-html="transaction.days_owned"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                        <tr ng-if="(portfolioHoldings.length > 1)">
                            <td colspan="9" class="text-right"><b>Total</b></td>
                            <td>@{{ formatValue(portfolioHoldings.total_market_value_in_base) }}</td>
                            <td>@{{ formatValue(portfolioHoldings.total_gain_loss_in_base) }}</td>
                            <td>@{{ formatValue(portfolioHoldings.total_weight) + ' %' }}</td>
                        </tr>
                        <tr ng-if="!portfolioHoldings.length">
                            <td colspan="11">No Holdings Found.</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- CARD DEVIDER CONTENT -->
    <div style="margin:15px 0"></div>
    <!-- <div class="col_full setting-actions">
        <a href="javascript:void(0);" ng-click="portfolioDailyValuesModal('xlg')" class="button button-small button-circle button-green">Portfolio Daily Values</a>
    </div> -->

    <div class="col-md-6 portfolio_cards card card-p">
        <!-- BANK ACCOUNT SECURITY CARD -->
        <div class="row">
            <div class="fancy-title">
                <h3 class="title_bold">Bank Accounts</h3>
                <div class="tag"></div>
            </div>
            <div class="row">
                <div class="col-xs-12 portfolio_cards card card-p ">
                </div>
            </div>
            <div class="col-xs-12 portfolio_cards card card-p">
            </div>
        </div>
        <div class="row pt_0">
            <!-- repeat columns -->

            <div ng-repeat="bank in portfolioBanks" class="bank_card banks-stat col-sm-6" ui-sref="panel.show.banks_balance({id: $root.portfolio,bank_id: bank.id})">
                <span ng-class="{ 'col_last' : ($index + 1) % 2 == 1 }" class="devider"></span>
                <h2 class="accounts_title">@{{ bank.name }}</h2>
                <p class="portf_font_title">
                    CURRENT BALANCE
                </p>
                <p class="accounts_value">
                    @{{bank.cash_amount | currFilter:currentPortfolio.comma_separator }} @{{bank.currency.symbol}}
                </p>
            </div>
            <div ng-if="!portfolioBanks.length" class="text-center">
                <button ng-click="openCreateBankModal()" class="btn btn-primary">
                    Click here to Create a Bank Account
                </button>
            </div>

        </div>
        <!-- KEY FIGURES CARD-->
        <div class="row card_group">
            <div class="fancy-title">
                <h3 class="title_bold">Key Figures in <b>@{{ portfolioData.currency.symbol }}</b></h3>
                <div class="tag"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 table-responsive">
                <table class="table table-hover table-striped table_no_borders">
                    <thead>
                    <tr>
                        <th>KEY FIGURES</th>
                        <!-- <th></th> -->
                        <th>
                            <div class="date_picker">
                                <input date-range-picker="" class="key_figures_input form-control date-picker ng-pristine ng-valid ng-isolate-scope ng-touched" type="text" ng-model="datePickerDate" options="datePickerOptions" placeholder="Date To/From" required="">
                            </div>
                        </th>
                        <th>SINCE PORTFOLIO START</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="(key, pyKeyFigure) in keyFigures.past_year">
                            <td>@{{ key.split("_").join(" ") }}</td>

                            <td>@{{ (['Total_no._of_Trades', 'Realized_Trades'].indexOf(key) > -1) ? formatValue(pyKeyFigure, 0) : formatValue(pyKeyFigure, 2) }} @{{ (['Commision_%', 'Realized_Return_%', 'Currency_Adjusted_Return_%'].indexOf(key) > -1) ? '%' : '' }}</td>
                            <!-- <td></td> -->

                            <td>@{{ (['Total_no._of_Trades', 'Realized_Trades'].indexOf(key) > -1) ? formatValue(keyFigures.since_portfolio_start[key], 0) : formatValue(keyFigures.since_portfolio_start[key], 2) }} @{{ (['Commision_%', 'Realized_Return_%', 'Currency_Adjusted_Return_%'].indexOf(key) > -1) ? '%' : '' }}</td>

                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- GAIN & LOSS CARD-->
        <div class="row card_group">
            <div class="fancy-title title-dotted-border">
                <h3 class="title_bold">Gain & Loss Analysis</h3>
                <div class="tag"></div>
                <div class="row">
                    <div class="col-sm-8">
                    </div>
                    <div class="col-sm-4 select-dir">
                        <span class="multi_select">
                            <isteven-multi-select
                                input-model="inputModel"
                                output-model="outputModel"
                                button-label="attrName subAttrName"
                                item-label="attrName subAttrName group_name"
                                tick-property="selected"
                                group-property="is_group"
                                disable-property="disabled"
                                on-item-click="generateInputHoldingsAnalysis( data )"
                                on-select-none="clearMultiSelection()"
                                on-reset="clearMultiSelection()"
                                translation="localLang"
                            >
                            </isteven-multi-select>
                        </span>
                    </div>
                </div>
            </div>

            <div class="fancy-title col-xs-12">
                <h3>GAIN</h3>
            </div>
            <div class="col-xs-12 table-responsive">
                <table class="table table-hover table-striped table_no_borders gain-table table_border_bottom">
                    <thead>
                        <tr>
                            <th></th>
                            <th>MARKET VALUE</th>
                            <th>WEIGHT</th>
                            <th>GAIN</th>
                            <th>WEIGHT</th>
                            <th>DIVIDEND</th>
                            <th>WEIGHT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat=" (attr, gainByAttributeRow) in gainLossSet['gain']['gainBy']">
                            <td>@{{ attr }}</td>
                            <td>@{{ gainByAttributeRow['market_value_in_base'] }}</td>
                            <td>@{{ gainByAttributeRow['weight_for_market_value_in_base'] + ' %' }}</td>
                            <td>@{{ gainByAttributeRow['gain_loss_in_base'] }}</td>
                            <td>@{{ gainByAttributeRow['weight_for_gain_loss_in_base'] + ' %' }}</td>
                            <td>@{{ gainByAttributeRow['dividend'] }}</td>
                            <td>@{{ gainByAttributeRow['weight_for_dividend'] + ' %' }}</td>
                        </tr>
                        <tr>
                            <td>TOTAL</td>
                            <td>@{{ gainLossSet.gain.totalsOfGain.total_market_value_in_base }}</td>
                            <td>@{{ gainLossSet.gain.totalsOfGain.total_weight_for_market_value_in_base + ' %' }} </td>
                            <td>@{{ gainLossSet.gain.totalsOfGain.total_gain_loss_in_base }}</td>
                            <td>@{{ gainLossSet.gain.totalsOfGain.total_weight_for_gain_loss_in_base + ' %' }} </td>
                            <td>@{{ gainLossSet.gain.totalsOfGain.total_dividend }}</td>
                            <td>@{{ gainLossSet.gain.totalsOfGain.total_weight_for_dividend + ' %' }} </td>
                        </tr>
                    </tbody>
                </table>
            </div>
                <div class="fncy-title title-dotted-border col-xs-12">
                    <h3>LOSS</h3>
                </div>
            <div class="col-xs-12 table-responsive">
                <table class="table table-hover table-striped table_no_borders loss-table table_border_bottom">
                    <thead>
                        <tr>
                            <th></th>
                            <th>MARKET VALUE</th>
                            <th>WEIGHT</th>
                            <th>LOSS</th>
                            <th>WEIGHT</th>
                         </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="(attr, lossByAttributeRow) in gainLossSet['loss']['lossBy']">
                            <td>@{{ attr }}</td>
                            <td>@{{ lossByAttributeRow['market_value_in_base'] }}</td>
                            <td>@{{ lossByAttributeRow['weight_for_market_value_in_base'] + ' %' }}</td>
                            <td>@{{ lossByAttributeRow['gain_loss_in_base'] }}</td>
                            <td>@{{ lossByAttributeRow['weight_for_gain_loss_in_base'] + ' %' }}</td>
                        </tr>
                        <tr>
                            <td>Total</td>
                            <td>@{{ gainLossSet.loss.totalsOfLoss.total_market_value_in_base }}</td>
                            <td>@{{ gainLossSet.loss.totalsOfLoss.total_weight_for_market_value_in_base + ' %' }} </td>
                            <td>@{{ gainLossSet.loss.totalsOfLoss.total_gain_loss_in_base }}</td>
                            <td>@{{ gainLossSet.loss.totalsOfLoss.total_weight_for_gain_loss_in_base + ' %' }} </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-xs-12 portfolio_currency">
                All figures are in  @{{ portfolioData.currency.symbol }}
            </div>
        </div>


        <!-- INCOME ANALYSIS CARD-->
        <div class="row card_group">
            <div class="fancy-title title-dotted-border">
                <h3 class="title_bold">Income Analysis</h3>
                <div class="tag"></div>
                <div class="row">
                    <div class="col-sm-4">
                    </div>
                    <div class="col-sm-4 select-dir">
                        <span>
                            <input date-range-picker="" class="form-control date-picker ng-pristine ng-valid ng-isolate-scope ng-touched" type="text" ng-model="datePickerIncomeAnalysis" options="incomeAnalysisDatePickerOptions" required="" placeholder="Choose date range">
                        </span>
                    </div>
                    <div class="col-sm-4 select-dir">
                        <span class="multi_select">
                            <isteven-multi-select
                                input-model="inputModelIncomeAnalysis"
                                output-model="outputModelIncomeAnalysis"
                                button-label="attrName subAttrName"
                                item-label="attrName subAttrName group_name"
                                tick-property="selected"
                                group-property="is_group"
                                disable-property="disabled"
                                on-item-click="generateInputIncomeAnalysis( data )"
                                on-select-none="clearMultiSelectionIncomeAnalysis()"
                                on-reset="clearMultiSelectionIncomeAnalysis()"
                                translation="localLang"
                            >
                            </isteven-multi-select>
                        </span>
                    </div>
                </div>
            </div>

            <div class="fancy-title col-xs-12">
                <h3>PROFIT</h3>
            </div>
            <div class="col-xs-12 table-responsive">
                <table class="table table-hover table-striped table_no_borders gain-table table_border_bottom">
                    <thead>
                        <tr>
                            <th></th>
                            <th>TRADE VALUE</th>
                            <th>WEIGHT</th>
                            <th>PROFIT</th>
                            <th>WEIGHT</th>
                            <th>DIVIDEND</th>
                            <th>WEIGHT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat=" (attr, profitByAttributeRow) in profitLossSet['profit']['profitBy']">
                            <td>@{{ attr }}</td>
                            <td>@{{ profitByAttributeRow['trade_value_base_profit'] }}</td>
                            <td>@{{ profitByAttributeRow['weight_for_trade_value_base_profit'] + ' %' }}</td>
                            <td>@{{ profitByAttributeRow['profits_base'] }}</td>
                            <td>@{{ profitByAttributeRow['weight_for_profits_base'] + ' %' }}</td>
                            <td>@{{ profitByAttributeRow['net_dividend_base'] }}</td>
                            <td>@{{ profitByAttributeRow['weight_for_net_dividend_base'] + ' %' }}</td>
                        </tr>
                        <tr>
                            <td>TOTAL</td>
                            <td>@{{ profitLossSet.profit.totalsOfProfits.total_trade_value_base_profit }} </td>
                            <td>@{{ profitLossSet.profit.totalsOfProfits.total_weight_for_trade_value_base_profit }}</td>
                            <td>@{{ profitLossSet.profit.totalsOfProfits.total_profits_base }}</td>
                            <td>@{{ profitLossSet.profit.totalsOfProfits.total_weight_for_profits_base }} </td>
                            <td>@{{ profitLossSet.profit.totalsOfProfits.total_net_dividend_base }}</td>
                            <td>@{{ profitLossSet.profit.totalsOfProfits.total_weight_for_net_dividend_base }} </td>
                        </tr>
                    </tbody>
                </table>
            </div>
                <div class="fncy-title title-dotted-border col-xs-12">
                    <h3>LOSS</h3>
                </div>
            <div class="col-xs-12 table-responsive">
                <table class="table table-hover table-striped table_no_borders loss-table table_border_bottom">
                    <thead>
                        <tr>
                            <th></th>
                            <th>TRADE VALUE</th>
                            <th>WEIGHT</th>
                            <th>LOSS</th>
                            <th>WEIGHT</th>
                         </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="(attr, lossByAttributeRow) in profitLossSet['loss']['lossBy']">
                            <td>@{{ attr }}</td>
                            <td>@{{ lossByAttributeRow['trade_value_base_loss'] }}</td>
                            <td>@{{ lossByAttributeRow['weight_for_trade_value_base_loss'] + ' %' }}</td>
                            <td>@{{ lossByAttributeRow['losses_base'] }}</td>
                            <td>@{{ lossByAttributeRow['weight_for_losses_base'] + ' %'}}</td>
                        </tr>
                        <tr>
                            <td>Total</td>
                            <td>@{{ profitLossSet.loss.totalsOfLoss.total_trade_value_base_loss }} </td>
                            <td>@{{ profitLossSet.loss.totalsOfLoss.total_weight_for_trade_value_base_loss + ' %' }} </td>
                            <td>@{{ profitLossSet.loss.totalsOfLoss.total_losses_base }}</td>
                            <td>@{{ profitLossSet.loss.totalsOfLoss.total_weight_for_losses_base + ' %'}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-xs-12 portfolio_currency">
                All figures are in @{{ portfolioData.currency.symbol }}
            </div>
        </div>
    </div>

    <div class="col-md-6 portfolio_cards card card-p" >

        <!-- GUIDELINES LIST CARD-->
        <div class="row guideline_pile">
            <div class="fancy-title">
                <h3 class="title_bold">Portfolio Guidelines
                <button id="add_guideline" class="btn btn-primary" ng-click="showGuidelineFields()" label="Add Guideline"><span>Add Guideline</span> +</button></h3>
                <div class="tag"></div>
            </div>
            <div class="col-xs-12 table-responsive">
                <table class="table table-striped table_no_borders tr_rows_stripe" >
                    <thead>
                        <tr>
                            <th>Guideline</th>
                            <th>Attribute</th>
                            <th>Min.</th>
                            <th>Max.</th>
                            <th>Current Value</th>
                            <th>Variance</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="item in guidelineItems">
                            <td>@{{ item.guidelineText }} <img ng-if="(['weight_per_position', 'risc_per_position'].indexOf(item.guideline) < 0) && item.warning_msg" class="guideline-alert" src="/images/icons/alert-icon.png" boostrap-popover data-content="@{{item.warning_msg}}" data-trigger="hover" /> </td>
                            <td><span>@{{ item.gl_attr }}</span></td>
                            <td>@{{ (item.guideline == 'securities_in_portfolio') ? (item['min'] | noDecimals) : (item['min'] | currFilter:currentPortfolio.comma_separator) + "%" }}</td>
                            <td>@{{ (item.guideline == 'securities_in_portfolio') ? (item['max'] | noDecimals) : (item['max'] | currFilter:currentPortfolio.comma_separator) + "%" }}</td>
                            <td>@{{ item['current_value'] }}</td>
                            <td>@{{ item['variance'] }}</td>

                            <td><button class="btn btn-danger delete_guideline" ng-click="removeGuideline($index)" ><i class="glyphicon glyphicon-minus"></i></button></td>
                        </tr>
                        <tr class="guideline_adding_row">
                            <td>
                                <select class="guideline_item guideline_data_item" name="guideline" ng-options="item as item.name for item in guidelineOption track by item.id " ng-change="getGuidelineAttr()" ng-model="guideline" ng-click="showGuidelineDesc()">
                                    <option value="0">Choose Guideline</option>
                                </select>
                            </td>
                            <td>
                                <select class="guideline_item guideline_data_item" name="attribute" ng-model="guideline_attr">
                                    <option ng-repeat="item in guidelineAttrOption"
                                            value="@{{item.attrName}}"
                                            data-attrib_type="@{{item.attribute}}">@{{item.attrName}}</option>
                                </select>
                            </td>
                            <td>
                                <input class="guideline_item guideline_data_item" type="tel" name="min" ng-model="min" min="0">
                            </td>
                            <td>
                                <input class="guideline_item guideline_data_item" type="tel" name="max" ng-model="max" min="0">
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    <tbody>
                </table>
                <span class="help-block" ng-if="show_guideline_desc">@{{guidelineOptionOriginal[guideline.id].desc}}</span>
                <button id="save_guideline" class="btn btn-primary guideline_item" ng-click="addGuideline()">Save</button>
                <button id="cancel_guideline" class="btn btn-default guideline_item" ng-click="hideGuidelineFields()">Cancel</button>
            </div>
        </div>

        <!-- TRANSACTION LIST CARD-->
        <div class="row card_group">
            <div class="fancy-title">
                <h3 class="title_bold">Transactions (ten most recent)</h3>
                <div class="tag"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 table-responsive">
                <table class="table table-striped table_no_borders tr_rows_stripe" >
                    <thead>
                        <tr>
                            <th>DATE</th>
                            <th>TRANSACTIONS</th>
                        </tr>
                    </thead>
                    <tbody data-ng-repeat="t in allPortfolioTransactions" ui-sref="panel.transactions.portfolio({id: $root.portfolio})" style="cursor:pointer">
                        <tr>
                            <td ng-bind-html="formatTDate(t)"></td>
                            <td ng-bind-html="formatTName(t)"></td>
                        <tr>
                    <tbody>
                </table>
            </div>
        </div>
        <!-- WATCHLIST BASED CARD -->
        <div class="row card-p card_group">
            <div class="fancy-title">
                <h3 class="title_bold">Watchlist</h3>
                <div class="tag"></div>
            </div>
            <div class="col-xs-12 table table-responsive">

                <span class="add_sec_trigger" title="Add Security"><img src="images/table/plus.png" alt="add"></span>

                <div class="add_sec_form">
                    <div class="input-group search" style="float:right">
                        <div class="input-group search" style="border-width: 0px">
                            <div class="input-group-btn">
                                <button class="btn btn-default" ng-click="addSecuritiesToWatchlist()" style="border-right-width:1px">
                                     <img src="images/table/plus.png" alt="add">
                                </button>
                            </div>

                            <ui-select class="ng-pristine ng-valid ng-empty ng-touched" ng-model="watchlist.securities" multiple style="border-width:0px; height: 100%;">
                                <ui-select-match placeholder="ADD SECURITIES">
                                    <span ng-bind="$item.symbol"></span>
                                </ui-select-match>
                                <ui-select-choices repeat="item in (securitySymbols[portfolioData.id] | filter: $select.search )  track by item.symbol" ng-attr-refresh="fetchSecurity($select.search, $select)" refresh-delay="2000">
                                    <div class="secu-search">
                                        <div class="secu-search-symname">
                                            <div><strong>@{{item.symbol | uppercase}}</strong></div>
                                            <span class="secu-search-name">@{{item.name}}</span>
                                        </div>
                                        <div class="secu-search-typeexch">@{{item.security_type ? item.security_type + ' - ' : ''}}@{{item.exchange}}</div>
                                    </div>
                                </ui-select-choices>
                            </ui-select>
                        </div>
                    </div>
                </div>

                <table class="table table-hover table-striped table_no_borders tr_rows_stripe">
                    <thead>
                    <tr>
                        <th>SECURITY</th>
                        <th>PRICE</th>
                        <th>CHANGE +/-</th>
                        <th>CHANGE %</th>
                        <th>CHANGE SINCE ADDED</th>
                        <th>CHANGE % SINCE ADDED</th>
                        <th colspan="2"></th>
                    </tr>
                    </thead>
                    <tbody ng-repeat="securityToWatch in (currentSecuritiesWatchlist) track by securityToWatch.id" class="card">
                        <tr>
                            <td>@{{ securityToWatch.name }}</td>
                            <td>@{{ securityToWatch.data.last_trade_price_only }}</td>
                            <td>@{{ securityToWatch.data.change }}</td>
                            <td>@{{ securityToWatch.data.percentage_change }} %</td>
                            <td>@{{ securityToWatch.ltp_change_since_added}}</td>
                            <td>@{{ securityToWatch.ltp_change_percent}} % </td>
                            <td class="options_tb">
                                <div class="row opt_menu_tb">
                                    <div class="col-xs-12 det"  ng-click="displayWatchlistRow[$index] = !displayWatchlistRow[$index]">
                                        TRADING INFO
                                    </div>
                                    <div class="col-xs-12 o_text_danger" ng-click="removeSecFromWatchlist($index)">
                                        DELETE
                                    </div>
                                </div>
                            </td>
                            <td class="opt_trigger_tb" >
                                <img src="images/mode-circular-button.png">
                            </td>
                        </tr>
                        <tr ng-show="displayWatchlistRow[$index]">
                            <td colspan="12" class="t-table-sub">
                                <table class="table table-hover table-condensed table-striped port-trans-table">
                                    <thead class="levelTwo">
                                    <tr>
                                        <th>Field</th>
                                        <th>Value</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Date added</td>
                                            <td ng-bind-html="formatTrDateTime(securityToWatch.date_added)"></td>
                                        </tr>
                                        <tr>
                                            <td>Security</td>
                                            <td>@{{securityToWatch.name}}</td>
                                        </tr>
                                        <tr>
                                            <td>Symbol</td>
                                            <td>@{{securityToWatch.symbol}}</td>
                                        </tr>
                                        <tr>
                                            <td>Exchange</td>
                                            <td>@{{securityToWatch.exchange}}</td>
                                        </tr>
                                        <tr>
                                            <td>Security Type</td>
                                            <td>@{{securityToWatch.security_type}}</td>
                                        </tr>
                                        <tr>
                                            <td>Volume</td>
                                            <td>@{{securityToWatch.data.volume}}</td>
                                        </tr>
                                        <tr>
                                            <td>Average Daily Volume</td>
                                            <td>@{{securityToWatch.data.average_daily_volume}}</td>
                                        </tr>
                                        <tr>
                                            <td>Previous Close</td>
                                            <td>@{{securityToWatch.data.previous_close}}</td>
                                        </tr>
                                        <tr>
                                            <td>Open</td>
                                            <td>@{{securityToWatch.data.open}}</td>
                                        </tr>
                                        <tr>
                                            <td>Change</td>
                                            <td>@{{securityToWatch.data.change}}</td>
                                        </tr>
                                        <tr>
                                            <td>% Change</td>
                                            <td>@{{securityToWatch.data.percentage_change}} %</td>
                                        </tr>
                                        <tr>
                                            <td>Last Trade Price Only</td>
                                            <td>@{{securityToWatch.data.last_trade_price_only}}</td>
                                        </tr>
                                        <tr>
                                            <td>Trade Price When Added</td>
                                            <td>@{{securityToWatch.price_added_watchlist}}</td>
                                        </tr>
                                        <tr>
                                            <td>Last Trade Date Time</td>
                                            <td ng-bind-html="formatTrDate(securityToWatch.data.last_trade_date) + ' ' + securityToWatch.data.last_trade_time"></td>
                                        </tr>
                                        <tr>
                                            <td>Day's Low</td>
                                            <td>@{{securityToWatch.data.days_low}}</td>
                                        </tr>
                                        <tr>
                                            <td>Day's High</td>
                                            <td>@{{securityToWatch.data.days_high}}</td>
                                        </tr>
                                        <tr>
                                            <td>Year Low</td>
                                            <td>@{{securityToWatch.data.year_low}}</td>
                                        </tr>
                                        <tr>
                                            <td>Year High</td>
                                            <td>@{{securityToWatch.data.year_high}}</td>
                                        </tr>
                                        <tr>
                                            <td>Market Capitalization</td>
                                            <td>@{{securityToWatch.data.market_capitalization}}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>

                    <tr ng-if="!currentSecuritiesWatchlist.length">
                        <td colspan="11">Watchlist is empty.</td>
                    </tr>
                </table>
            </div>
        </div>
        <!-- CURRENCIES BASED CARD -->
        <div class="row card_group">
            <div class="fancy-title">
                <h3 class="title_bold">Currencies relative to @{{ portfolioData.currency.symbol }}</h3>
                <div class="tag"></div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 table-responsive">
                <table class="table table-hover table-striped table_no_borders">
                    <thead>
                    <tr>
                        <th></th>
                        <th>Currency</th>
                        <th>Rate</th>
                    </tr>
                    </thead>
                    <tbody class="flag_list">
                        <tr ng-repeat="c in filterCurrencies">
                            <td>
                                <img class="flag flag-@{{(filterCurrencyNamesAlias[c.name.replace(portfolioData.currency.symbol, '')])}} @{{ c.name.replace(portfolioData.currency.symbol, '') }}" />
                            </td>
                            <td>@{{ c.name.replace(portfolioData.currency.symbol, '') + " (" + filterCurrencyNames[c.name.replace(portfolioData.currency.symbol, '')] + ")" }}</td>
                            <td ng-bind-html="formatValue(c.value, 4)"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="load_more load_more_crn">
                <a href="javascript:void(0);" class="button button-mini button-border button-rounded">
                     <img src="images/table/ios-arrow-down.png" alt="arrow"> <span class="list_visible_tile list_tile">Expand tile</span> <span class="list_expanded_tile list_tile">Contract tile</span>
                </a>
            </div>
           <!--  <div>
            Currency rates last updated: @{{ lastUpdatedCurrencies }}
            </div>
            {{--<ul uib-pagination ng-model="currentPageCurrencies"--}}
            {{--total-items="filterCurrencies.length"--}}
            {{--max-size="maxSizeCurrencies"--}}
            {{--boundary-links="true"--}}
            {{--items-per-page="numPerPageForCurrencies"--}}
            {{--ng-show="currentPageCurrencies">--}}
            {{--</ul>--}} -->
        </div>
    </div>
</div>
@include('includes.modals.new_bank')
<script type="text/javascript">

   var hide = {'display': 'none'};
    $(document).on('click submit', function(e){

        if ($(e.target).is('.add_sec_trigger, .add_sec_trigger > img')){
            $('.add_sec_trigger').css(hide);
            $('.add_sec_form').animate({'width': 'show'});
        } else {
            if (!$(e.target).closest('.add_sec_form').length) $('.add_sec_trigger').css('display', 'block'), $('.add_sec_form').css(hide);
        }
    });

    $('.load_more_crn').on('click submit', function(){
        $('.flag_list').toggleClass('list_all');
        $(this).parent().toggleClass('list_expanded');
    });

</script>