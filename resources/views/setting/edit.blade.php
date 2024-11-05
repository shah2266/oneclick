@extends('layouts.master')
@section('title', 'App setting')

@section('section-title', 'Edit this info')
@section('currentPage', 'Setting')

@section('content')

    @include('includes.breadcrumb')
    @include('includes.display-message')

    <div class="row">
        <div class="col-lg-12 grid-margin">
            <div class="float-right">
                <a
                    class="btn btn-inverse-info btn-fw"
                    href="{{ route('apps.index') }}">
                    Back
                </a>
            </div>
        </div>

        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <!-- form start -->
                    <form role="form" method="POST" action="{{ route('apps.update', ['setting' => $setting->id]) }}" enctype="multipart/form-data">
                        @method('PATCH')
                        @include('setting.form')
                        <button type="submit" class="btn btn-inverse-primary btn-fw">Update</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
