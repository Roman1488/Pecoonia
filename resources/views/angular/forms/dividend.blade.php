@extends('angular.forms.modal_container')
@section('modal_header')
         <a class="close" ng-click="closeModal();" style="position:relative;top:15px;right:15px;padding: 15px;"  aria-hidden="true"><img src="images/ios-close-outline.png" alt="close"></a>
        <div class="col_full modal_windows">
            <div class="modal-header">
                <div>NEW DIVIDEND TRANSACTION</div>
            </div>
        </div>
@stop
@section('modal_body')
        <form name="dividendForm" method="post" ng-submit="dividendTransaction()">

            <div class="form-group" ng-class="{ 'has-error' : dividendForm.bank.$invalid && !dividendForm.bank.$pristine }">

                <ui-select name="bank" ng-model="dividend.bank" ng-change="checkCurrency('dividend')" ng-if="displayBankName === false">
                    <ui-select-match placeholder="Bank">
                        <span ng-bind="$select.selected.name"></span>
                    </ui-select-match>
                    <ui-select-choices repeat="item in (banks | filter: $select.search) track by item.id">
                        <span ng-bind="item.name"></span>
                    </ui-select-choices>
                </ui-select>
                <div ng-if="displayBankName !== false" ng-bind="displayBankName"></div>
                <p ng-show="dividendForm.bank.$error.required && !dividendForm.bank.$pristine" class="help-block">The bank account field is required.</p>
            </div>

            <div class="form-group">
                <div class="input-group">
                    <label for="dev_date_search" class="input-group-addon" style="border-color: #DDDDDD"><i class="icon-calendar3"></i></label>
                    <input type="text"
                           style="border-style: solid;
                        border-color: #DDDDDD;
                        border-width:1px;
                        border-left: 0px;
                        border-top-left-radius:0px;
                        border-bottom-left-radius:0px;"
                           name="date" id="dev_date_search" ng-model="dividend.date" class="sm-form-control tleft past-enabled input_search" uib-datepicker-popup="@{{formatForDatePicker}}" is-open="popup.opened" datepicker-options="dateOptions" close-text="Close" ng-click="open()" required />
                </div>
                <div ng-class="{ 'has-error' : dividendForm.date.$invalid && !dividendForm.date.$pristine }">
                    <p ng-show="dividendForm.date.$error.required && !dividendForm.date.$pristine" class="help-block">The date field is required.</p>
                </div>
            </div>

            <div class="form-group" ng-class="{ 'has-error' : (dividendForm.securities.$invalid && !dividendForm.securities.$pristine) || isCurrencyOk }">
                <ui-select name="securities" ng-model="dividend.securities" ng-change="setCurrency(dividend.securities, 'dividend')">
                    <ui-select-match placeholder="Security">
                        <span ng-bind="$select.selected.name"></span>
                    </ui-select-match>
                    <ui-select-choices repeat="item in (allSecurities | filter: $select.search) track by item.id">
                        <span ng-bind="item.name"></span>
                    </ui-select-choices>
                </ui-select>
                <p ng-show="dividendForm.securities.$error.required && !dividendForm.securities.$pristine" class="help-block">The security field is required.</p>
                <p ng-show="isCurrencyOk" class="help-block">The currencies of portfolio, bank account and security are different. Please check.</p>
            </div>

            <div class="form-group">
                <input type="text" name="currency" value="" ng-model="dividend.currency.symbol" placeholder="Currency" ng-disabled="true" />
            </div>

            <div class="form-group" ng-class="{ 'has-error' : dividendForm.lc.$invalid && !dividendForm.lc.$pristine }" ng-if="displayLocalCurrency">
                <input type="tel" ng-model="dividend.local_currency_rate" name="lc" value=""   placeholder="Currency Rate" required ng-pattern="fourDecimalPattern" step="0.01"/>
                <p ng-show="dividendForm.lc.$error.required && !dividendForm.lc.$pristine" class="help-block">The "Currency Rate" field is required.</p>
                <p ng-show="dividendForm.lc.$error.pattern && !dividendForm.lc.$pristine" class="help-block">Only numbers with four decimals or less are allowed (Decimal mark : "<strong ng-bind="decimalChar"></strong>").</p>
            </div>

            <div class="form-group" ng-class="{ 'has-error' : dividendForm.dividend.$invalid && !dividendForm.dividend.$pristine }">
                <input type="tel" ng-model="dividend.dividend" name="dividend" value="" placeholder="Dividend" required ng-pattern="twoDecimalPattern" step="0.01"/>
                <p ng-show="dividendForm.dividend.$error.required && !dividendForm.dividend.$pristine" class="help-block">The dividend value field is required.</p>
                <p ng-show="dividendForm.dividend.$error.pattern && !dividendForm.dividend.$pristine" class="help-block">Only numbers with two decimals or less are allowed (Decimal mark : "<strong ng-bind="decimalChar"></strong>").</p>
            </div>

            <div class="form-group" ng-class="{ 'has-error' : dividendForm.tax.$invalid && !dividendForm.tax.$pristine }">
                <input type="tel" ng-model="dividend.tax" name="tax" value="" placeholder="Tax" ng-pattern="twoDecimalPattern" step="0.01"/>
                <p ng-show="dividendForm.tax.$error.pattern && !dividendForm.tax.$pristine" class="help-block">Only numbers with two decimals or less are allowed (Decimal mark : "<strong ng-bind="decimalChar"></strong>").</p>
            </div>

            <div class="form-group secur_select" ng-class="{ 'has-error' : dividendForm.action.$invalid && !dividendForm.action.$pristine }">
                <select name="action"  class="secur_select opt_select"
                        ng-model="dividend.is_tax_included"
                        required>
                    <option value="" disabled selected>Include Taxes</option>  
                    <option value="1"> Yes </option>
                    <option value="0"> No  </option>
                </select>
            </div>
            <div class="form-group btn_group text-right"> 
                <button type="button" class="btn btn_defoult fst_btn" ng-click="transaction_reset('dividend')">CANCEL</button>
                <button type="submit" class="btn btn_defoult btn_blue" ng-class="{'inactive': dividendForm.$invalid }" name="register-form-submit">SAVE</button>
                <!-- <a ng-click="goToTransactionList($root.portfolio)" ng-show="cashFormSubmitted">Go to Transaction List</a> -->
            </div>

        </form>
@stop
@section('modal_footer')

@stop