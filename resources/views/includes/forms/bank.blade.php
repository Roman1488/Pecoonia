<form name="bankForm" method="post" ng-submit="createBankFromModal()">

    <div class="form-group secur_select" ng-class="{ 'has-error' : bankForm.name.$invalid && !bankForm.name.$pristine }">
        <input type="text" ng-model="bank.name" name="name" value="" class="secur_select input_placeholder" required ng-maxlength="25" ng-pattern="/^[a-zA-Z0-9 ]*$/" ng-trim="false" placeholder="Bank Name"/>
        <p ng-show="bankForm.name.$error.required && !bankForm.name.$pristine" class="help-block">The name field is required.</p>
        <p ng-show="bankForm.name.$error.maxlength && !bankForm.name.$pristine" class="help-block">Max name length is 25.</p>
        <p ng-show="bankForm.name.$error.pattern && !bankForm.name.$pristine" class="help-block">The field can not have special chars.</p>
    </div>
    <div class="form-group secur_select" ng-class="{ 'has-error' : bankForm.amount.$invalid && !bankForm.amount.$pristine }">
        <input type="text" ng-model="bank.cash_amount" name="amount" value="" class="secur_select input_placeholder" ng-maxlength="25" ng-pattern="twoDecimalPattern" placeholder="Cash Amount" />
        <p ng-show="bankForm.amount.$error.pattern && !bankForm.amount.$pristine" class="help-block">Only numbers with two decimals or less are allowed (Decimal mark : "<strong ng-bind="decimalChar"></strong>").</p>
    </div>
    <div class="form-group secur_select" ng-class="{ 'has-error' : bankForm.currency.$invalid && !bankForm.currency.$pristine }">
        <select name="currency"  class="secur_select opt_select"
                ng-model="bank.currency_id"
                ng-options="item.id as item.symbol for item in currencies"
                required>
            <option value="" disabled selected> Currency </option>
        </select>
        <p ng-show="bankForm.currency.$error.required && !bankForm.currency.$pristine" class="help-block">The currency field is required.</p>
    </div>

    <div class="col_full secur_select" style="margin:auto">
        <div class="btn-group col_full secur_select">
            <div class="row">
            <label class="col-xs-8" for="autoplay-checkbox" style="font-weight:normal;padding-right:0"> Allow Overdraft </label>
                <div class="col-xs-4" style="padding-left:0">
                    <div class="row">
                        <label class="col-xs-2" style="font-weight:normal;" for="autoplay-checkbox">No</label>
                        <div class="col-xs-2">
                            <span class="yt-uix-checkbox-on-off">
                                <input type="checkbox" ng-model="bank.enable_overdraft" id="autoplay-checkbox" class="ios-switch" name="enable_overdraft" />
                                <label>
                                      <span class="checked">&nbsp;</span>
                                      <span class="unchecked"></span>
                                      <span class="toggle">&nbsp;</span>
                                </label>           
                            </span>
                        </div>
                        <label for="autoplay-checkbox" class="col-xs-2" style="font-weight:normal;margin-left: 18px">Yes</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group btn_group text-right">
        <button class="btn base_buttons base_btn_defoult" ng-click="reset('bank')">CANCEL</button>
        <button type="submit" class="btn base_buttons base_btn_blue"  ng-class="{'inactive': bankForm.$invalid }" name="register-form-submit">CREATE</button>  
    </div>
</form> 