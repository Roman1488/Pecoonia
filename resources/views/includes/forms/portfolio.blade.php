<form name="portfolioForm" method="post" ng-submit="createPortfolio()">

    <div class="form-group secur_select" ng-class="{ 'has-error' : portfolioForm.name.$invalid && !portfolioForm.name.$pristine }">
        <input type="text" ng-model="portfolio.name" name="name" value="" class="secur_select input_placeholder" required ng-maxlength="15" ng-pattern="/^[a-zA-Z0-9]*$/" ng-trim="false" placeholder="Name"/>
        <p ng-show="portfolioForm.name.$error.required && !portfolioForm.name.$pristine" class="help-block">The name field is required.</p>
        <p ng-show="portfolioForm.name.$error.maxlength && !portfolioForm.name.$pristine" class="help-block">Max name length is 15.</p>
        <p ng-show="portfolioForm.name.$error.pattern && !portfolioForm.name.$pristine" class="help-block">The field can not have whitespaces.</p>
    </div>

    <div class="form-group secur_select" ng-class="{ 'has-error' : portfolioForm.currency.$invalid && !portfolioForm.currency.$pristine }">
        <select name="currency"  class="secur_select opt_select"
                ng-model="portfolio.currency_id"
                ng-options="item.id as item.symbol for item in currencies"
                required>
            <option value="" disabled selected> Currency </option>
        </select>
        <p ng-show="portfolioForm.currency.$error.required && !portfolioForm.currency.$pristine" class="help-block">The currency field is required.</p>
    </div>

    <div class="form-group secur_select" ng-class="{ 'has-error' : portfolioForm.date_format.$invalid && !portfolioForm.date_format.$pristine }">
        <select name="date_format"  class="secur_select opt_select"
                ng-model="portfolio.date_format"
                required>
        <option value="" disabled selected>Date Format</option>
        <option  value="0"> @{{ $root.dateFormat[0] }} </option>
        <option  value="1"> @{{ $root.dateFormat[1] }} </option>
        </select>
        <!-- <p ng-show="portfolioForm.date_format.$error.required && !portfolioForm.date_format.$pristine" class="help-block">The date format is required.</p> -->
    </div>

    <div class="form-group secur_select" ng-class="{ 'has-error' : portfolioForm.comma_separator.$invalid && !portfolioForm.comma_separator.$pristine }">
        <select name="comma_separator"  class="secur_select opt_select"
                ng-model="portfolio.comma_separator" required>
                <option value="" disabled selected>Decimal Mark</option>  
                <option value="0"> , </option>
                <option value="1"> . </option>
        </select>
        <!-- <p ng-show="portfolioForm.comma_separator.$error.required && !portfolioForm.comma_separator.$pristine" class="help-block">The comma separator field is required.</p> -->
    </div>

    <div class="form-group secur_select" ng-class="{ 'has-error' : portfolioForm.is_company.$invalid && !portfolioForm.is_company.$pristine }">
        <select name="is_company"  class="secur_select opt_select"
                ng-model="portfolio.is_company" required>
                <option value="" disabled selected> Type </option>  
                <option value="0"> Private </option>
                <option value="1"> Company </option>
        </select>
        <!-- <p ng-show="portfolioForm.is_company.$error.required && !portfolioForm.is_company.$pristine" class="help-block">The type company field is required.</p> -->
    </div>
    <div class="form-group btn_group text-right">
       <!--  <button class="btn base_buttons base_btn_defoult" data-dismiss="modal">CANCEL</button> -->
       <button class="btn base_buttons base_btn_defoult" ng-click="reset('portfolio')">CANCEL</button>
        <button type="submit" class="btn base_buttons base_btn_blue">CREATE</button>  
    </div>

</form>