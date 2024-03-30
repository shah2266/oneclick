@extends('layouts.master')
@section('title', 'Themes')

@section('section-title', 'Update theme info of -> ' . $theme->theme_name)
@section('currentPage', 'Themes')


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
                    <form role="form" method="POST" action="{{ route('themes.update', ['theme' => $theme->id]) }}" enctype="multipart/form-data">
                        @method('PATCH')
                        @include('auth.theme.form')
                        <button type="submit" class="btn btn-inverse-primary btn-fw">Update theme info</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
