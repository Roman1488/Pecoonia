@extends('layouts.dashboard')

@section('content')
    <div class="page-bar">
        <ul class="page-breadcrumb">
            <li>
                <a href="/">Home</a>
                <i class="fa fa-circle"></i>
            </li>
            <li>
                <span>Dashboard</span>
            </li>
        </ul>
    </div>

    <h1 class="page-title">
        Admin Dashboard <small>statistics, charts</small>
    </h1>

    <div class="note note-info">
        <p>
            Statistics goes here
        </p>
    </div>
@endsection
