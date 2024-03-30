@extends('layouts.master')
@section('title', 'Billing server')

@section('section-title', 'Add new server')
@section('currentPage', 'Billing server')


@section('content')

@include('includes.breadcrumb')
@include('includes.display-message')

<div class="row">

    <div class="col-lg-12 grid-margin">
        <div class="float-right">
            <a
                class="btn btn-inverse-info btn-fw"
                href="{{ URL::to( 'server/info' ) }}">
                Back to server manage page
            </a>
        </div>
    </div>

    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <!-- form start -->
                <form role="form" method="POST" action="{{ url('server/info') }}" enctype="multipart/form-data">
                    @include('server.servers.form')
                    <button type="submit" class="btn btn-inverse-primary btn-fw">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
