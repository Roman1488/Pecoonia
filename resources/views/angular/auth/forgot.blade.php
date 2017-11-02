<div class="auth_box forgot_form" ng-controller="AuthController"> 
<span class="form_title text-center">FORGOT PASSWORD</span>
   
    <form ng-submit="forgotModal()" name="forgotForm" class="nobottommargin" method="post">

        <div class="ff_email" ng-class="{ 'has-error' : forgotForm.email.$invalid && !forgotForm.email.$pristine }">
            <input type="email" 
                   id="login-form-username" 
                   ng-model="forgot_form_email" 
                   name="email" value=""  placeholder="Email" 
                   class="forms_field form-control" 
                   required />
            <p ng-show="forgotForm.email.$invalid && !forgotForm.email.$pristine" class="help-block">Enter a valid email.</p>
        </div>

        <div class="login_buttons_form">
            <a href="#" class="btn base_btn_defoult base_buttons link_btn_defoult" role="button">CANCEL</a>         
            <button type="submit" ng-disabled="forgot_form_submit_clicked" class="btn base_buttons base_btn_blue login_btn">SEND</button>
        </div>
    </form>

</div>