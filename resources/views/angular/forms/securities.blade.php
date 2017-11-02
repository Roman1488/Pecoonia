@extends('angular.forms.modal_container')
@section('modal_header')
        <a class="close" ng-click="closeModal();" style="position:relative;top:15px;right:15px;padding: 15px;" aria-hidden="true"><img src="images/ios-close-outline.png" alt="close"></a>
        <div class="col_full modal_windows">
            <div class="modal-header">
                <div>NEW SECURITY TRANSACTION</div>
            </div>
        </div>
@stop
@section('modal_body')
        <form name="securitiesForm" method="post" ng-submit="securitiesTransaction()">

            <div class="form-group" ng-class="{ 'has-error' : securitiesForm.bank.$invalid && !securitiesForm.bank.$pristine }" ng-if="displayBankDropdown">
                <ui-select name="bank" ng-model="securities.bank" ng-change="checkCurrency('securities')" ng-if="displayBankName === false">
                    <ui-select-match placeholder="Bank">
                        <span ng-bind="$select.selected.name"></span>
                    </ui-select-match>
                    <ui-select-choices repeat="item in (banks | filter: $select.search) track by item.id">
                        <span ng-bind="item.name"></span>
                    </ui-select-choices>
                </ui-select>
                <div ng-if="displayBankName !== false" class="black-f-15" ng-bind="displayBankName"></div>
                <p ng-show="securitiesForm.bank.$error.required && !securitiesForm.bank.$pristine" class="help-block">The bank account field is required.</p>
            </div>

            <div class="form-group secur_select" ng-class="{ 'has-error' : securitiesForm.action.$invalid && !securitiesForm.action.$pristine }">
                <!-- <select ng-model="options.v"
                        ng-options="v for v in options"
                        class="secur_select opt_select"
                        ng-change="showSecurities(options.v)"
                        required>
                        <option value="" disabled selected>Buy/Sell</option>
                
                </select> -->
                <ui-select ng-model="options.name" ng-change="showSecurities(options.name)">
                    <ui-select-match placeholder="Buy/Sell">
                        <span ng-bind="$select.selected.name"></span>
                    </ui-select-match>
                    <ui-select-choices repeat="item in options track by item.id">
                        <span ng-bind="item.name"></span>
                    </ui-select-choices>
                </ui-select>
                <p ng-show="securitiesForm.action.$error.required && !securitiesForm.action.$pristine" class="help-block">This options is required.</p>
            </div>

            <div class="form-group" ng-class="{ 'has-error' : securitiesForm.date.$invalid && !securitiesForm.date.$pristine }">
                <div class="input-group">
                    <label for="dev_date_search" class="input-group-addon" style="border-color: #DDDDDD"><i class="icon-calendar3"></i></label>
                    <input type="text"
                           style="border-style: solid;
                        border-color: #DDDDDD;
                        border-width:1px;
                        border-left: 0px;
                        border-top-left-radius:0px;
                        border-bottom-left-radius:0px;"
                           name="date" options="dateOptions"
                           id="dev_date_search" ng-model="securities.date" class="sm-form-control tleft past-enabled input_search" uib-datepicker-popup="@{{formatForDatePicker}}" is-open="popup.opened" datepicker-options="dateOptions" close-text="Close" ng-click="open()" required />
                </div>
                <div>
                    <p ng-show="securitiesForm.date.$error.required && !securitiesForm.date.$pristine" class="help-block">The date field is required.</p>
                </div>
            </div>

            <div class="form-group" ng-class="{ 'has-error' : (securitiesForm.securities.$invalid && !securitiesForm.securities.$pristine) || isCurrencyOk, 'col_last' : displayBankDropdown }">
                <ui-select name="bank" ng-model="securities.securities" ng-change="setCurrency(securities.securities, 'securities'); fetchSecurityQuantity();">
                    <ui-select-match placeholder="Security">
                        <span ng-bind="$select.selected.name"></span>
                    </ui-select-match>
                    <ui-select-choices repeat="item in (allSecurities | filter: $select.search) track by item.symbol" ng-attr-refresh="(securities.action == 'buy') ? fetchSecurity($select.search, $select) : false" refresh-delay="2000" class="secu-list">
                        <div class="secu-search">
                            <div class="secu-search-symname">
                                <div><strong>@{{item.symbol | uppercase}}</strong></div>
                                <span class="secu-search-name">@{{item.name}}</span>
                            </div>
                            <div class="secu-search-typeexch">@{{item.security_type ? item.security_type + ' - ' : ''}}@{{item.exchange}}</div>
                        </div>
                    </ui-select-choices>
                </ui-select>
                <p ng-show="securitiesForm.securities.$error.required && !securitiesForm.securities.$pristine" class="help-block">The security field is required.</p>
                <p ng-show="isCurrencyOk" class="help-block">The currencies of portfolio, bank account and security are different. Please check.</p>
            </div>

            <div class="form-group">
                <input type="text" name="currency" value="" placeholder="Currency" ng-model="securities.currency.symbol" ng-disabled="true" />
            </div>

            <div class="form-group" ng-class="{ 'has-error' : securitiesForm.lc.$invalid && !securitiesForm.lc.$pristine }" ng-if="displayLocalCurrency">
                <input type="tel" ng-model="securities.local_currency_rate" name="lc" value="" placeholder="Currency Rate" required ng-pattern="fourDecimalPattern" step="0.01"/>
                <p ng-show="securitiesForm.lc.$error.required && !securitiesForm.lc.$pristine" class="help-block">The "Currency Rate" field is required.</p>
                <p ng-show="securitiesForm.lc.$error.pattern && !securitiesForm.lc.$pristine" class="help-block">Only numbers with four decimals or less are allowed (Decimal mark : "<strong ng-bind="decimalChar"></strong>").</p>
            </div>

            <div class="form-group" ng-class="{ 'has-error' : securitiesForm.quantity.$invalid && !securitiesForm.quantity.$pristine }">
                <input type="tel" ng-model="securities.quantity" name="quantity" value="" class="sm-form-control" placeholder="Quantity" required ng-pattern="/^\d+$/" />
                <p ng-show="securitiesForm.quantity.$error.required && !securitiesForm.quantity.$pristine" class="help-block">The quantity field is required.</p>
                <p ng-show="securitiesForm.quantity.$error.pattern && !securitiesForm.quantity.$pristine" class="help-block">The quantity field can have only counting number.</p>
            </div>

            <div class="form-group" ng-class="{ 'has-error' : securitiesForm.trade.$invalid && !securitiesForm.trade.$pristine }">
                <input type="tel" ng-model="securities.trade_value" name="trade" value="" class="sm-form-control" placeholder="Trade Value" required ng-pattern="twoDecimalPattern"/>
                <p ng-show="securitiesForm.trade.$error.required && !securitiesForm.trade.$pristine" class="help-block">The trade value field is required.</p>
                <p ng-show="securitiesForm.trade.$error.pattern && !securitiesForm.trade.$pristine" class="help-block">Only numbers with two decimals or less are allowed (Decimal mark : "<strong ng-bind="decimalChar"></strong>").</p>
            </div>

            <div class="form-group" ng-class="{ 'has-error' : securitiesForm.commision.$invalid && !securitiesForm.commision.$pristine }">

                <input type="tel" placeholder="Commision" ng-model="securities.commision" name="commision" value="" ng-pattern="twoDecimalPattern" step="0.01" />
                <p ng-show="securitiesForm.commision.$error.pattern && !securitiesForm.commision.$pristine" class="help-block">Only numbers with two decimals or less are allowed (Decimal mark : "<strong ng-bind="decimalChar"></strong>").</p>
            </div>

            <div class="form-group" ng-class="{ 'has-error' : securitiesForm.tags.$invalid && !securitiesForm.tags.$pristine || securitiesForm.tags.maxUiLength }">
                <ui-select class="secu-tags-select" name="tags" ng-model="securities.tags" multiple limit="20" security-tags="">
                    <ui-select-match placeholder="Tags">
                        <span ng-bind="$item"></span>
                    </ui-select-match>
                    <ui-select-choices repeat="item in (getTags($select.search) | filter: $select.search)">
                        <span ng-bind="item"></span>
                    </ui-select-choices>
                </ui-select>
                <p ng-show="securitiesForm.tags.$error.maxUiLength && !securitiesForm.tags.$pristine" class="help-block">Only select up to 20 tags.</p>
                <p ng-show="securitiesForm.tags.$error.uiWhiteSpaces && !securitiesForm.tags.$pristine" class="help-block">Tag name can't have white spaces.</p>
            </div>

            <div class="form-group btn_group text-right">
                <button type="button" class="btn btn_defoult fst_btn" ng-click="transaction_reset('securities')">CANCEL</button>
                <button type="submit" class="btn btn_defoult btn_blue" ng-class="{'inactive': securitiesForm.$invalid }" name="register-form-submit">SAVE</button>
                <!-- <a ng-click="goToTransactionList($root.portfolio)" ng-show="securitiesFormSubmitted">Go to Transaction List</a> -->
            </div>

        </form>
@stop
@section('modal_footer')

@stop