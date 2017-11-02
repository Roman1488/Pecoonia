@include('includes.modals.new_portfolio')
<div class="col_full portfolio_create_page">

    <div class="container-fluid cards_view">

        <div class="row">
            <div class="card col-md-4" ng-repeat="portfolio in $root.userPortfolios" ng-class="{ 'col_last' : ($index + 1) % 4 == 0 }">
                <table class="tbl_opt" style="position:relative;top:30px; left: 85%;">
                    <tr>
                        <td class="options_tb">
                            <div class="row opt_menu_tb">
                                <div style="display: none" class="col-xs-12 det" ui-sref="panel.show.portfolio({id: portfolio.id})">
                                    DETAILS
                                </div>
                                <div class="other_opt col-xs-12 o_text-info" ui-sref="panel.settings">
                                    EDIT
                                </div>
                                <div class="other_opt col-xs-12 o_text_danger" ng-click="deleteModalPortfolio('md', portfolio)">
                                    DELETE
                                </div>
                            </div>
                        </td>
                        <td class="opt_trigger_tb" >
                            <img src="images/mode-circular-button.png">
                        </td>
                    </tr>
                </table>
                <h3 class="portf_header">Portfolio @{{ portfolio.name }}</h3>
                    <div class="row card_content">
                        <div class="col-xs-4 port_stat">
                            <p class="portf_font_title">PORTFOLIO VALUE</p>
                            <p>
                                <table>
                                    <tr>
                                        <td class="portf_font">@{{ portfolio.currency.portfolio_value | currFilter:portfolio.comma_separator}}</td>
                                     </tr>
                                </table>
                            </p>
                        </div>

                        <div class="col-xs-8 portf_currency  text-right">
                            @{{ portfolio.currency.symbol }}
                        </div>
                    </div>
            </div>
            <div class="card col-md-4 text-center add_new_portfolio" ng-click="openCreatePortfolioModal()">
                    <span class="plus-icon">+</span> <span>NEW PORTFOLIO</span>
            </div>
        </div>
    </div>
</div>
<!-- ui-sref="panel.show.portfolio({id: portfolio.id})" -->
