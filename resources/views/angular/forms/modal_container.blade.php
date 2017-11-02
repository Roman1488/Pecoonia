<!-- Modal content-->
<div  ng-class="isModalRequested ? 'modal-content' : ''">

    <div ng-class="isModalRequested ? 'modal-header' : '' ">
        @yield('modal_header')
    </div>

    <div ng-class="isModalRequested ? 'modal-body' : '' ">
        @yield('modal_body')
    </div>

    <div ng-class="isModalRequested ? 'modal-footer' : '' ">
        @yield('modal_footer')
    </div>

</div>