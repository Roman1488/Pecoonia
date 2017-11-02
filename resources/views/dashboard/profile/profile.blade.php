@extends('layouts.dashboard')

@section('content')
    <div class="page-bar">
        <ul class="page-breadcrumb">
            <li>
                <a href="/">Home</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <span>Profile</span>
            </li>
        </ul>
    </div>

    <h1 class="page-title">
        Admin profile:
    </h1>

    <div class="row">
        <div class="col-xs-12">
            <div class="portlet light">
                <div class="portlet-title tabbable-line">
                    <div class="caption caption-md">
                        <i class="icon-globe theme-font hide"></i>
                        <span class="caption-subject font-blue-madison bold uppercase">Profile Settings</span>
                    </div>
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#change-pass" data-toggle="tab">Change Password</a>
                        </li>
                    </ul>{{--nav--}}
                </div>{{--portlet-title--}}

                <div class="portlet-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="change-pass">
                            <div class="note note-info">
                                <p>
                                    Password must be min. 8 characters and use both capitol and small letters and numbers.
                                </p>
                            </div>

                            <form role="form" method="POST" action="{{ url('/change-pass') }}">
                                {{ csrf_field() }}
                                <input type="hidden" name="_method" value="PUT">

                                <div class="form-group{{ $errors->has('password_old') ? ' has-error' : '' }}">
                                    <label for="password_old" class="control-label">Current Password</label>    
                                    <input id="password_old" type="password" class="form-control" name="password_old">

                                    @if ($errors->has('password_old'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('password_old') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                                    <label for="password" class="control-label">New Password</label>    
                                    <input id="password" type="password" class="form-control" name="password">

                                    @if ($errors->has('password'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
                                    <label for="password_confirmation" class="control-label">Repeat New Password</label>    
                                    <input id="password_confirmation" type="password" class="form-control" name="password_confirmation">

                                    @if ($errors->has('password_confirmation'))
                                        <span class="help-block">
                                            <strong>{{ $errors->first('password_confirmation') }}</strong>
                                        </span>
                                    @endif
                                </div>

                                <div class="margin-top-10">
                                    <button type="submit" class="btn green"> Change Password </button>

                                    <a href="{{ url('/dashboard') }}" class="btn default"> Cancel </a>
                                </div>
                            </form>
                        </div>
                    </div>{{--tab-content--}}
                </div>{{--portlet-body--}}
            </div>{{--portlet--}}
        </div>{{--col--}}
    </div>{{--row--}}
@endsection

@section('scripts')

    <!-- Laravel Javascript Validation -->
    <script type="text/javascript" src="{{ asset('vendor/jsvalidation/js/jsvalidation.js')}}"></script>

    {!! $validator !!}

@endsection