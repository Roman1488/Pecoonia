<div class="col_full container-fluid bank_ballans_page">

        <div class="container-fluid portfolio_cards card card-p" style="margin-bottom: 15px">
            <div class="row">
                <div class="row">
                    <div class="fancy-title title-dotted-border">
                        <div class="container-fluid">
                            <div class="row" style="padding-bottom: 0px;">
                                <div class="col-sm-6">
                                    <h3 class="bank_ballance_tittle title_bold"> Transactions</h3>
                                    <div class="tag"></div>
                                </div>
                                <div class="col-sm-6">
                                    <form class="navbar-form" role="search">
                                        <div class="input-group search">
                                            <div class="input-group-btn">
                                                <button class="btn btn-default" type="submit" ng-click="searchTransactions()">
                                                    <img  src="images/menu_bar/search-icon-blue.png" alt="search">
                                                </button>
                                            </div>
                                            <input type="text" class="form-control" placeholder="SEARCH" name="" ng-model="transactionsSearch">
                                        </div>
                                        <div ng-repeat="t in transactionsSearchArray" class="search_terms">
                                            <div class="search_terms__item">
                                                Free text: @{{ t }} <span class="delete_label" ng-click="removeSearchTerm(t)">
                                                    <i class="fa fa-close"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div ng-if="transactionsSearchArray.length">
                                            <div class="search_terms__reset">
                                                RESET ALL <span class="delete_label" ng-click="clearSearchTransiction()">
                                                    <i class="fa fa-close"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </form>
                                 </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 table table-responsive">
                    <table class="table table-striped table_no_borders tr_rows_stripe">
                        <thead>
                            <tr>
                                <th class="col-xs-1">DATE</th>
                                <th class="col-xs-10">TRANSACTION</th>
                                <th class="col-xs-1" colspan='2'></th>
                            </tr>
                        </thead>
                        <tbody data-ng-repeat="t in allPortfolioTransactions" class="card">
                            <tr id="t.id">
                                <td ng-bind-html="formatTrDate(t)"></td>
                                <td ng-bind-html="formatTrName(t)"></td>
                                <td class="options_tb">
                                    <div class="row opt_menu_tb">
                                        <div class="col-xs-12 det" data-ng-click="selectTableRow($index, t.id)" >
                                            DETAILS
                                        </div>
                                        <div class="col-xs-12 o_text_danger" ng-click="confirmTransDelete(t.id, $index, $event);" ng-if="t.del_transaction_link">
                                            DELETE
                                        </div>
                                    </div>
                                </td>
                                <td class="opt_trigger_tb" >
                                    <img src="images/mode-circular-button.png">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="9" style="padding: 0px">
                                    <div uib-collapse="!tableDataCollapse[$index]">
                                        <table class="table table-hover table-condensed table-striped port-trans-table small-first-column-table">
                                            <thead class="levelTwo">
                                            <tr>
                                                <th>Field</th>
                                                <th>Value</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr data-ng-repeat="(key, val) in t" ng-if="shouldDisplayField(key, t.transaction_type, t.security_currency) && val">
                                                <td>@{{displayColumnKey(key, val, t.security_currency)}}</td>
                                                <td>@{{displayColumnValue(key, val)}}</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- <div ng-if="currentPortfolioId">
                        Showing @{{ allPortfolioTransactions.length }} out of @{{ total }} transactions.
                    </div> -->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-4">
                                    <span ng-if="currentPortfolioId">
                                        Showing @{{ allPortfolioTransactions.length }} of @{{ total }}
                                    </span>
                                </div>
                                <div class="col-lg-4">
                                    <div class="load_more">
                                        <a href="javascript:void(0);" ng-click="loadMore()" class="button button-mini button-border button-rounded">
                                            <span class="text-black">Load more transactions &nbsp;&nbsp;&nbsp;&nbsp;</span>
                                             <img src="images/table/ios-arrow-down.png" alt="arrow">
                                        </a>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- <a href="javascript:void(0);" ng-click="loadMore()" class="button button-mini button-border button-rounded"><span>Load more</span></a> -->
                    {{--<a href="#" ng-click="goToTop()" class="button button-mini button-border button-rounded"><span>To top</span></a>--}}
                    <!-- <a href="javascript:void(0);" class="button button-mini button-border button-rounded"><span>Show all</span></a>  -->

                    {{--<ul uib-pagination ng-model="currentPagePortfolios"--}}
                    {{--total-items="$root.userPortfolios.length"--}}
                    {{--max-size="maxSizePortfolios"--}}
                    {{--boundary-links="true"--}}
                    {{--items-per-page="numPerPageForPortfolios"--}}
                    {{--ng-show="currentPagePortfolios">--}}
                    {{--</ul>--}}
                </div>
            </div>
        </div>
</div>

