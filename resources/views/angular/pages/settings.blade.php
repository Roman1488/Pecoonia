
<div class="row settings">

    <div class="col_full col-sm-6 col-sm-push-6 bank_ballans_page">
        <div class="col-xs-12 table">
            <div class="fancy-title title-dotted-border">
                <h3 class="bank_ballance_tittle title_bold">Tags</h3>
                <div class="tag"></div>
                <!-- <span class="edit">Edit</span> -->
            </div>
            <div class="col_full">
                <span ng-repeat="tag in allTags track by $index" class="btn btn-default btn-xs settings-tags" type="button" ng-click="confirmTagDelete(tag, $index, $event)">
                    <span>
                        <span class="settings-tags-tag">@{{ tag }} &nbsp <img class="cross_close" src="/images/Icon_close.png"> </span>
                    </span>
                </span>
                <span ng-if="allTags.length <= 0">No Tags.</span>
            </div>
        </div>
    </div>

    <div class="col_full col-sm-6 col-sm-pull-6 bank_ballans_page">
        <div class="col-xs-12 table table-responsive">
            <div class="fancy-title title-dotted-border">
                <h3 class="bank_ballance_tittle title_bold">Portfolio</h3>
                <div class="tag"></div>
            </div>
            <table class="table table-hover table-striped table_no_borders">
                <thead>
                    <tr>
                        <th>NAME</th>
                        <th>CURRENCY</th>
                        <th>TYPE</th>
                        <th>DECIMAL MARK</th>
                        <th>DATE FORMAT</th>
                        <th colspan=3></th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="portfolio in userAllPortfolios track by $index" ng-class="(portfolio.status === 1) ? 'active' : 'deactiveted'" data-toggle="tooltip" data-placement="left" title="@{{ (portfolio.status === 1) ? 'Active' : 'Deactivated'}}">
                        {{--<td>@{{ $index + 1 }}</td>--}}
                        <td>@{{ portfolio.name }}</td>
                        <td>@{{ portfolio.currency.symbol }}</td>
                        <td>@{{ $root.isCompany[portfolio.is_company] }}</td>
                        <td>@{{ $root.decimalMark[portfolio.comma_separator] }}</td>
                        <td>@{{ portfolio.date_format }}</td>
                        <td class="options_tb">
                            <div class="row opt_menu_tb">
                                <div class="col-xs-12"  ng-click="editModalPortfolios('sm', portfolio)" >
                                    EDIT
                                </div>
                                <div class="col-xs-12 o_text-grey"  ng-click="deReActivateModalPortfolio('md', portfolio)" >
                                    @{{ (portfolio.status === 1) ? 'DEACTIVATE' : 'REACTIVATE' }}
                                </div>
                                <div class="col-xs-12 o_text_danger" ng-click="deleteModalPortfolio('md', portfolio)">
                                    DELETE
                                </div>
                            </div>
                        </td>
                        <td class="opt_trigger_tb" >
                            <img src="images/mode-circular-button.png">
                        </td>
                    </tr>
                </tbody>
            </table>
            {{--<ul uib-pagination ng-model="currentPagePortfolios"--}}
            {{--total-items="userAllPortfolios.length"--}}
            {{--max-size="maxSizePortfolios"--}}
            {{--boundary-links="true"--}}
            {{--items-per-page="numPerPageForPortfolios"--}}
            {{--ng-show="currentPagePortfolios">--}}
            {{--</ul>--}}
        </div>
    </div>

    <div class="col_full col-sm-6  bank_ballans_page">
        <div class="col-xs-12 table table-responsive">
            <div class="fancy-title title-dotted-border">
                <h3 class="bank_ballance_tittle title_bold">Bank Accounts</h3>
                <div class="tag"></div>
            </div>
            <table class="table table-hover table-striped table_no_borders">
                <thead>
                    <tr>
                        <th>NAME</th>
                        <th>CURRENCY</th>
                        <th>ALLOW OVERDRAFT</th>
                        <th>PORTFOLIO</th>
                        <th colspan="2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="bank in userBanks track by $index" ng-class="(bank.status === 1) ? 'active' : 'deactiveted'" data-toggle="tooltip" data-placement="left" title="@{{ (bank.status === 1) ? 'Active' : 'Deactivated'}}">
                        <td>@{{ bank.name }}</td>
                        <td>@{{ bank.currency.symbol }}</td>
                        <td>@{{ $root.enableOverdraft[bank.enable_overdraft] }}</td>
                        <td>@{{ bank.portfolio.name }}</td>
                        {{--<td>@{{ $root.decimalMark[portfolio.comma_separator] }}</td>--}}
                        <td class="options_tb">
                            <div class="row opt_menu_tb">
                                <div class="col-xs-12"  ng-click="editModalBanks('sm', bank)" >
                                    EDIT
                                </div>
                                <div class="col-xs-12 o_text-grey"  ng-click="deReActivateBank('md', bank)" >
                                    @{{ (bank.status === 1) ? 'DEACTIVATE' : 'REACTIVATE' }}
                                </div>
                                <div class="col-xs-12 o_text_danger" ng-click="deleteBank('md', bank, $index)">
                                    DELETE
                                </div>
                            </div>
                        </td>
                        <td class="opt_trigger_tb">
                            <img src="images/mode-circular-button.png">
                        </td>
                    </tr>
                </tbody>
            </table>
            {{--<ul uib-pagination ng-model="currentPageBanks"--}}
            {{--total-items="userBanks.length"--}}
            {{--max-size="maxSizeBanks"--}}
            {{--boundary-links="true"--}}
            {{--items-per-page="numPerPageForBanks"--}}
            {{--ng-show="currentPageBanks">--}}
            {{--</ul>--}}
        </div>
    </div>
</div>
