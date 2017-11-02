<nav class="navbar navbar_blue">
  <a ng-hide="$root.user" href="http://staging.pecoonia.com" >
    <img class="logo" src="images/Pecoonia_logo_blue_bg.svg" alt="Logo">
    <span class="logo-beta">BETA</span>
  </a>

  <a ng-show="$root.user" href="#!/create" >
    <img class="logo" src="images/Pecoonia_logo_blue_bg.svg" alt="Logo">
    <span class="logo-beta">BETA</span>
  </a>
   <nav id="header-trigger" ng-show="$root.user">
      <div class="hamburger">
        <span>MENU</span>
        <img src="images/menu_bar/icon_nav_2.png" alt="MENU">
        <img src="images/menu_bar/icon_nav_2_blue.png" class="scroled-icon" alt="MENU">
      </div>
  </div>
</nav>
<header id="header"  ng-controller="HeaderController">

  <div id="header-wrap">

        <div class="container clearfix">

            <div id="primary-menu-trigger">
                <div class="hamburger"></div>
            </div>

            <!-- Primary Navigation
            ============================================= -->
            <nav id="primary-menu">
                <ul id="nav_bar" ng-show="$root.user">
                    <li id="inner-header-trigger"><a href="#"><img src="images/menu_bar/icon_nav.png" alt="MENU"></a>
                    </li>
                    <li class="home" ng-class="{'is_selected' : currentState == 'panel.panel.create'}">
                        <a ng-show="$root.user" ui-sref="panel.create" href="#!/create">
                            <img>
                            <span>HOME</span>
                        </a>
                    </li>
                    <li id="dash_id" href="javascript:void(0);" class="list" ng-class="{is_selected: (
                        currentState == 'panel.panel.show.panel.show.portfolio' )}">
                        <a ng-show="$root.portfolio" href="javascript:void(0);" ui-sref="panel.show.portfolio({id: $root.portfolio})" class="dash">
                            <span>
                                <img  alt=" " class="dash-ico">
                                <span>DASHBOARD</span>
                            </span>
                        </a>
                        <a ng-show="!$root.portfolio" href="javascript:void(0);" class="dash menu-image-disable disabled-menu-item">
                            <span>
                                <img alt=" ">
                                <span  class="disabled">DASHBOARD</span>
                            </span>
                        </a>
                    </li>
                    <li ng-show="$root.user" class="list" ng-class="{is_selected: (
                        currentState == 'panel.panel.transactions.panel.transactions.portfolio' )}">
                        <a ng-show="$root.portfolio" class="avaiable_cont" ui-sref="panel.transactions.portfolio({id: $root.portfolio})">
                            <img alt=" ">
                            <span>TRANSACTIONS<span>
                        </a>
                        <a ng-show="!$root.portfolio" href="javascript:void(0);" class="avaiable_cont menu-image-disable disabled-menu-item">
                            <img alt=" ">
                            <span class="disabled">TRANSACTIONS<span>
                        </a>
                    </li>
                    <li ng-show="$root.user" class="bank_ac" ng-class="{is_selected: (
                        currentState == 'panel.panel.show.panel.show.portfolio_banks' )}">
                        <a ng-show="$root.portfolio" class="avaiable_cont" ui-sref="panel.show.portfolio_banks({id: $root.portfolio})">
                            <img alt=" ">
                            <span>BANK ACCOUNTS<span>
                        </a>
                        <a ng-show="!$root.portfolio" href="javascript:void(0);" class="avaiable_cont menu-image-disable disabled-menu-item">
                            <img alt=" ">
                            <span class="disabled">BANK ACCOUNTS<span>
                        </a>
                    </li>
                    <li class="profile_menu" ng-show="$root.user" ng-class="{is_selected: (
                        currentState == 'panel.panel.profile' )}">
                        <a href="#" ui-sref="panel.profile">
                            <img>
                            <span>PROFILE</span>
                        </a>
                    </li>
                    <li class="settings no-animate"  ng-show="$root.user" ng-class="{'is_selected' : currentState == 'panel.panel.settings'}">
                      <a href="#" ui-sref="panel.settings">
                        <img>
                        <span>SETTINGS</span>
                      </a>
                    </li>
                    <li class="not_selected" style="min-height: 200px;"</li>
                    <li ng-show="$root.user" class="not_selected">
                      <a href="#" ng-click="logout()">
                        <img src="images/menu_bar/logout.png" alt="Logout">
                        <span>LOGOUT</span>
                      </a>
                    </li>
                </ul>
            </nav><!-- #primary-menu end -->
        </div>
    </div>

    <div class="pecoonia-spinner" ng-show="$root.loading">
        <div class="pecoonia-spinner-bounce1"></div>
        <div class="pecoonia-spinner-bounce2"></div>
        <div class="pecoonia-spinner-bounce3"></div>
    </div>

    <script type="text/javascript">
        var global_site_url = "{{ env('SITE_URL') }}";
    </script>

</header><!-- #header end -->
