<div class="form_group top-50">
    <p ng-show="isInactivityLogout" class="help-block">You have been logged out automatically due to inactivity.</p>

    <div class="form_title ignore-positions ignore-spacing">LOGIN</div>
    <br>
    <div>
        <a class="btn btn-social btn-facebook btn-inline"  ng-click="openFacebookPopUp('login')">
            <i class="icon-large icon-facebook"></i> Facebook
        </a>
        <a class="btn btn-social btn-google-plus btn-inline btn-right" ng-click="openGooglePopUp('login')">
            <i class="icon-large icon-google-plus"></i> Google
        </a>
        <hr>
    </div>
    <form name="loginForm" method="post" ng-submit="loginModal()">
        <div ng-class="{ 'has-error' : loginForm.username.$invalid && !loginForm.username.$pristine }">
            <input class="forms_field form-control"
                   type="text"
                   id="login-form-username"
                   ng-model="data.login.user_name"
                   name="username" value=""
                   placeholder="Username"
                   required />
            <p ng-show="loginForm.username.$invalid && !loginForm.username.$pristine" class="help-block">The username field is required.</p>
        </div>

        <div class="m-t-30 m-b-30" ng-class="{ 'has-error' : loginForm.password.$invalid && !loginForm.password.$pristine }">
            <input  class="forms_field form-control"
                    type="password"
                    id="login-form-password"
                    ng-model="data.login.password"
                    name="password" value=""
                    placeholder="Password"
                    required />
            <p ng-show="loginForm.password.$invalid && !loginForm.password.$pristine" class="help-block">The password field is required.</p>
        </div>
        <div class="m-b-30">
            <label>
                <input class="checkbox_squere" type="checkbox" name="remember" ng-model="data.login.remember" ng-checked="expression">
                <span class="is_stay_online">Stay Logged In</span>
            </label>
            <span class="forgot_psw_lnk"><a href="#!/forgot" style="color:#2E9BDE" class="fright">Forgot password?</a></span>
        </div>
        <div>
             <a href="#!/register" class="btn btn-md base_btn_defoult base_buttons link_btn_defoult" role="button">SIGN UP</a>
             <button type="submit" class="btn base_buttons base_btn_blue login_btn">LOGIN</button>
        </div>
    </form>
</div>