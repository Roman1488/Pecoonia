@extends('angular.forms.modal_container')
    @section('modal_header')
    <a class="close" ng-click="closeModal()" style="position:relative;top:15px;right:15px;padding: 15px;"  aria-hidden="true"><img src="images/ios-close-outline.png" alt="close"></a>
    <div class="col_full modal_windows">
        <div class="modal-header">
            <div>NEW BOOK VALUE TRANSACTION</div>
        </div>
    </div>
    @stop

@section('modal_body')
        <form name="bookValueForm" method="post" ng-submit="bookValueTransaction()">

            <div class="form-group" ng-class="{ 'has-error' : (bookValueForm.securities.$invalid && !bookValueForm.securities.$pristine) || isCurrencyOk }">
                <ui-select name="securities" ng-model="bookValue.securities" ng-change="setCurrency(bookValue.securities, 'bookValue'); fetchBookValueQuantity();">
                    <ui-select-match placeholder="Security">
                        <span ng-bind="$select.selected.name"></span>
                    </ui-select-match>
                    <ui-select-choices repeat="item in (allSecurities | filter: $select.search) track by item.id">
                        <span ng-bind="item.name"></span>
                    </ui-select-choices>
                </ui-select>
                <p ng-show="bookValueForm.securities.$error.required && !bookValueForm.securities.$pristine" class="help-block">The security field is required.</p>
                <p ng-show="isCurrencyOk" class="help-block">The currencies of portfolio, bank account and security are different. Please check.</p>
            </div>

            <div class="form-group">
                <input type="text" placeholder="Currency" name="currency" value="" ng-model="bookValue.currency.symbol" ng-disabled="true" />
            </div>

            <div class="form-group">

                <div class="input-group">
                    <label for="book_date_search" class="input-group-addon" style="border-color: #DDDDDD"><i class="icon-calendar3"></i></label>
                    <input type="text"
                           style="border-style: solid;
                        border-color: #DDDDDD;
                        border-width:1px;
                        border-left: 0px;
                        border-top-left-radius:0px;
                        border-bottom-left-radius:0px;"
                           name="date" id="book_date_search" ng-model="bookValue.date" class="sm-form-control tleft past-enabled" uib-datepicker-popup="@{{formatForDatePicker}}" is-open="popup.opened" datepicker-options="dateOptions" close-text="Close" ng-click="open()" ng-change="fetchBookValueQuantity();" required />
                </div>
                <div ng-class="{ 'has-error' : bookValueForm.date.$invalid && !bookValueForm.date.$pristine }">
                    <p ng-show="bookValueForm.date.$error.required && !bookValueForm.date.$pristine" class="help-block">The date field is required.</p>
                </div>

            </div>

            <div class="form-group" ng-class="{ 'has-error' : bookValueForm.lc.$invalid && !bookValueForm.lc.$pristine }" ng-if="displayLocalCurrency">
                <input type="tel" ng-model="bookValue.local_currency_rate" name="lc" value="" placeholder="local Currency" required ng-pattern="fourDecimalPattern" step="0.01" />
                <p ng-show="bookValueForm.lc.$error.required && !bookValueForm.lc.$pristine" class="help-block">The local currency field is required.</p>
                <p ng-show="bookValueForm.lc.$error.pattern && !bookValueForm.lc.$pristine" class="help-block">Only numbers with four decimals or less are allowed (Decimal mark : "<strong ng-bind="decimalChar"></strong>").</p>
            </div>

            <div class="form-group" ng-class="{ 'has-error' : bookValueForm.quantity.$invalid && !bookValueForm.quantity.$pristine }">
                <input type="tel" ng-model="bookValue.quantity" name="quantity" value="" placeholder="Quantity" ng-pattern="/^\d+$/" ng-disabled="true" />
                <p ng-show="bookValueForm.quantity.$error.required && !bookValueForm.quantity.$pristine" class="help-block">The quantity field is required.</p>
                <p ng-show="bookValueForm.quantity.$error.pattern && !bookValueForm.quantity.$pristine" class="help-block">The quantity field can have only counting number.</p>
            </div>


            <div class="form-group" ng-class="{ 'has-error' : bookValueForm.bookValue.$invalid && !bookValueForm.bookValue.$pristine }">
                <input type="tel" ng-model="bookValue.book_value" name="bookValue" value="" placeholder="Book Value" required ng-pattern="twoDecimalPattern" step="0.01"/>
                <p ng-show="bookValueForm.bookValue.$error.required && !bookValueForm.bookValue.$pristine" class="help-block">The book value field is required.</p>
                <p ng-show="bookValueForm.bookValue.$error.pattern && !bookValueForm.bookValue.$pristine" class="help-block">Only numbers with two decimals or less are allowed (Decimal mark : "<strong ng-bind="decimalChar"></strong>").</p>
            </div>

            <div class="form-group btn_group text-right"> 
                <button type="button" class="btn btn_defoult fst_btn" ng-click="transaction_reset('bookValue')">CANCEL</button>
                <button type="submit" class="btn btn_defoult btn_blue" ng-class="{'inactive': bookValueForm.$invalid }" name="register-form-submit">SAVE</button>
                <!-- <a ng-click="goToTransactionList($root.portfolio)" ng-show="bookValueFormSubmitted">Go to Transaction List</a> -->
            </div>

        </form>
@stop
@section('modal_footer')

@stop