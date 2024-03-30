@php use Carbon\Carbon; @endphp
@extends('layouts.master')
@section('title', 'IOS platform')

@section('section-title', 'IOS call report for BTRC')
@section('currentPage', 'BTRC Report')

@section('content')

@include('includes.breadcrumb')
@include('includes.display-message')

    <!-- Form -->
    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <form action="{{ url('platform/ios/report/btrc') }}" method="post" class="forms-sample">

                        {!! csrf_field() !!}

                        <div class="form-group row">
                            <div class="col-4">
                                <label for="reportDate">Insert date of report generation:<code>*</code></label>
                                <input
                                    class="form-control date2 {{ $errors->has('reportDate') ? ' has-error':'' }}"
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
    <div class="row">

        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    @if(Session::get('btrcFileName'))
                        <p>
                            <a
                                href="{{ URL::to( '/platform/ios/report/btrc/' . Session::get('btrcFileName') )  }}"
                                class="btn btn-primary btn-fw" title="{{ $fileName }}">
                                Download
                            </a>
                        </p>
                    @endif

                    @if(Session::get('iosDayWise'))
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <th colspan="11" style="text-align:center">Date</th>
                                </tr>
                                <tr class="font-weight-bold">
                                    <td>IOS Name</td>
                                    @foreach(Session::get('iosDayWise') as $dayWise)
                                        <td>{{ Carbon::parse($dayWise->trafficDate)->format('d-M') }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td>Bangla Trac (IOS)</td>
                                    @foreach(Session::get('iosDayWise') as $dayWise)
                                        <td>{{ number_format($dayWise->duration) }}</td>
                                    @endforeach
                                </tr>
                            </table>
                        </div>

                    @elseif($fileLastModifiedDate == $nowDate)
                        <div class="callout bg-inverse-info">
                            <span> Report already generated. Generated date is {{ Carbon::NOW()->subdays()->format('d-M-Y') }}</span>
                            <a
                                href="{{ URL::to( 'platform/ios/report/btrc/'.$fileName)  }}"
                                class="btn btn-primary btn-fw float-right" title="{{ $fileName }}">
                                Download
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- #Report Area -->
@endsection
