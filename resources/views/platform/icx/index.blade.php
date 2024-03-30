@php use Carbon\Carbon; @endphp
@extends('layouts.master')
@section('title', 'BanglaICX platform')

@section('section-title', 'BanglaICX daily reports')
@section('currentPage', 'BanglaICX daily reports')

@section('content')

@include('includes.breadcrumb')
@include('includes.display-message')


<!-- Form -->
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <form action="{{ url('platform/banglaicx/report/callsummary') }}" method="post" class="forms-sample">

                    {!! csrf_field() !!}

                    <div class="form-group row">

                        <div class="col">
                            <label for="reportType">Report types:<code>*</code></label>
                            <select id="reportType" name="reportType" class="form-control {{ $errors->has('reportType') ? ' has-error':'' }}" required="required" >
                                <option value="">--Report Type--</option>
                                <option value="1">Incoming</option>
                                <option value="2">Outgoing</option>
                                <option value="3">National</option>
                                <option value="4">Summary OR Screenshot</option>
                                <option value="all" selected="selected">All</option>
                            </select>
                            @if($errors->has('reportType'))
                                <span class="error-message" role="alert">
                                    <strong>{{ $errors->first('reportType') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="col">
                            <label for="reportDate1">Insert date of report generation:<code>*</code></label>
                            <input
                                class="form-control date1 {{ $errors->has('reportDate1') ? ' has-error':'' }}"
                                id="reportDate1" name="reportDate1"
                                placeholder="Insert Date e.g: DD-MM-YYYY"
                                value="{{ Carbon::now()->subdays()->format('d-m-Y') }}"
                                required="required"
                            />
                            @if($errors->has('reportDate1'))
                                <span class="error-message" role="alert">
                                    <strong>{{ $errors->first('reportDate1') }}</strong>
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
                                        href="{{ URL::to( 'platform/banglaicx/report/callsummary/zip/download' ) }}">
                                        Download Zip File
                                    </a>
                                    <a
                                        class="btn btn-inverse-danger btn-fw"
                                        href="{{ URL::to( 'platform/banglaicx/report/callsummary/clean/directory' ) }}">
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
                                                <a class="dropdown-item" href="{{ URL::to( 'platform/banglaicx/report/callsummary/' . $fileName )  }}">
                                                    Download
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a
                                                    class="dropdown-item"
                                                    href="{{ URL::to( 'platform/banglaicx/report/callsummary/delete/'. $fileName) }}">
                                                    Delete
                                                </a>
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
