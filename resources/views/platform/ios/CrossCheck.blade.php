@php use Carbon\Carbon; @endphp
@extends('layouts.master')
@section('title', 'IOS platform')

@section('section-title', 'IOS Main and Summary day wise cross-check')
@section('currentPage', 'IOS cross check')

@section('content')

    @include('includes.breadcrumb')
    @include('includes.display-message')

    <!-- Report Area -->
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <form action="{{ url('/platform/ios/report/crosscheck') }}" method="post" class="forms-sample">
                        {!! csrf_field() !!}
                        <div class="form-group row">

                            <div class="col">
                                <span style="font-size:1em; font-weight:700; color: #575d7f; display: block;">Traffic direction</span>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="direction" value="1"> Incoming
                                    </label>
                                </div>
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="direction" value="2"> Outgoing
                                    </label>
                                </div>
                                @if($errors->has('direction'))
                                    <span class="error-message" role="alert">
                                        {{ $errors->first('direction') }}
                                    </span>
                                @endif
                            </div>

                            <div class="col">
                                <label for="fromDate">From date:<code>*</code></label>
                                <input
                                    class="form-control date1 {{ $errors->has('fromDate') ? ' has-error':'' }}"
                                    id="fromDate" name="fromDate"
                                    placeholder="Insert Date e.g: DD-MM-YYYY"
                                    value="{{ Carbon::yesterday()->subdays(3)->format('d-m-Y') }}"
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
                                    value="{{ Carbon::yesterday()->format('d-m-Y') }}"
                                    required="required"
                                />
                                @if($errors->has('toDate'))
                                    <span class="error-message" role="alert">
                                {{ $errors->first('toDate') }}
                            </span>
                                @endif
                            </div>

                        </div>

                        <button type="submit" class="btn btn-inverse-primary btn-fw float-right"> Checking</button>

                    </form>
                </div>
            </div>
            <!--form mask ends-->
        </div>
    </div>

    <!-- Report Area -->
    @if(Session::get('main') && ($summaryData = Session::get('summary')))
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    @if(Session::get('direction') == 1)
                        <span class="bg-inverse-info p-1"><strong>Direction:</strong> Incoming</span>
                    @endif

                    @if(Session::get('direction') == 2)
                        <span class="bg-inverse-info p-1"><strong>Direction:</strong> Outgoing</span>
                    @endif
                    <div class="table-responsive">
                       <table class="table text-center table-hover">
                          <thead>
                            <tr>
                                <th colspan="5">IOS Main Table</th>
                                <th colspan="3" class="bg-inverse-danger">MainTable - SummaryTable</th>
                                <th colspan="5">IOS Summary Table</th>
                            </tr>
                            <tr>
                                <th>SN</th>
                                <th>Traffic Date</th>
                                <th>Successful Call</th>
                                <th>Minutes</th>
                                <th>ACD</th>
                                <th>Successful Call</th>
                                <th>Minutes</th>
                                <th>ACD</th>
                                <th>SN</th>
                                <th>Traffic Date</th>
                                <th>Successful Call</th>
                                <th>Minutes</th>
                                <th>ACD</th>
                            </tr>
                          </thead>
                          <tbody>

                            @foreach(Session::get('main') as $key=> $mainTableData)
                                @php
                                    $main_acd = number_format($mainTableData->duration / $mainTableData->successfulCall, 2);
                                    $summary_acd = number_format($summaryData[$key]->duration / $summaryData[$key]->successfulCall, 2);
                                    $acd_diff = number_format($main_acd - $summary_acd, 2);
                                @endphp
                                <tr>
                                    <td>{{ ($key+1) }}</td>
                                    <td>{{ Carbon::parse($mainTableData->date)->format('d M Y') }} </td>
                                    <td>{{ number_format($mainTableData->successfulCall) }}</td>
                                    <td>{{ number_format($mainTableData->duration, 2) }}</td>
                                    <td>{{ $main_acd }}</td>

                                    <td class="bg-inverse-danger">{{ number_format($mainTableData->successfulCall - $summaryData[$key]->successfulCall) }}</td>
                                    <td class="bg-inverse-danger">{{ number_format(($mainTableData->duration - $summaryData[$key]->duration),2) }}</td>
                                    <td class="bg-inverse-danger">{{ $acd_diff }}</td>

                                    <td>{{ ($key+1) }}</td>
                                    <td>{{ Carbon::parse($summaryData[$key]->date)->format('d M Y') }}</td>
                                    <td>{{ number_format($summaryData[$key]->successfulCall) }}</td>
                                    <td>{{ number_format($summaryData[$key]->duration, 2) }}</td>
                                    <td>{{ $summary_acd }}</td>
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
