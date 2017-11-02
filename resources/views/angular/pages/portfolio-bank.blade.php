@include('includes.modals.new_bank')
<div class="col_full portfolio_bank_page">
     <div class="container-fluid cards_view">
        <div class="card col-md-4" ng-repeat="bank in portfolioBanks" ng-class="{ 'col_last' : ($index + 1) % 4 == 0 }">
            <table class="tbl_opt" style="position:relative;top:30px; left: 85%;">
                <tr>
                    <td class="options_tb">
                        <div class="row opt_menu_tb">
                            <div class="other_opt col-xs-12 o_text-info" ui-sref="panel.settings">
                                EDIT
                            </div>
                            <div style="display:none" class="col-xs-12 det"  ui-sref="panel.show.banks_balance({id: $root.portfolio,bank_id: bank.id})">
                                DETAILS
                            </div>
                            <div class="other_opt col-xs-12 o_text-grey"
                                ng-click="deReActivateBank('md', bank)" >
                               @{{ (bank.status === 1) ? 'DEACTIVATE' : 'REACTIVATE' }}
                            </div>
                            <div class="other_opt col-xs-12 o_text_danger" ng-click="deleteBank('md', bank, $index)">
                                DELETE
                            </div>
                        </div>
                    </td>
                    <td class="opt_trigger_tb" >
                        <img src="images/mode-circular-button.png">
                    </td>
                </tr>
            </table>

            <div class="card_content col-xs-12">
            <div class="bank_header"> @{{ bank.name}} </div>
            <p class="portf_font_title col-xs-12"> CURRENT BALANCE</p>
            <p class="accounts_value col-xs-12">
                @{{bank.cash_amount | currFilter:currentPortfolio.comma_separator }} @{{bank.currency.symbol}}
            </p>
            </div>
        </div>

        <div class="card col-md-4 card_empty" ng-if="portfolioBanks.length <= 0">
            <p class="no-bank-found">NO BANK FOUND</p>
        </div>

        <div class="card col-md-4 add_new_portfolio" ng-click="openCreateBankModal()" type="button">
            <span class="plus-icon">+</span> <span>NEW BANK</span>
        </div>

    </div>
</div>
