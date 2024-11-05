@extends('layouts.master')
@section('title', 'App setting')

@section('section-title', 'App setting')
@section('currentPage', 'App setting')

@section('content')

    @include('includes.breadcrumb')
    @include('includes.display-message')

    <!-- Report Area -->
    <div class="row">

        <!-- Server list -->
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <div class="table-responsive">
                        <p class="float-right">
                            <a
                                class="btn btn-primary btn-fw"
                                href="{{ route('apps.create') }}">
                                Add new setting
                            </a>
                        </p>
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Environment</th>
                                <th>App name</th>
                                <th>Short name</th>
                                <th>URL</th>
                                <th>Version</th>
                                <th>Contact details</th>
                                <th>Logo</th>
                                <th>Favicon</th>
                                <th>Copy right statement</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($settings as $key => $setting)
                                <tr>
                                    <td>{{$setting->environment}}</td>
                                    <td>{{$setting->app_name}}</td>
                                    <td>{{$setting->short_name}}</td>
                                    <td><a href="{{$setting->app_url ?? '#'}}">Click here</a></td>
                                    <td>{{$setting->app_version ?? 'null'}}</td>
                                    <td>
                                        <p><b>Email:</b> {{ $setting->email ?? 'null'}}</p>
                                        <p><b>Phone:</b> {{ $setting->phone ?? 'null'}}</p>
                                        <p><b>Address:</b> {{ $setting->address ?? 'null'}}</p>
                                    </td>
                                    <td><img src="{{ asset('assets/images/logo/' . $setting->logo) }}" alt="logo" loading="lazy"></td>
                                    <td>{{$setting->favicon}}</td>
                                    <td>{{$setting->copy_right_statement}}</td>
                                    <td>{{$setting->description ?? 'null'}}</td>
                                    <td>
                                        @if($setting->status == 'Active')
                                            <span class="text-success">
                                                {{ $setting->status }}
                                            </span>
                                        @else
                                            <span class="text-danger">
                                                {{ $setting->status }}
                                            </span>
                                        @endif
                                    </td>

                                    <td style="text-align: right">
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                                    id="dropdownMenuOutlineButton1" data-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false"> Action
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuOutlineButton1">
                                                <a class="dropdown-item" href="{{ route('apps.edit', ['setting' => $setting->id]) }}">Edit and Details</a>
                                                @if(Auth::user()->user_type != 2)
                                                    <div class="dropdown-divider"></div>
                                                    <!-- Trigger modal when clicking the "Delete" link -->
                                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteSetting" data-setting-id="{{ $setting->id }}">
                                                        Delete
                                                    </a>
                                                @endif
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
        <!-- #Server list -->

    </div>
    <!-- #Report Area -->

    <!-- Delete user for confirmation -->
    <div class="modal fade" id="deleteSetting" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
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
