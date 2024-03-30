<!-- resources/views/schedules/index.blade.php -->

@extends('layouts.master')
@section('title', 'Noclick-command')

@section('section-title', 'Noclick command list')
@section('currentPage', 'Noclick-command')

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

                    <div class="table-responsive">
                        <p class="float-right">
                            <a
                                class="btn btn-primary btn-fw"
                                href="{{ route('commands.create') }}">
                                Create new command
                            </a>
                        </p>
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Id</th>
                                <th>Name</th>
                                <th>Command</th>
{{--                                <th>Mail template id</th>--}}
                                <th>Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($commands as $command)
                                <tr>
                                    <td>{{ $command->id }}</td>
                                    <td>{{ $command->name }}</td>
                                    <td>{{ $command->command }}</td>
{{--                                    <td>{{ $command->noclickMailTemplate->template_name }}</td>--}}
                                    <td>
                                        <label class="setting-toggle">
                                            <span class="switch">
                                                <input class="switch__input" type="checkbox" role="switch" id="toggle-command-{{ $command->id }}" onchange="toggleCommandStatus('{{ $command->id }}', this.checked)" {{ $command->status === 'on' ? 'checked' : '' }}>
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
                                                <a class="dropdown-item" href="{{ route('commands.edit', ['command' => $command->id]) }}">Edit info</a>
                                                <div class="dropdown-divider"></div>
                                                <!-- Trigger modal when clicking the "Delete" link -->
                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteCommand" data-command-id="{{ $command->id }}">
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
    <div class="modal fade" id="deleteCommand" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
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
