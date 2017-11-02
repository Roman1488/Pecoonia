<section id="content">

    <div class="content-wrap">

        <div class="container clearfix">

            <div class="auth_box login_form" ng-controller="AuthController" ng-if="!$root.user">

                <div class="form_group top-50 right-50">
                    <div class="form_title ignore-positions ignore-spacing">Reset Password</div>
                    <br>
                    <form name="resetPwdForm" class="form-horizontal nobottommargin" role="form" method="post" ng-submit="resetPassword()">
                        <input type="hidden" name="token" ng-model="data.resetPwd.token">
                        <div class="m-b-30" ng-class="{ 'has-error' : resetPwdForm.email.$invalid && !resetPwdForm.email.$pristine }">

                            <input id="email" type="email" ng-model="data.resetPwd.email" name="email" class="form-control forms_field full-width" required placeholder="Email Address" />
                            <p ng-show="resetPwdForm.email.$invalid && !resetPwdForm.email.$pristine" class="help-block">Enter a valid email.</p>
                        </div>

                        <div class="m-t-30 m-b-30" ng-class="{ 'has-error' : resetPwdForm.password.$invalid && !resetPwdForm.password.$pristine }">

                            <input type="password" ng-model="data.resetPwd.password" id="password" name="password" value="" class="form-control forms_field full-width" required complex-password  placeholder="Choose Password:" />
                            <p ng-show="resetPwdForm.password.$invalid && resetPwdForm.password.$error.complexity && !resetPwdForm.password.$pristine" class="help-block">This field is required and must contain min. 8 characters. Min one number and one capital letter.</p>
                        </div>

                        <div class="m-t-30 m-b-30" ng-class="{ 'has-error' : resetPwdForm.password_confirmation.$invalid && !resetPwdForm.password_confirmation.$pristine }">

                            <input id="password_confirmation" type="password" ng-model="data.resetPwd.password_confirmation" name="password_confirmation" value="" class="form-control forms_field full-width" pw-check='password' required placeholder="Re-enter Password:" />
                            <p ng-show="resetPwdForm.password_confirmation.$invalid && !resetPwdForm.password_confirmation.$pristine" class="help-block">Passwords do not match.</p>
                        </div>

                        <div class="form-group">
                            <div class="col-md-6 pull-right">
                                <button type="submit" class="btn base_buttons base_btn_blue">Reset Password</button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>

            <div class="clear"></div>

        </div>

    </div>

</section>
<!-- #content end -->