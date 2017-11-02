@extends('angular.forms.modal_container') 
@section('modal_header')
        <a class="close" ng-click="closeModal();" style="position:relative;top:15px;right:15px;padding: 15px;" aria-hidden="true"><img src="images/ios-close-outline.png" alt="close"></a>
        <div class="col_full modal_windows">
            <div class="modal-header">
                <div>NEW CASH TRANSACTION</div>
            </div>
        </div>
@stop
@section('modal_body')
        <form name="cashForm" method="post" ng-submit="cashTransaction()">

            <div class="form-group" ng-class="{ 'has-error' : cashForm.bank.$invalid && !cashForm.bank.$pristine }">

                <ui-select name="bank" ng-model="cash.bank" ng-change="setCurrency(cash.bank, 'cash')" ng-if="displayBankName === false">
                    <ui-select-match placeholder="Bank">
                        <span ng-bind="$select.selected.name"></span>
                    </ui-select-match>
                    <ui-select-choices repeat="item in (banks | filter: $select.search) track by item.id">
                        <span ng-bind="item.name"></span>
                    </ui-select-choices>
                </ui-select>
                <div ng-if="displayBankName !== false" ng-bind="displayBankName"></div>
                <p ng-show="cashForm.bank.$error.required && !cashForm.bank.$pristine" class="help-block">The bank account field is required.</p>
            </div>

            <div class="form-group" ng-class="{ 'has-error' : cashForm.action.$invalid && !cashForm.action.$pristine }">
                <select class="form-group" ng-model="cash.action">
                  <option name="cash.action" value="withdraw">Withdraw</option>
                  <option name="cash.action" value="deposit">Deposit</option>
                    <option value="" disabled selected>Deposit/Withdraw</option>
                </select>
                <p ng-show="cashForm.action.$error.required && !cashForm.action.$pristine" class="help-block">This options is required.</p>
            </div>

            <div class="form-group">
                <div class="input-group">
                    <label for="cash_date_search" class="input-group-addon" style="border-color: #DDDDDD"><i class="icon-calendar3"></i></label>
                    <input type="text"
                           name="date"
                           id="cash_date_search"
                           style="border-style: solid;
                        border-color: #DDDDDD;
                        border-width:1px;
                        border-left: 0px;
                        border-top-left-radius:0px;
                        border-bottom-left-radius:0px;"
                           ng-model="cash.date"
                           class="sm-form-control tleft past-enabled ng-pristine ng-valid ng-isolate-scope ng-not-empty ng-valid-date ng-valid-required ng-touched" uib-datepicker-popup="@{{formatForDatePicker}}"  uib-datepicker-popup="MM-dd-yyyy" is-open="popup.opened" datepicker-options="dateOptions" close-text="Close" ng-click="open()" placeholder="Date" required
                    />

                    <div uib-datepicker-popup-wrap="" ng-model="date" ng-change="dateSelection(date)" template-url="uib/template/datepickerPopup/popup.html" class="ng-pristine ng-untouched ng-valid ng-scope ng-not-empty ng-valid-date-disabled"><!-- ngIf: isOpen -->
                    </div>
                </div>
                <div ng-class="{ 'has-error' : cashForm.date.$invalid && !cashForm.date.$pristine }">
                    <p ng-show="cashForm.date.$error.required && !cashForm.date.$pristine" class="help-block">The date field is required.</p>
                </div>
            </div>

            <div class="form-group" ng-class="{ 'has-error' : cashForm.amount.$invalid && !cashForm.amount.$pristine }">
                <input type="tel" ng-model="cash.amount" name="amount" value="" class="sm-form-control" required placeholder="Amount" ng-maxlength="25" ng-pattern="twoDecimalPattern"  step="0.01"/>
                <p ng-show="cashForm.amount.$error.required && !cashForm.amount.$pristine" class="help-block">The cash amount field is required.</p>
                <p ng-show="cashForm.amount.$error.pattern && !cashForm.amount.$pristine" class="help-block">Only numbers with two decimals or less are allowed (Decimal mark : "<strong ng-bind="decimalChar"></strong>").</p>
            </div>

            <div class="form-group">
                <input type="text" name="currency" value="" ng-model="cash.currency.symbol" ng-disabled="true" placeholder="Currency" />
            </div>

            <div class="form-group" ng-class="{ 'has-error' : cashForm.text.$invalid && !cashForm.text.$pristine }">
                <input ng-model="cash.text" name="text" ng-maxlength="25" placeholder="Text">
                <p ng-show="cashForm.text.$error.maxlength && !cashForm.text.$pristine" class="help-block">Max Text length is 25.</p>
            </div>

            <div class="form-group btn_group text-right"> 
                <button type="button" class="btn btn_defoult fst_btn" ng-click="transaction_reset('cash')">CANCEL</button>
                <button type="submit" class="btn btn_defoult btn_blue" ng-class="{'inactive': cashForm.$invalid }" name="register-form-submit">SAVE</button>
                <!-- <a ng-click="goToTransactionList($root.portfolio)" ng-show="cashFormSubmitted">Go to Transaction List</a> -->
            </div>

        </form>
@stop
@section('modal_footer')

@stop