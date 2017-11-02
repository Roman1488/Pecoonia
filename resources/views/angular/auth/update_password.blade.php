<div class="auth_box forgot_form update_form" ng-controller="AuthController" ng-if="$root.user && $root.portfolio"> 
   
    <form name="changeUserPassword" class="nobottommargin" method="post" ng-submit="changePassword($event)">

      <span class="form_title">UPDATE PASSWORD</span>
          <div class="ff_old_password" ng-class="{ 'has-error' : changeUserPassword.password.$invalid && !changeUserPassword.password.$pristine }">
              <input class="forms_field form-control" 
                     type="password"
                     ng-model="password.old"
                     name="password" 
                     value="" 
                     placeholder="Old Password" 
                     required unique-check
                     />
              <p ng-show="changeUserPassword.password.$error.uniqueMatch && !changeUserPassword.password.$pristine" class="help-block">Sorry, this is not your password.</p>
          </div>

          <div class="ff_password" ng-class="{ 'has-error' : changeUserPassword.new_password.$invalid && !changeUserPassword.new_password.$pristine }"">
              <input  class="forms_field form-control" 
                      type="password" 
                      ng-model="password.new" 
                      id="new-password" 
                      name="new_password"
                      placeholder="New Password" 
                      value="" 
                      required complex-password
                      />
              <p ng-show="changeUserPassword.new_password.$invalid && changeUserPassword.new_password.$error.complexity && !changeUserPassword.new_password.$pristine" class="help-block">This field is required and must contain min. 8 characters. Min one number and one capital letter.</p>
          </div>

          <div class="ff_confirm_password" ng-class="{ 'has-error' : changeUserPassword.confirm_password.$invalid && !changeUserPassword.confirm_password.$pristine }"">
              <input  class="forms_field form-control"
                      type="password" 
                      ng-model="password.confirm" 
                      name="confirm_password" 
                      value="" 
                      placeholder="Repeat New Password"
                      pw-check='new-password'
                      id="new-password" 
                      required
                      />
              <p ng-show="changeUserPassword.confirm_password.$invalid && !changeUserPassword.confirm_password.$pristine" class="help-block">Passwords do not match.</p>
          </div>

          <div class="login_buttons_form update_psw">
               <a href="#!/create" class="btn btn-md base_btn_defoult base_buttons link_btn_defoult" role="button">CANCEL</a>         
               <button type="submit" class="btn base_buttons base_btn_blue login_btn">UPDATE</button>        
          </div>  
    </form>
</div>