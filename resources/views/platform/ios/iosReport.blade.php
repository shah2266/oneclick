@php use Carbon\Carbon; @endphp
@extends('layouts.master')
@section('title', 'IOS platform')

@section('section-title', 'BTrac IOS IN OUT Call Summary Report')
@section('currentPage', 'IOS Daily Report')

@section('content')

@include('includes.breadcrumb')
@include('includes.display-message')


<!-- Form -->
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <form action="{{ url('platform/ios/report/callsummary') }}" method="post" class="forms-sample">

                    {!! csrf_field() !!}

                    <div class="form-group row">

                        <div class="col-4">
                            <label for="reportDate">Insert date of report generation:<code>*</code></label>
                            <input
                                class="form-control date1 {{ $errors->has('reportDate') ? ' has-error':'' }}"
                                id="reportDate" name="reportDate"
                                placeholder="Insert Date e.g: DD-MM-YYYY"
                                value="{{ Carbon::now()->subdays()->format('d-M-Y') }}"
                                required="required"
                            />
                            @if($errors->has('reportDate'))
                                <span class="error-message" role="alert">
                                    {{ $errors->first('reportDate') }}
                                </span>
                            @endif
                        </div>

                    </div>

                    <button type="submit" class="btn btn-inverse-primary btn-fw float-left"> Submit</button>

                </form>
            </div>
        </div>
        <!--form mask ends-->
    </div>
</div>
<!-- #Form -->

<!-- Report Area -->
@if(!empty($files))
    <div class="row">

        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Serial</th>
                                <th>Report Name</th>
                                <th style="text-align: right">
                                    <a
                                        class="btn btn-primary btn-fw"
                                        href="{{ URL::to( 'platform/ios/report/callsummary/zip/download' ) }}">
                                        Download Zip File
                                    </a>
                                    <a
                                        class="btn btn-inverse-danger btn-fw"
                                        href="{{ URL::to( 'platform/ios/report/callsummary/clean/directory' ) }}">
                                        Directory Clean
                                    </a>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($files as $key => $fileName)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $fileName }}</td>
                                    <td style="text-align: right">
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                                    id="dropdownMenuOutlineButton1" data-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false"> Action
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuOutlineButton1">
                                                <a class="dropdown-item" href="{{ URL::to( 'platform/ios/report/callsummary/' . $fileName )  }}">Download</a>
                                                <div class="dropdown-divider"></div>
                                                <a
                                                    class="dropdown-item"
                                                    href="{{ URL::to( 'platform/ios/report/callsummary/move/'. $fileName) }}">
                                                    File move another directory
                                                </a>
                                                <a
                                                    class="dropdown-item"
                                                    href="{{ URL::to( 'platform/ios/report/callsummary/delete/'. $fileName) }}">
                                                    Delete</a>
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
    </div>
@endif
<!-- #Report Area -->

@endsection
