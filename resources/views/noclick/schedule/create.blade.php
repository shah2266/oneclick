@extends('layouts.master')
@section('title', 'Schedule')

@section('section-title', 'Create New Schedule')
@section('currentPage', 'Schedule')


@section('content')

    @include('includes.breadcrumb')
    @include('includes.display-message')

    <div class="row">

        <div class="col-lg-12 grid-margin">
            <div class="float-right">
                <a
                    class="btn btn-inverse-info btn-fw"
                    href="{{ route( 'schedules.index' ) }}">
                    Back to schedule manage page
                </a>
            </div>
        </div>

        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <!-- form start -->
                    <form role="form" method="POST" action="{{ route('schedules.store') }}" enctype="multipart/form-data">
                        @include('noclick.schedule.form')
                        <button type="submit" class="btn btn-inverse-primary btn-fw">Create Schedule</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
