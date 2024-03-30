@extends('layouts.master')
@section('title', 'Billing connectivity')

@section('section-title', 'Billing server connectivity testing')
@section('currentPage', 'Billing connectivity')

@section('content')

    @include('includes.breadcrumb')
    @include('includes.display-message')

    <!-- Form -->
    <div class="row">
        @foreach($servers as $server)
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <input type="hidden" id="csrf-token" value="{{ csrf_token() }}">
                        <h4 class="card-title">{{ $server['ServerName'] .' (' . $server['IPAddress'] . ')' }}</h4>
                        <div id="{{ str_replace('.', '_',$server['IPAddress']) }}_output" style="font-size: 10px;">
                            <!-- Output will be shown here -->
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <!-- #Form -->

@endsection
