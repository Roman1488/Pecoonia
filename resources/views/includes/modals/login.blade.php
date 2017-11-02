<div class="modal fade login-modal-md" tabindex="-1" role="dialog" aria-labelledby="myLoginModalLabel" aria-hidden="true" ng-controller="AuthController">
    <div class="modal-dialog modal-md">
        <div class="modal-body">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3>Login to your Account</h3>
                </div>
                <div class="modal-body">
                    <div class="panel-body" style="padding: 40px;">

                        @include('includes.forms.login')

                        <div class="line line-sm"></div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>