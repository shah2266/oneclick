@extends('layouts.master')
@section('title', 'Schedule')

@section('section-title', 'Update mail template -> ' . $template->template_name)
@section('currentPage', 'Schedule')


@section('content')

    @include('includes.breadcrumb')
    @include('includes.display-message')

    <div class="row">

        <div class="col-lg-12 grid-margin">
            <div class="float-right">
                <a
                    class="btn btn-inverse-info btn-fw"
                    href="{{ URL::to( 'noclick/mail/templates' ) }}">
                    Back to template page
                </a>
            </div>
        </div>

        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <!-- form start -->
                    <form role="form" method="POST" action="{{ route('templates.update', ['template' => $template->id]) }}" enctype="multipart/form-data">
                        @method('PATCH')
                        @include('noclick.template.form')
                        <button type="submit" class="btn btn-inverse-primary btn-fw">Update Template</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
