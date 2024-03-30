@php use Carbon\Carbon; @endphp
@extends('layouts.master')
@section('title', 'IGW platform')

@section('section-title', 'BTRC daily reports')
@section('currentPage', 'BTRC daily')

@section('content')

@include('includes.breadcrumb')
@include('includes.display-message')

<!-- Form -->
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <form action="{{ url('platform/igw/report/btrc') }}" method="post" class="forms-sample">

                    {!! csrf_field() !!}

                    <div class="form-group row">

                        <div class="col">
                            <label for="fromDate">From date:<code>*</code> (If needed, Changed it.)</label>
                            <input
                                class="form-control date1 {{ $errors->has('fromDate') ? ' has-error':' disabled' }}"
                                id="fromDate" name="fromDate"
                                placeholder="Insert Date e.g: DD-MM-YYYY"
                                value="{{ Carbon::yesterday()->subdays(32)->format('d-M-Y') }}"
                                required="required"
                            />
                            @if($errors->has('fromDate'))
                                <span class="error-message" role="alert">
                                    {{ $errors->first('fromDate') }}
                                </span>
                            @endif
                        </div>

                        <div class="col">
                            <label for="toDate">To date:<code>*</code></label>
                            <input
                                class="form-control date2 {{ $errors->has('toDate') ? ' has-error':'' }}"
                                id="toDate" name="toDate"
                                placeholder="Insert Date e.g: DD-MM-YYYY"
                                value="{{ Carbon::yesterday()->format('d-M-Y') }}"
                                required="required"
                            />
                            @if($errors->has('toDate'))
                                <span class="error-message" role="alert">
                                    {{ $errors->first('toDate') }}
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
                                        href="{{ URL::to( 'platform/igw/report/btrc/zip/download' ) }}">
                                        Download Zip File
                                    </a>
                                    <a
                                        class="btn btn-inverse-danger btn-fw"
                                        href="{{ URL::to( 'platform/igw/report/btrc/clean/directory' ) }}">
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
                                                <a class="dropdown-item" href="{{ URL::to( 'platform/igw/report/btrc/' . $fileName )  }}">Download</a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item" href="{{ URL::to( 'platform/igw/report/btrc/delete/'. $fileName) }}">Delete</a>
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
