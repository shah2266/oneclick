@extends('layouts.master')
@section('title', 'Database Status')

@section('section-title', 'Billing database status')
@section('currentPage', 'Database Status')

@section('content')

    @include('includes.breadcrumb')
    @include('includes.display-message')

<!-- Main row -->
<div class="row">
    <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <table class="table table-hover text-center">
                    <thead>
                    <tr>
                        <th>Platform</th>
                        <th>Total Space (GB)</th>
                        <th>Total Used Space (GB)</th>
                        <th>Total Free Space (GB)</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($infos as $key=>$info)
                        <tr>
                            <td>{{ $key }}</td>
                            @foreach($info as $i)
                                <td>{{ $i->SizeGB }}</td>
                                <td>{{ $i->UsedGB }}</td>
                                <td>{{ $i->FreeGB }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
