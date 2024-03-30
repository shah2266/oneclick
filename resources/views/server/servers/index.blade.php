@extends('layouts.master')
@section('title', 'Billing server')

@section('section-title', 'Billing server list')
@section('currentPage', 'Billing server')

@section('content')

@include('includes.breadcrumb')
@include('includes.display-message')

<!-- Report Area -->
<div class="row">

    <div class="col-lg-12 grid-margin">
        <div class="float-right">
            <a
                class="btn btn-inverse-primary btn-fw"
                href="{{ URL::to( 'server/info/igw/documentation' ) }}">
                Download IGW Documentation
            </a>
            <a
                class="btn btn-inverse-success btn-fw"
                href="{{ URL::to( 'server/info/ios/documentation' ) }}">
                Download IOS Documentation
            </a>

        </div>
    </div>

    <!-- Server list -->
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <p class="float-right">
                        <a
                            class="btn btn-primary btn-fw"
                            href="{{ URL::to( 'server/info/create' ) }}">
                            Add new server info
                        </a>
                    </p>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Machine Name</th>
                            <th>Short Name</th>
                            <th>IP Address</th>
                            <th>OS Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($server as $key => $data)
                            <tr>
                                <td>{{$data->machineName}}</td>
                                <td>{{$data->shortName}}</td>
                                <td>{{$data->ipAddress}}</td>
                                <td>{{$data->operatingSystem}}</td>

                                <td>
                                    @if($data->status == 'Active')
                                        <span class="badge bg-green-active">
                                        {{ $data->status }}</span>
                                    @else
                                        <span class="badge bg-red-active">
                                        {{ $data->status }}</span>
                                    @endif
                                </td>

                                <td style="text-align: right">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                                id="dropdownMenuOutlineButton1" data-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false"> Action
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuOutlineButton1">
                                            <a class="dropdown-item" href="{{ url('server/info/'.$data->id.'/edit') }}">Edit this info</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="{{ url('server/info/'.$data->id.'/edit') }}">View details</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    <!-- #Server list -->

</div>
<!-- #Report Area -->
@endsection
