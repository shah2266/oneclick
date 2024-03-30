@php use Carbon\Carbon; @endphp
@extends('layouts.master')
@section('title', 'IGW platform')

@section('section-title', 'IGW call summary reports')
@section('currentPage', 'IGW call summary')

@section('content')

@include('includes.breadcrumb')
@include('includes.display-message')

<!-- Report Area -->
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <form action="{{ url('platform/igw/report/callsummary') }}" method="post" class="forms-sample">

                    {!! csrf_field() !!}

                    <div class="form-group row">

                        <div class="col">
                            <label for="previousDays">Previous day (Optional):</label>
                            <select
                                class="form-control select2 {{ $errors->has('previousDays') ? ' has-error':'' }}"
                                id="previousDays" name="previousDays">
                                <option value="">--Select Days--</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                            </select>
                        </div>

                        <div class="col">
                            <label for="reportTypes">Report Type:<code>*</code></label>
                            <select class="form-control select2 {{ $errors->has('reportTypes') ? ' has-error':'' }}"
                                    required="required" id="reportTypes" name="reportTypes">
                                <option value="">--Report Type--</option>
                                <option value="1">Incoming</option>
                                <option value="2">Outgoing</option>
                                <option value="3" selected="selected">Both</option>
                            </select>
                            @if($errors->has('reportTypes'))
                                <span class="error-message" role="alert">
                                    {{ $errors->first('reportTypes') }}
                                </span>
                            @endif
                        </div>

                        <div class="col">
                            <label for="date">Date:<code>*</code></label>
                            <input
                                class="form-control date1 {{ $errors->has('reportDate') ? ' has-error':'' }}"
                                id="date" name="reportDate"
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
                                    href="{{ URL::to( 'platform/igw/report/callsummary/zip/download' )  }}">
                                    Download Zip File
                                </a>
                                <a
                                    class="btn btn-inverse-danger btn-fw"
                                    href="{{ URL::to( 'platform/igw/report/callsummary/clean/directory' )  }}">
                                    Directory Clean
                                </a>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($files as $key => $value)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $value }}</td>
                                    <td style="text-align: right">
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                                    id="dropdownMenuOutlineButton1" data-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false"> Action
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuOutlineButton1">
                                                <a class="dropdown-item" href="{{ URL::to( 'platform/igw/report/callsummary/' . $value )  }}">Download</a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="{{ URL::to( 'platform/igw/report/callsummary/delete/'. $value) }}">Delete</a>
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
