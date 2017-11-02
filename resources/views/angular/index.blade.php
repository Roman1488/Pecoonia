<section id="content">

    <div class="content-wrap">

        <div class="container clearfix">

            <div class="auth_box login_form" ng-controller="AuthController" ng-if="!$root.user">

                @include('includes.forms.login')

            </div>

            <div class="clear"></div>

        </div>

    </div>

</section>
<!-- #content end -->