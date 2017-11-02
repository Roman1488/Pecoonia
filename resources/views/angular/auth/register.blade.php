
<div class="auth_box create_form" ng-controller="AuthController">
    <div class="form_group top-50">
        <div class="form_title ignore-positions ignore-spacing text-left">
            CREATE ACCOUNT
        </div>

        <p class="gray-text text-left">Sign Up using your Facebook or Google account.</p>

        <div class="">
            <a class="btn btn-social btn-facebook btn-inline"  ng-click="openFacebookPopUp('signUp')">
                <i class="icon-large icon-facebook"></i> Facebook
            </a>
            <a class="btn btn-social btn-google-plus btn-inline btn-right" ng-click="openGooglePopUp('signUp')">
                <i class="icon-large icon-google-plus"></i> Google
            </a>
        </div>
        <hr>
        <p class="header-login gray-text">Or Sign Up using your email address.</p>
        <form name="registerForm" class="nobottommargin" method="post" ng-submit="register()">

            <div class="col_full defoult_field_margin" ng-class="{ 'has-error' : registerForm.name.$invalid && !registerForm.name.$pristine }">
                <input type="text" ng-model="data.register.name"
                    name="name" value=""
                    placeholder="First Name"
                    class="forms_field form-control"  required
                    ng-maxlength="15"
                />
                <p ng-show="registerForm.name.$error.required && !registerForm.name.$pristine" class="help-block">The Name field is required.</p>
                <p ng-show="registerForm.name.$error.maxlength && !registerForm.name.$pristine" class="help-block">Max name length is 15.</p>
            </div>

            <div class="col_full defoult_field_margin" ng-class="{ 'has-error' : registerForm.user_name.$invalid && !registerForm.user_name.$pristine }">
                <input type="text" ng-model="data.register.user_name"
                    name="user_name" value=""
                    placeholder="User Name"
                    class="forms_field form-control"  required
                    ng-pattern="/^[a-zA-Z0-9]*$/"
                    ng-maxlength="15" unique-check
                />
                <p ng-show="registerForm.user_name.$error.required && !registerForm.user_name.$pristine" class="help-block">User Name field is required.</p>
                <p ng-show="registerForm.user_name.$error.maxlength && !registerForm.user_name.$pristine" class="help-block">Max name length is 15.</p>
                <p ng-show="registerForm.user_name.$error.uniqueMatch && !registerForm.user_name.$pristine" class="help-block">Sorry, this username is already taken.</p>
                <p ng-show="registerForm.user_name.$error.pattern && !registerForm.user_name.$pristine" class="help-block">User Name can only contain alphabets and/or numbers.</p>
            </div>

            <div class="clear"></div>

            <div class="col_full defoult_field_margin" ng-class="{ 'has-error' : registerForm.email.$invalid && !registerForm.email.$pristine }">
                <input id="register-form-email" type="email"
                    ng-model="data.register.email"
                    name="email" value=""
                    placeholder="Email"
                    class="forms_field form-control" required
                />
                <p ng-show="registerForm.email.$invalid && !registerForm.email.$pristine" class="help-block">Enter a valid email.</p>
            </div>

            <div class="col_full defoult_field_margin" ng-class="{ 'has-error' : registerForm.reEmail.$invalid && !registerForm.reEmail.$pristine }">
                <input type="email" ng-model="data.register.reemail"
                    name="reEmail" value=""
                    placeholder="Repeat Email"
                    class="forms_field form-control"
                    required
                    pw-check='register-form-email'
                />
                <p ng-show="registerForm.reEmail.$invalid && !registerForm.reEmail.$pristine" class="help-block">Emails do not match.</p>
            </div>

            <div class="clear"></div>

            <div class="col_full defoult_field_margin" ng-class="{ 'has-error' : registerForm.password.$invalid && !registerForm.password.$pristine }">
                <input type="password" ng-model="data.register.password"
                    id="register-password"
                    placeholder="Password"
                    name="password" value=""
                    class="forms_field form-control"
                    required
                    complex-password
                />
                <p ng-show="registerForm.password.$invalid && registerForm.password.$error.complexity && !registerForm.password.$pristine" class="help-block">This field is required and must contain min. 8 characters. Min one number and one capital letter.</p>
            </div>

            <div class="col_full defoult_field_margin" ng-class="{ 'has-error' : registerForm.rePassword.$invalid && !registerForm.rePassword.$pristine }">
                <input type="password" ng-model="data.register.repassword"
                    name="rePassword" value=""
                    placeholder="Repeat Password"
                    class="forms_field form-control"
                    pw-check='register-password' required
                />
                <p ng-show="registerForm.rePassword.$invalid && !registerForm.rePassword.$pristine" class="help-block">Passwords do not match.</p>
            </div>

            <div class="clear"></div>

             <div class="forms_field form-control defoult_field_margin" style="padding:0; border:0px" ng-class="{ 'has-error' : registerForm.timezone.$invalid && registerForm.timezone.$dirty}">
                <ui-select class="holdings-analysis" name="timezone" id="timezone" ng-model="data.register.timezone" required>
                    <ui-select-match placeholder="Time Zone">
                        @{{ $select.selected.text }}
                    </ui-select-match>
                    <ui-select-choices repeat="item in timezones | propsFilter: {text: $select.search, value:$select.search}">
                        <div ng-bind-html="item.text | highlight: $select.search"></div>
                    </ui-select-choices>
                </ui-select>
                <p ng-show="registerForm.timezone.$invalid && registerForm.timezone.$dirty" class="help-block">Please select timezone.</p>
            </div>

            <div class="defoult_field_margin">
                <input type="checkbox" name="terms_of_service" id="terms_of_service"  oninvalid="setCustomValidity('You must accept the Terms of Service to create an account. Please mark the check box.')"
                    onchange="try{setCustomValidity('')}catch(e){}" required="" />
                <span>&nbsp;&nbsp;<span ng-click="openTermsOfUseModal()" style="cursor: pointer;"><u>Terms of Use</u></span></span>
            </div>
            <br>
            <div class="register_buttons_form">
                 <a href="#" class="btn btn-md base_btn_defoult base_buttons link_btn_defoult" role="button">CANCEL</a>
                 <button type="submit" name="register-form-submit" value="submit" class="btn base_buttons base_btn_blue login_btn">REGISTER</button>
            </div>
            <!-- <button type="button"  class="button button-3d button-rounded" ng-click="reset()">Reset</button> -->
        </form>
    </div>
</div>
