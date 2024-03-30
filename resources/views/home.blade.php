@extends('layouts.master')
@section('title', 'Home')

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <p class="callout bg-inverse-success alert alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <span>Welcome to <b>Oneclick</b> reporting system</span>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9">
                            <div class="d-flex align-items-center align-self-start">
                                <h3 class="mb-0">11</h3>
                                <!--<p class="text-success ml-2 mb-0 font-weight-medium"></p>-->
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="icon icon-box-primary">
                                <a href="{{ url('server/connectivity/status') }}">
                                    <span class="mdi mdi-link-variant icon-item"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Server Disk Status</h6>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9">
                            <div class="d-flex align-items-center align-self-start">
                                <h3 class="mb-0">2</h3>
                                <!--<p class="text-success ml-2 mb-0 font-weight-medium"></p>-->
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="icon icon-box-primary">
                                <a href="{{ url('server/database/status/space') }}">
                                    <span class="mdi mdi-link-variant icon-item"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Databases Status</h6>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9">
                            <div class="d-flex align-items-center align-self-start">
                                <h3 class="mb-0">11</h3>
                                <!--<p class="text-danger ml-2 mb-0 font-weight-medium"></p>-->
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="icon icon-box-primary">
                                <a href="{{ url('server/connectivity/status') }}" class="small-box-footer">
                                    <span class="mdi mdi-link-variant icon-item"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Connectivity Testing</h6>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9">
                            <div class="d-flex align-items-center align-self-start">
                                <h3 class="mb-0">{{ $users }}</h3>
                                <!--<p class="text-success ml-2 mb-0 font-weight-medium"></p>-->
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="icon icon-box-primary">
                                <a href="{{ url('users') }}" class="small-box-footer">
                                    <span class="mdi mdi-link-variant icon-item"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <h6 class="text-muted font-weight-normal">Active users</h6>
                </div>
            </div>
        </div>
    </div>

@endsection
