<form class="nobottommargin" name="changeUser" method="post">

    <div class="form-process"></div>

    <div class="col_two_third" ng-class="{ 'has-error' : changeUser.name.$invalid && !changeUser.name.$pristine }">
        <label for="template-contactform-name">First Name <small>*</small></label>
        <input type="text" ng-model="user.name" ng-change="changeUserSettings()" name="name" value="" class="sm-form-control required" required />
        <p ng-show="changeUser.name.$invalid && !changeUser.name.$pristine" class="help-block">First name field is required.</p>
    </div>

    <div class="col_one_third col_last">
        <label for="template-contactform-email">Username <small>*</small></label>
        <input type="text" id="template-contactform-email" name="username" value="" class="required email sm-form-control" ng-model="user.user_name" ng-disabled="true" />
    </div>

    <div class="clear"></div>

    <div class="col_half" ng-class="{ 'has-error' : changeUser.email.$invalid && !changeUser.email.$pristine }">
        <label for="template-contactform-phone">Email</label>
        <input type="email" ng-model="user.email" ng-change="changeUserSettings()" ng-model-options="{updateOn: 'blur'}" id="template-contactform-phone" name="email" value="" class="sm-form-control" required />
        <p ng-show="changeUser.email.$invalid && !changeUser.email.$pristine" class="help-block">Enter a valid email.</p>
    </div>

</form>
<form name="changeUserPassword" method="post" ng-submit="changePassword($event)">

    <div class="col_half col_last" ng-class="{ 'has-error' : changeUserPassword.password.$invalid && !changeUserPassword.password.$pristine }">
        <label for="template-contactform-phone">Current Password</label>
        <input type="password" ng-model="password.old" name="password" value="" class="sm-form-control" required unique-check />
        <p ng-show="changeUserPassword.password.$error.uniqueMatch && !changeUserPassword.password.$pristine" class="help-block">Sorry, this is not your password.</p>
    </div>

    <div class="clear"></div>

    <div class="col_half" ng-class="{ 'has-error' : changeUserPassword.new_password.$invalid && !changeUserPassword.new_password.$pristine }">
        <label for="template-contactform-phone">New Password</label>
        <input type="password" ng-model="password.new" id="new-password" name="new_password" value="" class="sm-form-control" required complex-password />
        <p ng-show="changeUserPassword.new_password.$invalid && changeUserPassword.new_password.$error.complexity && !changeUserPassword.new_password.$pristine" class="help-block">This field is required and must contain min. 8 characters. Min one number and one capital letter.</p>
    </div>

    <div class="col_half col_last" ng-class="{ 'has-error' : changeUserPassword.confirm_password.$invalid && !changeUserPassword.confirm_password.$pristine }">
        <label for="template-contactform-phone">Confirm new password</label>
        <input type="password" ng-model="password.confirm" name="confirm_password" value="" class="sm-form-control" pw-check='new-password' required />
        <p ng-show="changeUserPassword.confirm_password.$invalid && !changeUserPassword.confirm_password.$pristine" class="help-block">Passwords do not match.</p>
    </div>

    <div class="clear"></div>

    <div class="col_full">
        <button class="button button-3d nomargin" type="submit" id="template-contactform-submit" name="template-contactform-submit" value="submit">Change Password</button>
    </div>

    <div class="col_half">
        <label for="timezone">Choose TimeZone</label>
        <ui-select class="holdings-analysis" id="timezone" ng-model="user.timezone" ng-change="changeUserSettings()">
            <ui-select-match placeholder="Search">
                @{{ $select.selected.text }}
            </ui-select-match>
            <ui-select-choices repeat="item in timezones | propsFilter: {text: $select.search, value:$select.search}">
                <div ng-bind-html="item.text | highlight: $select.search" class=""></div>
            </ui-select-choices>
        </ui-select>
    </div>
    <br /><br /><br />
</form>