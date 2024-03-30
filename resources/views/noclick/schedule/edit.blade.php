@extends('layouts.master')
@section('title', 'Noclick-Schedule')

@section('section-title', 'Update schedule')
@section('currentPage', 'Noclick-Schedule')


@section('content')

    @include('includes.breadcrumb')
    @include('includes.display-message')

    <div class="row">

        <div class="col-lg-12 grid-margin">
            <div class="float-right">
                <a
                    class="btn btn-inverse-info btn-fw"
                    href="{{ route( 'schedules.index' ) }}">
                    Back to schedule page
                </a>
            </div>
        </div>

        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <!-- form start -->
                    <form role="form" method="POST" action="{{ route('schedules.update', ['schedule' => $schedule->id]) }}" enctype="multipart/form-data">
                        @method('PATCH')
                        @include('noclick.schedule.form')
                        <button type="submit" class="btn btn-inverse-primary btn-fw">Update schedule</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
