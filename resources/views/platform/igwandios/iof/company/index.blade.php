@extends('layouts.master')
@section('title', 'IGW and IOS platform')

@section('section-title', 'IOF Reports Company list')
@section('currentPage', 'Company list')

@section('content')

@include('includes.breadcrumb')
@include('includes.display-message')

<!-- Report Area -->
<div class="row">

    <div class="col-lg-12 grid-margin">
        <div class="float-right">
            <a
                class="btn btn-inverse-info btn-fw"
                href="{{ URL::to( 'platform/igwandios/report/iof/daily/call/summary/report' ) }}">
                Back to report panel
            </a>
            <a
                class="btn btn-success btn-fw"
                href="{{ URL::to( 'platform/igwandios/report/iof/company/create' ) }}">
                Add Company
            </a>
        </div>
    </div>

    <!-- IGW Company -->
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <h4 class="text-primary font-weight-bold">IGW company list</h4>
                    <table class="table">
                        <thead>
                        <tr>
                            <th>Serial</th>
                            <th>System ID</th>
                            <th>Full Name</th>
                            <th style="width: 380px;">Short Name</th>
                            <th style="width: 85px;">Status</th>
                            <th style="width: 40px">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($igwCompanies as $key => $data)
                            <tr>
                                <td>{{$data->precedence}}</td>
                                <td>{{$data->systemId}}</td>
                                <td>{{$data->fullName}}</td>
                                <td>{{$data->shortName}}</td>
                                <td>
                                    @if($data->status == 'Visible')
                                        <span class="btn btn-outline-success btn-rounded btn-sm"><i class="fa fa-eye"></i>
                                        {{ $data->status }}</span>
                                    @else
                                        <span class="btn btn-outline-danger btn-rounded btn-sm"><i class="fa fa-eye-slash"></i>
                                        {{ $data->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-outline-info btn-icon-text" href="{{ url('platform/igwandios/report/iof/company/'.$data->id.'/edit') }}" title="Edit this info"> Edit <i
                                            class="mdi mdi-pencil"></i></a>
                                </td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
    <!-- #IGW Company -->

    <!-- IOS Company -->
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">

                <div class="table-responsive">
                    <h4 class="text-primary font-weight-bold">IOS company list</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Serial</th>
                                <th>System ID</th>
                                <th>Full Name</th>
                                <th style="width: 380px;">Short Name</th>
                                <th style="width: 85px;">Status</th>
                                <th style="width: 40px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($iosCompanies as $key => $data)
                            <tr>
                                <td>{{$data->precedence}}</td>
                                <td>{{$data->systemId}}</td>
                                <td>{{$data->fullName}}</td>
                                <td>{{$data->shortName}}</td>
                                <td>
                                    @if($data->status == 'Visible')
                                        <span class="btn btn-outline-success btn-rounded btn-sm"><i class="fa fa-eye"></i>
                                        {{ $data->status }}</span>
                                    @else
                                        <span class="btn btn-outline-danger btn-rounded btn-sm"><i class="fa fa-eye-slash"></i>
                                        {{ $data->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-outline-info btn-icon-text" href="{{ url('platform/igwandios/report/iof/company/'.$data->id.'/edit') }}" title="Edit this info"> Edit <i
                                            class="mdi mdi-pencil"></i></a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>

                    </table>
                </div>

            </div>
        </div>
    </div>
    <!-- #IOS Company -->

    <!-- ICX Company -->
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">

                    <h4 class="text-primary font-weight-bold">ICX company list</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Serial</th>
                                <th>System ID</th>
                                <th>Full Name</th>
                                <th style="width: 380px;">Short Name</th>
                                <th style="width: 85px;">Status</th>
                                <th style="width: 40px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($icxCompanies as $key => $data)
                            <tr>
                                <td>{{$data->precedence}}</td>
                                <td>{{$data->systemId}}</td>
                                <td>{{$data->fullName}}</td>
                                <td>{{$data->shortName}}</td>
                                <td>
                                    @if($data->status == 'Visible')
                                        <span class="btn btn-outline-success btn-rounded btn-sm"><i class="fa fa-eye"></i>
                                        {{ $data->status }}</span>
                                    @else
                                        <span class="btn btn-outline-danger btn-rounded btn-sm"><i class="fa fa-eye-slash"></i>
                                        {{ $data->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-outline-info btn-icon-text" href="{{ url('platform/igwandios/report/iof/company/'.$data->id.'/edit') }}" title="Edit this info"> Edit <i
                                            class="mdi mdi-pencil"></i></a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>

                </div>

            </div>
        </div>
    </div>
    <!-- #ICX Company -->

    <!-- ANS Company -->
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">

                    <h4 class="text-primary font-weight-bold">ANS company list</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Serial</th>
                                <th>System ID</th>
                                <th>Full Name</th>
                                <th style="width: 380px;">Short Name</th>
                                <th style="width: 85px;">Status</th>
                                <th style="width: 40px">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ansCompanies as $key => $data)
                            <tr>
                                <td>{{$data->precedence}}</td>
                                <td>{{$data->systemId}}</td>
                                <td>{{$data->fullName}}</td>
                                <td>{{$data->shortName}}</td>
                                <td>
                                    @if($data->status == 'Visible')
                                        <span class="btn btn-outline-success btn-rounded btn-sm"><i class="fa fa-eye"></i>
                                        {{ $data->status }}</span>
                                    @else
                                        <span class="btn btn-outline-danger btn-rounded btn-sm"><i class="fa fa-eye-slash"></i>
                                        {{ $data->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-outline-info btn-icon-text" href="{{ url('platform/igwandios/report/iof/company/'.$data->id.'/edit') }}" title="Edit this info"> Edit <i
                                            class="mdi mdi-pencil"></i></a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>

                    </table>

                </div>

            </div>
        </div>
    </div>
    <!-- #ANS Company -->

</div>
<!-- #Report Area -->
@endsection
