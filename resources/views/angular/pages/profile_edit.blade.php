<div class="container-fluid profile">
    <div class="row">
        <div class="fancy-title title-dotted-border">
            <div class="col-md-6">
                <h3 class="bank_ballance_tittle ng-binding">Profile</h3>
                <div class="tag"></div>
            </div>
        </div>
        <form class="nobottommargin ng-pristine ng-valid ng-valid-required ng-valid-email change-user-form" name="changeUser" method="post" ng-submit="changeUserSettings()" >
            <div class="col-md-6">
                <div class="cards_view prof_info">
                    <div class="form-process"></div>
                    <div class="profile_block">
                        <h2 class="profile_block_title">
                        First name
                        </h2>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group inp_grp " ng-class="{ 'has-error' : changeUser.name.$invalid && !changeUser.name.$pristine }">
                                    <input type="text" ng-model="user.name" name="name" value="" placeholder="First Name" required />
                                    <p ng-show="changeUser.name.$invalid && !changeUser.name.$pristine" class="help-block">First name field is required.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="profile_block">
                        <h2 class="profile_block_title">
                        Username
                        </h2>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group inp_grp" ng-class="{ 'has-error' :  changeUser.name.$invalid && !changeUser.name.$pristine }">
                                    <input type="text" ng-model="user.user_name" name="name" value="@{{user.name}}" ng-disabled="true" required placeholder="Username" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="profile_block">
                        <h2 class="profile_block_title">
                        Email
                        </h2>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group inp_grp" ng-class="{ 'has-error' : changeUser.name.$invalid && !changeUser.name.$pristine }">
                                    <input  type="email" ng-model="user.email" ng-model-options="{updateOn: 'blur'}" id="template-contactform-phone" name="email" value="@{{user.email}}" ng-disabled="user.signup_source" placeholder="Email" required>
                                    <p ng-show="changeUser.email.$invalid && !changeUser.email.$pristine" class="help-block">Enter a valid email.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="submit" id="submit-settings" class="hidden" />
                    <div class="profile_block">
                        <h2 class="profile_block_title">
                        Time Zone
                        </h2>
                        <div class="row">
                            <div class="col-md-6">
                                <ui-select style="border-radius: 3px" class="holdings-analysis" id="timezone" ng-model="user.timezone">
                                <ui-select-match placeholder="Search">
                                @{{ $select.selected.text }}
                                </ui-select-match>
                                <ui-select-choices repeat="item in timezones | propsFilter: {text: $select.search, value:$select.search}">
                                <div ng-bind-html="item.text | highlight: $select.search" class=""></div>
                                </ui-select-choices>
                                </ui-select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row" ng-if="!user.signup_source">
                    <div class="btn-group">
                        <div class="row">
                        <label class="col-xs-6 label" for="change_password"> Change Password </label>
                            <div class="col-xs-4">
                                <div class="row">
                                    <label class="col-xs-2" for="change_password">No</label>
                                    <div class="col-xs-2">
                                        <span class="yt-uix-checkbox-on-off">
                                            <input type="checkbox" ng-model="user.change_password" id="change_password" class="ios-switch" name="change_password" />
                                            <label>
                                                  <span class="checked">&nbsp;</span>
                                                  <span class="unchecked"></span>
                                                  <span class="toggle">&nbsp;</span>
                                            </label>
                                        </span>
                                    </div>
                                    <label for="change_password" class="col-xs-2" style="font-weight:normal;margin-left: 18px">Yes</label>
                                </div>
                            </div>
                            <div class="col-xs-2">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cards_view prof_info" ng-if="user.change_password">
                    <div class="profile_block">
                        <h2 class="profile_block_title">
                            Password
                        </h2>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group inp_grp" ng-class="{ 'has-error' : changeUser.password.$invalid && !changeUser.password.$pristine }">
                                    <input ng-if="user.change_password" type="password" ng-model="user.password.old" name="password" value="" placeholder="Old Password" unique-check required/>
                                    <p ng-show="changeUser.password.$error.uniqueMatch && !changeUser.password.$pristine" class="help-block">Sorry, this is not your password.</p>
                                </div>
                                <br/>
                                <div class="form-group inp_grp" ng-class="{ 'has-error' : changeUser.new_password.$invalid && !changeUser.new_password.$pristine }">
                                    <input ng-if="user.change_password" type="password" ng-model="user.password.new" id="new-password" name="new_password" value="" placeholder="New Password" complex-password required/>
                                    <p ng-show="changeUser.new_password.$invalid && changeUser.new_password.$error.complexity && !changeUser.new_password.$pristine" class="help-block">This field is required and must contain min. 8 characters. Min one number and one capital letter.</p>
                                </div>
                                <br/>
                                <div class="form-group inp_grp" ng-class="{ 'has-error' : changeUser.confirm_password.$invalid && !changeUser.confirm_password.$pristine }">
                                    <input ng-if="user.change_password" type="password" ng-model="user.password.confirm" name="confirm_password" value="" placeholder="Repeat new password" pw-check='new-password' required/>
                                    <p ng-show="changeUser.confirm_password.$invalid && !changeUser.confirm_password.$pristine" class="help-block">Passwords do not match.</p>
                                </div>
                                <br/>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group btn_group text-right">
                        <button class="btn edit_btn" ui-sref="panel.profile">CANCEL</button>
                        <label for="submit-settings" class="btn btn-label base_buttons base_btn_blue">UPDATE</label>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="tag"></div>
    <div class="delete_lnk">
        <a href="javascript:void(0);" class="btn" ng-click="deleteModalUserAccount('md')">Delete my account permanently</a>
    </div>
</div>