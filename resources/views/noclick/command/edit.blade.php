@extends('layouts.master')
@section('title', 'Noclick-Command')

@section('section-title', 'Update schedule -> ' . $command->name)
@section('currentPage', 'Noclick-Command')


@section('content')

    @include('includes.breadcrumb')
    @include('includes.display-message')

    <div class="row">

        <div class="col-lg-12 grid-margin">
            <div class="float-right">
                <a
                    class="btn btn-inverse-info btn-fw"
                    href="{{ route( 'commands.index' ) }}">
                    Back to command page
                </a>
            </div>
        </div>

        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <!-- form start -->
                    <form role="form" method="POST" action="{{ route('commands.update', ['command' => $command->id]) }}" enctype="multipart/form-data">
                        @method('PATCH')
                        @include('noclick.command.form')
                        <button type="submit" class="btn btn-inverse-primary btn-fw">Update Command</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
