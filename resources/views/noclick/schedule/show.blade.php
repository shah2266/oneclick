<!-- resources/views/schedules/index.blade.php -->
@php use Carbon\Carbon; @endphp
@extends('layouts.master')
@section('title', 'Noclick-schedule')

@section('section-title')
    {{ 'Noclick ' . strtolower(\App\Models\NoclickSchedule::frequencyOptions()[request()->route('frequency')]) . ' schedule list' }}
@endsection

@section('currentPage', 'Noclick-schedule')

@section('content')

    @include('includes.breadcrumb')
    @include('includes.display-message')

    <!-- Data display Area -->
    <div class="row">

        <div class="col-lg-12 grid-margin">
            <div class="float-right">
                <a
                    class="btn btn-inverse-success btn-fw"
                    href="{{ URL::to( 'noclick/mail/templates' ) }}">
                    Manage mail template
                </a>
            </div>
        </div>

        <!-- Schedule list -->
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <a
                                class="btn btn-inverse-info btn-fw"
                                href="{{ route('schedules.dashboard') }}">
                                Back to schedule dashboard
                            </a>
                            <a
                                class="btn btn-inverse-info btn-fw"
                                href="{{ route('schedules.index') }}">
                                View all schedules
                            </a>
                        </div>
                        @if(request()->route('frequency') == 2)
                            <div class="col text-right">
                                <label for="holiday">
                                    Set holidays: (Optional)
                                </label>
                                <input
                                    type="text"
                                    class="form-control multi_date {{ $errors->has('holiday') ? ' has-error':'' }}"
                                    name="holiday" id="holiday"
                                    value="{{ old('holiday') }}"
                                    placeholder="Separated by commas.e.g: 14-Feb-2024"
                                    style=" display: inline; max-width: 300px"
                                    onchange="updateDate(this.value)"
                                >
                            </div>
                        @endif

                        <div class="col">
                            <a
                                class="btn btn-primary btn-fw float-right"
                                href="{{ route('schedules.create') }}">
                                Create new schedule
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Frequency</th>
                                    <th>Command</th>
                                    <th>Days/Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($schedules as $schedule)
                                <tr>
                                    <td>{{ $schedule->id }}</td>
                                    <td>{{ $schedule->frequencyOptions() [$schedule->frequency] }}</td>
                                    <td>{{ $schedule->noclickCommand->command }}</td>
                                    {{--<td>{{ ['Regular', 'Weekend', 'Both'][$schedule->type - 1] }}</td>--}}
                                    <td>
                                        @if($schedule->frequency != 2)
                                            {{ $schedule->days }}
                                        @else
                                            <span class="text-danger font-italic holiday-display" data-schedule-id="{{ $schedule->id }}">{{ $schedule->holiday }}</span>
                                        @endif
                                    </td>
                                    <td>{{ Carbon::parse($schedule->time)->isoFormat('HH:mm') }}</td>
                                    <td>
                                        <label class="setting-toggle">
                                            <span class="switch">
                                                <input class="switch__input" type="checkbox" role="switch" id="toggle-schedule-{{ $schedule->id }}" onchange="toggleScheduleStatus('{{ $schedule->id }}', this.checked)" {{ $schedule->status === 'on' ? 'checked' : '' }}>
                                                <span class="switch__fill" aria-hidden="true">
                                                    <span class="switch__text">ON</span>
                                                    <span class="switch__text">OFF</span>
                                                </span>
                                            </span>
                                        </label>
                                    </td>

                                    <td style="text-align: right">
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                                    id="dropdownMenuOutlineButton1" data-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false"> Action
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuOutlineButton1">
                                                <a class="dropdown-item" href="{{ route('schedules.edit', ['schedule' => $schedule->id]) }}">Edit info</a>
                                                <div class="dropdown-divider"></div>
                                                <!-- Trigger modal when clicking the "Delete" link -->
                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteSchedule" data-schedule-id="{{ $schedule->id }}">
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
        <!-- #Schedule list -->

    </div>
    <!-- #Data display Area -->

    <!-- Delete schedule for confirmation -->
    <div class="modal fade" id="deleteSchedule" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="mdi mdi-close-circle"></i>
                    </button>
                </div>
                <!-- Hidden form for delete action -->
                <form id="delete-form" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body" id="deleteModalBody">
                        <!-- Content will be dynamically updated here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-outline-danger">Delete</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

@endsection
