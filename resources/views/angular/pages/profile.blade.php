<div class="container-fluid profile">
    <div class="row">
        <div class="fancy-title title-dotted-border">
            <div class="col-md-6">
                <h3 class="bank_ballance_tittle ng-binding title_bold">Profile</h3>
                <div class="tag"></div>
            </div>
        </div>
        <div class="col-md-12">

            <div class="cards_view prof_info">
                <div class="profile_block">
                    <h2 class="profile_block_title">
                        First name
                    </h2>
                    <div class="profile_block_content">
                        @{{user.name}}
                    </div>
                </div>
                <div class="profile_block">
                    <h2 class="profile_block_title">
                        Username
                    </h2>
                    <div class="profile_block_content">
                        @{{user.user_name}}
                    </div>
                </div>
                <span ng-if="(user.signup_source)" class="signup-source"> @{{ "Signed Up using " + (user.signup_source | capitalize) }} </span>
                <div class="profile_block">
                    <h2 class="profile_block_title">
                        Email
                    </h2>
                    <div class="profile_block_content">
                        @{{user.email}}
                    </div>
                </div>
                <div class="profile_block" ng-if="(user.timezone)">
                    <h2 class="profile_block_title">
                        Time Zone
                    </h2>
                    <div class="profile_block_content">
                        @{{user.timezone.text}}
                    </div>
                </div>
                <div class="profile_block">
                    <h2 class="profile_block_title">
                       Password
                    </h2>
                    <div class="profile_block_content">
                        ************ <br>Last updated: @{{user.password_last_changed_at}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="form-group btn_group text-right col-md-6">
        <button class="btn edit_btn" ui-sref="panel.profile_edit">EDIT</button>
    </div>
    <div class="tag"></div>
    <div class="delete_lnk">
        <a href="javascript:void(0);" class="btn" ng-click="deleteModalUserAccount('md')">Delete my account permanently</a>
    </div>
</div>
