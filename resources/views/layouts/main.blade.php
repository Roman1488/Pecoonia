<!DOCTYPE html>
<html dir="ltr" lang="en-US" ng-app="PecooniaApp">
<head>

    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="author" content="PecooniaWeb" />

    <!-- Stylesheets
    ============================================= -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans|Lato" rel="stylesheet">
    <link rel="stylesheet" href="{!! asset('vendor/css/final.min.css') !!}" type="text/css" />
    <link rel="stylesheet" href="{!! asset('css/font-icons.css') !!}" type="text/css" />
    {{--<link rel="stylesheet" href="{!! asset('vendor/css/new_styles.css') !!}" type="text/css" />--}}
    <!-- Other Stylesheets
    ============================================= -->
    @yield('styles')

    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="images/favicons/favicon.png" type="image/png">
    <!-- Document Title
    ============================================= -->
    <title>Pecoonia | Making you a Better Investor</title>

</head>

<body class="stretched side-header open-header close-header-on-scroll" style="padding:0px">

<!-- Document Wrapper
============================================= -->
<div id="wrapper" class="clearfix fadeOut">

    <!-- Header
    ============================================= -->
    @include('includes.header')
    <!-- Modal -->
    <div id="new_transaction" class="modal fade" role="dialog">

        <!-- Modal content-->
        <div class="modal-content modal_windows">
        <a class="close" style="position:relative;top:30px;right:30px" data-dismiss="modal" aria-hidden="true"><img src="images/ios-close-outline.png" alt="close"></a>
          <div class="modal-header" style="border-width: 0px">

            <p class="secur_header">
                CHOOSE TRANSACTION TYPE
            </p>
          </div>
          <div class="modal-body" fab-list>
                <div class="row">

                    <div class="fab-buttons col-sm-6" data-fabbtn="securities"  href="javascript:void(0);">
                        <div>
                            <img src="images/transaction/security_transaction.svg" class="transaction-image">
                        </div>
                        <div>
                            <a class="btn btn-default">
                                <img src="images/transaction/security_transaction.svg" class="transaction-icon">
                                <span> SECURITY TRANSACTION </span>
                            </a>
                        </div>
                    </div>

                    <div class="fab-buttons col-sm-6" data-fabbtn="dividend"  href="javascript:void(0);">
                        <div>
                            <img src="images/transaction/divident_transaction.svg" class="transaction-image">
                        </div>
                        <div>
                            <a class="btn btn-default" >
                                <img src="images/transaction/divident_transaction.svg" class="transaction-icon">
                                <span> DIVIDEND TRANSACTION </span>
                            </a>
                        </div>
                    </div>

                    <div class="fab-buttons col-sm-6" data-fabbtn="cash"  href="javascript:void(0);">
                        <div>
                            <img src="images/transaction/book_value_transaction.svg" class="transaction-image">
                        </div>
                        <div>
                            <a class="btn btn-default">
                                <img src="images/transaction/book_value_transaction.svg" class="transaction-icon">
                                <span> CASH TRANSACTION </span>
                            </a>
                        </div>
                    </div>

                    <div class="fab-buttons col-sm-6"  data-fabbtn="book_value"  href="javascript:void(0);" ng-class="($root.portfolioObj.is_company != 1) ? 'disabled-button' : '' ";>
                        <div>
                            <img src="images/transaction/cash_transaction.svg" class="transaction-image">
                        </div>
                        <div>
                            <a class="btn btn-default">
                                <img src="images/transaction/cash_transaction.svg" class="transaction-icon">
                                <span> BOOK VALUE TRANSACTION </span>
                            </a>
                        </div>
                    </div>
                </div>
          </div>
         <!--  <div class="modal-footer text-right">
            <button type="button" class="btn btn-default form_btn" data-dismiss="modal">CANCEL</button>
          </div> -->
        </div>


    </div>
    <div class="container-fluid clearfix sub_header" ng-show="$root.user">
        <div class="row">
            <div class="col-sm-8 sitepath-wrap">
                <div ncy-breadcrumb></div>
            </div>
            <div class="text-right button_wrap" ng-class="{visible: (currentState !== 'landing' &&
                $root.user &&
                $root.portfolio &&
                ( currentState == 'panel.show.portfolio' ||
                currentState == 'panel.panel.transactions.panel.transactions.portfolio' ||
                currentState == 'panel.show.portfolio_banks' ||
                currentState == 'panel.show.banks_balance' ||
                currentState == 'panel.panel.show.panel.show.portfolio' ||
                currentState == 'panel.panel.transactions.panel.transactions.portfolio' ||
                currentState == 'panel.panel.show.panel.show.portfolio_banks' ||
                currentState == 'panel.panel.show.panel.show.banks_balance' ||
                currentState == 'panel.transactions.portfolio'
                ))}"
            >
                <!--
                <div class="notification_wrap">
                    <button class="btn notification_button have-notification">
                        <span class="glyphicon glyphicon-bell" aria-hidden="true"></span>
                    </button>
                </div>
                <div class="button-devider"></div>
                -->
                <div class="transaction_wrap" style="margin-right: 1%;">
                    <!-- Open notification with a button -->
                    <button class="btn transaction_button" data-toggle="modal" data-target="#new_transaction">
                        <span>ADD NEW TRANSACTION</span> +
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Content
    ============================================= -->
    <md-content ui-view></md-content>
    {{--@yield('content')--}}

    <!-- Notification
    ============================================= -->
    @include('includes.notification')

    <!-- Footer
    ============================================= -->
    @include('includes.footer')

</div><!-- #wrapper end -->

<!-- Go To Top
============================================= -->
<div id="gotoTop" class="icon-angle-up"></div>

<!-- Spinner Page Load
============================================= -->
<route-loading-indicator></route-loading-indicator>

<!-- JavaScripts
============================================= -->
<script type="text/javascript" src="{!! asset('js/jquery.js') !!}"></script>
<script type="text/javascript" src="{!! asset('vendor/js/final.min.js') !!}"></script>
{{--<script type="text/javascript" src="https://maps.google.com/maps/api/js?key={!! env('GOOGLE_API_KEY') !!}"></script>--}}

<!-- Other JavaScripts
============================================= -->
@yield('scripts')

<!-- Footer Scripts
============================================= -->

</body>
</html>