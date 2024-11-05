@extends('layouts.master')
@section('title', 'User')

@section('section-title', 'Create New User')
@section('currentPage', 'User')


@section('content')

    @include('includes.breadcrumb')
    @include('includes.display-message')

    <div class="row">

        <div class="col-lg-12 grid-margin">
            <div class="float-right">
                <a
                    class="btn btn-inverse-info btn-fw"
                    href="{{ route('users.index') }}">
                    Back to users page
                </a>
            </div>
        </div>

        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <!-- form start -->
                    <form role="form" method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
                        @include('auth.form')
                        <button type="submit" class="btn btn-inverse-primary btn-fw">Add user</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
