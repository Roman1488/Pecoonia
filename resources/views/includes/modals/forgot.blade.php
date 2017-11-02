<div class="modal fade forgot-modal-md" tabindex="-1" role="dialog" aria-labelledby="myForgotModalLabel" aria-hidden="true" ng-controller="AuthController">
    <div class="modal-dialog modal-md">
        <div class="modal-body">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3>Reset password</h3>
                </div>
                <div class="modal-body">
                    <div class="panel-body">

                        <form name="forgotForm" class="nobottommargin" method="post" ng-submit="forgotModal()">
                            <div class="col_full" ng-class="{ 'has-error' : forgotForm.email.$invalid && !forgotForm.email.$pristine }">
                                <label for="login-form-username">Email:</label>
                                <input type="email" id="login-form-username" ng-model="forgot_form_email" name="email" value="" class="form-control not-dark" required />
                                <p ng-show="forgotForm.email.$invalid && !forgotForm.email.$pristine" class="help-block">Enter a valid email.</p>
                            </div>

                            <div class="col_full nobottommargin">
                                <button type="submit" ng-disabled="forgot_form_submit_clicked" class="button button-3d nomargin">Submit</button>
                            </div>
                        </form>

                        <div class="line line-sm"></div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>