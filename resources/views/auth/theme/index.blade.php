<!-- resources/views/Users/index.blade.php -->
@extends('layouts.master')
@section('title', 'Themes')

@section('section-title', 'Themes list')
@section('currentPage', 'Themes')

@section('content')

    @include('includes.breadcrumb')
    @include('includes.display-message')

    <!-- Data display Area -->
    <div class="row">
        <!-- User list -->
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">

                    <div class="table-responsive">
                        <p class="float-right">
                            <a
                                class="btn btn-primary btn-fw"
                                href="{{ route( 'themes.create' ) }}">
                                Create new theme
                            </a>
                        </p>
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Id</th>
                                <th>Name</th>
                                <th>Stylesheet name</th>
                                <th>User name</th>
                                <th class="text-right">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($themes as $theme)
                                <tr>
                                    <td>{{ $theme->id }}</td>
                                    <td>{{ $theme->theme_name }}</td>
                                    <td>{{ $theme->stylesheet_name }}</td>
                                    <td>
                                        @if(!$theme->users->isEmpty())
                                            [
                                                @foreach ($theme->users as $key => $user)
                                                    {{ $user->name }}
                                                    @if(!$loop->last),@endif
                                                @endforeach
                                            ]
                                        @endif
                                    </td>

                                    <td style="text-align: right">
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                                    id="dropdownMenuOutlineButton1" data-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false"> Action
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuOutlineButton1">
                                                <a class="dropdown-item" href="{{ url('setting/themes/'.$theme->id.'/edit') }}">Edit info</a>

                                                <div class="dropdown-divider"></div>
                                                <!-- Trigger modal when clicking the "Delete" link -->
                                                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteTheme" data-theme-id="{{ $theme->id }}">
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
        <!-- #User list -->

    </div>
    <!-- #Data display Area -->

    <!-- Delete theme for confirmation -->
    <div class="modal fade" id="deleteTheme" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
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
