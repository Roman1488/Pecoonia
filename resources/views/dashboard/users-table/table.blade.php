@extends('layouts.dashboard')

@section('content')
    <div class="page-bar">
        <ul class="page-breadcrumb">
            <li>
                <a href="/">Home</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <span>Users</span>
            </li>
        </ul>
    </div>

    <h1 class="page-title">
        Manage users:
    </h1>

    <div class="row">
        <div class="col-md-12">
            <!-- BEGIN EXAMPLE TABLE PORTLET-->
            <div class="portlet light portlet-fit bordered">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="icon-settings"></i>
                        <span class="caption-subject bold uppercase">Users</span>
                    </div>
                </div>
                <div class="portlet-body">
                    <table class="w-table table table-striped table-hover table-bordered">
                        <thead>
                            <tr>
                                <th> Username </th>
                                <th> Email </th>
                                <th> Joined </th>
                                <th> Email verified </th>
                                <th> Action </th>
                            </tr>
                        </thead>

                        <tbody>
                            
                            @foreach ($users as $user)
                                <tr class="{{ $user['deleted_at'] ? 'warning' : '' }}">
                                    <td>{{ $user['user_name'] }}</td>
                                    <td>{{ $user['email'] }}</td>
                                    <td>{{ $user['created_at'] }}</td>
                                    <td>
                                        @if ($user['activated'])
                                            <span class="label label-success"> Yes </span>
                                        @else
                                            <span class="label label-danger"> No </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm blue" 
                                            data-toggle="modal" 
                                            data-target="#edit" 
                                            data-id="{{ $user['id'] }}" 
                                            data-name="{{ $user['user_name'] }}" 
                                            data-email="{{ $user['email'] }}"> 
                                            <i class="fa fa-edit"></i>
                                        </button> 
                                        <button class="btn btn-danger btn-sm" 
                                                data-toggle="modal" 
                                                data-target="{{ $user['deleted_at'] ? '#delete' : '#delete_first' }}" 
                                                data-id="{{ $user['id'] }}" 
                                                data-name="{{ $user['user_name'] }}" > 
                                                <i class="fa fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- END EXAMPLE TABLE PORTLET-->
        </div>
    </div>


    <div class="modal fade" id="delete_first" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">User deleting.</h4>
                </div>
                <div class="modal-body text-center">
                    Are you sure you want to delete the user "<strong class="delete-name"></strong>" ?

                    <form id="user-delete-first" role="form" method="POST" action="{{ url('/delete-user-first') }}" >
                        <input type="hidden" name="_method" value="DELETE">
                        {{ csrf_field() }}
                        <input type="hidden" class="delete-id" name="id" value="">
                    </form>
                </div>

                <div class="modal-footer">
                    <div class="text-center">
                        <button type="button" class="btn dark btn-outline" data-dismiss="modal">
                            Cancel
                        </button>
                        
                        <button type="submit" form="user-delete-first" class="btn red">
                            <i class="fa fa-trash" aria-hidden="true"></i> OK
                        </button>
                    </div>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="delete" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title">User deleting.</h4>
                </div>
                <div class="modal-body text-center">
                    Are you sure you want to delete the user "<strong class="delete-name"></strong>" ?

                    <form id="user-delete-form" role="form" method="POST" action="{{ url('/delete-user') }}" >
                        {{ csrf_field() }}
                        <input type="hidden" name="_method" value="DELETE">
                        <input type="hidden" class="delete-id" name="id" value="">

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password" class="control-label">Enter password</label>
                            <input id="password" type="password" class="form-control" name="password">
                        </div>
                    </form>
                </div>

                <div class="modal-footer">
                    <div class="text-center">
                        <button type="button" class="btn dark btn-outline" data-dismiss="modal">
                            Cancel
                        </button>
                        
                        <button type="submit" form="user-delete-form" class="btn red">
                            <i class="fa fa-trash" aria-hidden="true"></i> OK
                        </button>
                    </div>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

    <div class="modal fade" id="edit" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title">Edit</h4>
                    </div>
                    <div class="modal-body">
                        <form class="form-horizontal" role="form" method="POST" action="{{ url('/edit-user') }}">
                            <input type="hidden" name="_method" value="PUT">
                            <input type="hidden" name="id" value="" id="edit_id">
                            {{ csrf_field() }}
                        
                            <div class="form-body">
                                <div class="form-group{{ $errors->has('user_name') ? ' has-error' : '' }}">
                                    <label for="user_name" class="col-md-3 control-label">Username</label>
                                    <div class="col-md-9">
                                        <input id="username" type="text" class="form-control" name="user_name" value="{{ old('user_name') }}">

                                        @if ($errors->has('user_name'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('user_name') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                                    <label for="email" class="col-md-3 control-label">Email</label>
                                    <div class="col-md-9">
                                        <input id="email" type="text" class="form-control" name="email" value="{{ old('email') }}">

                                        @if ($errors->has('email'))
                                            <span class="help-block">
                                                <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <button type="button" class="btn dark btn-outline" data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn green">Save changes</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

@endsection

@section('scripts')
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val(),
            }
        });

        let table = $('table').DataTable({
            paging: false,
            lengthChange: true,
            ordering: true,
            info: false,
            autoWidth: false,
            columnDefs:[
                {
                    targets: [4],
                    sortable: false,
                }
            ]
        });

        $('table').on('click', '[data-target="#edit"]', function (event) {
            let vm = this;
            
            $('#edit_id').val($(vm).data('id'));
            $('#username').val($(vm).data('name'));
            $('#email').val($(vm).data('email'));
        });

        $('table').on('click', '[data-target="#delete"]', function(event){
            let vm = this;
            
            $('#delete .delete-id').val($(vm).data('id'));
            $('#delete .delete-name').html($(vm).data('name'));
        });

        $('table').on('click', '[data-target="#delete_first"]', function(event){
            let vm = this;
            
            $('#delete_first .delete-id').val($(vm).data('id'));
            $('#delete_first .delete-name').html($(vm).data('name'));
        });
    </script>
@endsection
