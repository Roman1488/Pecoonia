<!-- Content
		============================================= -->
<section id="content">

    <div class="content-wrap">

        <div class="">

            <div class="row clearfix">

                <div class="col-sm-12">

                    {{--<img src="{{asset('images/icons/avatar.jpg')}}" class="alignleft img-circle img-thumbnail notopmargin nobottommargin" alt="Avatar" style="max-width: 84px;">--}}

                    {{--<div class="heading-block noborder">--}}
                        {{--<h3>@{{ $root.user.name }}</h3>--}}
                        {{--<span>Your Profile</span>--}}
                    {{--</div>--}}

                    <div class="clear"></div>

                    <div class="row clearfix">

                        <div class="col-md-12">

                            <div ng-controller="AlertController">
                                <div uib-alert ng-repeat="alert in $root.alerts" class="style-msg2" ng-class="alert.type || 'errormsg'" close="closeAlert($index)">
                                    <div class="msgtitle"><i class="icon-info-sign"></i> @{{ alert.title || 'Information box.' }}</div>
                                    <div class="sb-msg"><span compile="alert.msg"></span></div>
                                </div>
                            </div>

                            <md-content ui-view></md-content>

                        </div>

                    </div>

                </div>

                <div class="line visible-xs-block"></div>

                {{--<div class="col-sm-3 clearfix">--}}

                    {{--<div class="list-group">--}}
                        {{--<a href="#" ui-sref="panel.create" class="list-group-item clearfix">Create <i class="icon-pencil2 pull-right"></i></a>--}}
                        {{--<a href="#" ui-sref="panel.settings" class="list-group-item clearfix">Settings <i class="icon-cog pull-right"></i></a>--}}
                        {{--<a href="#" class="list-group-item clearfix" ng-click="$root.$emit('auth:logout')">Logout <i class="icon-signout pull-right"></i></a>--}}
                    {{--</div>--}}

                    {{--<div class="fancy-title topmargin title-border">--}}
                        {{--<h4>Social Profiles</h4>--}}
                    {{--</div>--}}

                    {{--<a href="#" class="social-icon si-facebook si-small si-rounded si-light" title="Facebook">--}}
                        {{--<i class="icon-facebook"></i>--}}
                        {{--<i class="icon-facebook"></i>--}}
                    {{--</a>--}}

                    {{--<a href="#" class="social-icon si-gplus si-small si-rounded si-light" title="Google+">--}}
                        {{--<i class="icon-gplus"></i>--}}
                        {{--<i class="icon-gplus"></i>--}}
                    {{--</a>--}}

                    {{--<a href="#" class="social-icon si-twitter si-small si-rounded si-light" title="Twitter">--}}
                        {{--<i class="icon-twitter"></i>--}}
                        {{--<i class="icon-twitter"></i>--}}
                    {{--</a>--}}

                {{--</div>--}}

            </div>

        </div>

    </div>

</section><!-- #content end -->