@extends('layouts.master')
@section('title', 'User')

@section('section-title', 'Create new theme')
@section('currentPage', 'Theme')


@section('content')

    @include('includes.breadcrumb')
    @include('includes.display-message')

    <div class="row">

        <div class="col-lg-12 grid-margin">
            <div class="float-right">
                <a
                    class="btn btn-inverse-info btn-fw"
                    href="{{ URL::to( 'themes' ) }}">
                    Back to themes page
                </a>
            </div>
        </div>

        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <!-- form start -->
                    <form role="form" method="POST" action="{{ route('themes.store') }}" enctype="multipart/form-data">
                        @include('auth.theme.form')
                        <button type="submit" class="btn btn-inverse-primary btn-fw">Add theme</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
