@extends('layouts.master')
@section('title', 'Command')

@section('section-title', 'Create New Command')
@section('currentPage', 'Command')


@section('content')

    @include('includes.breadcrumb')
    @include('includes.display-message')

    <div class="row">

        <div class="col-lg-12 grid-margin">
            <div class="float-right">
                <a
                    class="btn btn-inverse-info btn-fw"
                    href="{{ route( 'commands.index' ) }}">
                    Back to command manage page
                </a>
            </div>
        </div>

        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <!-- form start -->
                    <form role="form" method="POST" action="{{ route('commands.store') }}" enctype="multipart/form-data">
                        @include('noclick.command.form')
                        <button type="submit" class="btn btn-inverse-primary btn-fw">Create Command</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
