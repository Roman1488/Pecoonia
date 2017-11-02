<div class="col_full container-fluid bank_ballans_page">

        <div class="container-fluid portfolio_cards card">

            <div class="row">
                <div class="fancy-title title-dotted-border">
                    <div class="row">
                        <div class="col-md-6">
                            <h3 class="bank_ballance_tittle title_bold">@{{bank.name}}  Balance</h3>
                            <div class="tag"></div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12 table table-responsive">
                    <table class="col-xs-12 table table-hover table-striped table_no_borders">
                        <thead>
                            <tr>
                                <th class="col-xs-1">DATE</th>
                                <th class="col-xs-8">TEXT</th>
                                <th class="col-xs-2">AMOUNT</th>
                                <th class="col-xs-1">BALANCE</th>
                            </tr>
                        </thead>
                        <tbody ng-init="counter=0">
                            <tr ng-repeat="t in bank_transactions track by $index"  ng-if="t.transaction_type!=='bookvalue'">
                                <td>@{{t.tx_date}}</td>
                                <td>@{{t.tx_text}}</td>
                                <td>@{{t.tx_amount}}</td>
                                <td>@{{t.tx_balance}}</td>
                            <tr>
                        </tbody>
                    </table>
                </div>
                <!-- <div ng-if="currentPortfolioId">
                    Showing @{{ allPortfolioTransactions.length }} out of @{{ total }} transactions.
                </div> -->
<!--                 <a href="javascript:void(0);" ng-click="showAll()" class="button button-mini button-border button-rounded"><span>Show all</span></a>
 -->

                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-4">
                                <span ng-if="currentPortfolio.id">
                                    Showing @{{ bank_transactions.length }} of @{{ total }}
                                </span>
                            </div>
                            <div class="col-lg-4">
                                <div class="load_more">
                                    <a href="javascript:void(0);" ng-click="loadMore()" class="button button-mini button-border button-rounded">
                                    <span class="text-black">Load more transactions &nbsp;&nbsp;&nbsp;&nbsp;</span>
                                        <img src="images/table/ios-arrow-down.png" alt="button-circle">
                                    </a>
                                </div>
                            </div>
                            <div class="col-lg-4">
                            </div>
                        </div>
                    </div>
                </div>
             </div>
        </div>
    </div>
</div>


