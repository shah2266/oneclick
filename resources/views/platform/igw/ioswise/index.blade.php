@php use Carbon\Carbon; @endphp
@extends('layouts.master')
@section('title', 'IGW platform')

@section('section-title', 'IOS wise reports')
@section('currentPage', 'IOS wise')

@section('content')

@include('includes.breadcrumb')
@include('includes.display-message')

<!-- Report Area -->
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <form action="{{ url('platform/igw/report/ioswise') }}" method="post" class="forms-sample">

                    {!! csrf_field() !!}

                    <div class="form-group row">

                        <div class="col">
                            <span style="font-size:1em; font-weight:700; color: #575d7f; display: block;">Create file</span>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="create_file" id="optionsRadios1" value="1"> Single file
                                </label>
                            </div>
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="create_file" id="optionsRadios2" value="2" checked> Multiple files
                                </label>
                            </div>
                            @if($errors->has('create_file'))
                                <span class="error-message" role="alert">
                                    {{ $errors->first('create_file') }}
                                </span>
                            @endif
                        </div>

                        <div class="col">
                            <label for="reportType">Report Type:<code>*</code></label>
                            <select
                                class="form-control {{ $errors->has('reportType') ? ' has-error':'' }}"
                                id="reportType"
                                name="reportType"
                                required="required"
                            >
                                <option value="">--Report Type--</option>
                                <option value="1">Incoming</option>
                                <option value="2">Outgoing</option>
                                <option value="3" selected="selected">Both</option>
                            </select>
                            @if($errors->has('reportType'))
                                <span class="error-message" role="alert">
                                    {{ $errors->first('reportType') }}
                                </span>
                            @endif
                        </div>

                        <div class="col">
                            <label for="fromDate">From date:<code>*</code></label>
                            <input
                                class="form-control date1 {{ $errors->has('reportDate1') ? ' has-error':'' }}"
                                id="fromDate" name="reportDate1"
                                placeholder="Insert Date e.g: DD-MM-YYYY"
                                value="{{ Carbon::now()->subdays()->format('d-M-Y') }}"
                                required="required"
                            />
                            @if($errors->has('reportDate1'))
                                <span class="error-message" role="alert">
                                    {{ $errors->first('reportDate1') }}
                                </span>
                            @endif
                        </div>

                        <div class="col">
                            <label for="toDate">To date:<code>*</code></label>
                            <input
                                class="form-control date2 {{ $errors->has('reportDate2') ? ' has-error':'' }}"
                                id="toDate" name="reportDate2"
                                placeholder="Insert Date e.g: DD-MM-YYYY"
                                value="{{ Carbon::now()->subdays()->format('d-M-Y') }}"
                                required="required"
                            />
                            @if($errors->has('reportDate2'))
                                <span class="error-message" role="alert">
                                    {{ $errors->first('reportDate2') }}
                                </span>
                            @endif
                        </div>

                    </div>

                    <button type="submit" class="btn btn-inverse-primary btn-fw float-right"> Submit</button>

                </form>
            </div>
        </div>
        <!--form mask ends-->
    </div>
</div>

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
                                        href="{{ URL::to( 'platform/igw/report/ioswise/zip/download' ) }}">
                                        Download Zip File
                                    </a>
                                    <a
                                        class="btn btn-inverse-danger btn-fw"
                                        href="{{ URL::to( 'platform/igw/report/ioswise/clean/directory' ) }}">
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
                                                <a class="dropdown-item" href="{{ URL::to( 'platform/igw/report/ioswise/' . $fileName )  }}">Download</a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="{{ URL::to( 'platform/igw/report/ioswise/delete/'. $fileName) }}">Delete</a>
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
    <!-- #Report Area -->
@endif

@endsection
