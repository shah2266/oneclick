<!-- resources/views/schedules/index.blade.php -->
@php use Carbon\Carbon; @endphp
@extends('layouts.master')
@section('title', 'Noclick-dashboard')

@section('section-title', 'Noclick dashboard')
@section('currentPage', 'Noclick-dashboard')

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <p class="callout bg-inverse-success alert alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <span>Welcome to <b>Noclick</b> reporting system</span>
            </p>
        </div>
    </div>

    @include('includes.breadcrumb')
    @include('includes.display-message')

    <div class="row">
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-9">
                            <div class="d-flex align-items-center align-self-start">
                                <h3 class="mb-0">{{$schedules}}</h3>
                                <!--<p class="text-success ml-2 mb-0 font-weight-medium"></p>-->
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="icon icon-box-primary">
                                <a href="{{ route('schedules.index') }}">
                                    <span class="mdi mdi-drawing-box"></span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <h6 class="text-muted font-weight-normal"><b>Total Schedules</b></h6>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @foreach($frequencyCounts as $result)
            <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-9">
                                <div class="d-flex align-items-center align-self-start">
                                    <h3 class="mb-0">{{$result->count}}</h3>
                                    <!--<p class="text-success ml-2 mb-0 font-weight-medium"></p>-->
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="icon icon-box-primary">
                                    <a href="{{ route('schedules.frequency.list', ['frequency' => $result->frequency]) }}">
                                        <span class="mdi mdi-drawing-box"></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <h6 class="text-muted font-weight-normal">Schedule :: <b>{{ $result->frequencyOptions() [$result->frequency] }}</b></h6>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex align-items-center align-self-start">
                                <h4>
                                   <a href="{{ route('schedules.create') }}" class="mb-0 font-weight-medium d-block text-behance" target="_blank">Create new schedule</a>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
