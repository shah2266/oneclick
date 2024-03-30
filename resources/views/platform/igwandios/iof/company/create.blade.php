@extends('layouts.master')
@section('title', 'IGW and IOS platform')

@section('section-title', 'Add company info')
@section('currentPage', 'Add company')


@section('content')

@include('includes.breadcrumb')
@include('includes.display-message')

    <div class="row">

        <div class="col-lg-12 grid-margin">
            <div class="float-right">
                <a
                    class="btn btn-inverse-info btn-fw"
                    href="{{ URL::to( 'platform/igwandios/report/iof/company' ) }}">
                    Back to report panel
                </a>
            </div>
        </div>

        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <!-- form start -->
                    <form role="form" method="POST" action="{{ url('platform/igwandios/report/iof/company') }}" enctype="multipart/form-data">
                        @include('platform.igwandios.iof.company.form')
                        <button type="submit" class="btn btn-inverse-primary btn-fw">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
