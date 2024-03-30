<!-- resources/views/schedules/index.blade.php -->
@extends('layouts.master')
@section('title', 'Noclick-template')

@section('section-title', 'Noclick mail template list')
@section('currentPage', 'Noclick-template')

@section('content')

    @include('includes.breadcrumb')
    @include('includes.display-message')

    <!-- Data display Area -->
    <div class="row">

        <div class="col-lg-12 grid-margin">
            <div class="float-right">
                <a
                    class="btn btn-inverse-success btn-fw"
                    href="{{ URL::to( 'noclick/commands' ) }}">
                    Back command page
                </a>
            </div>
        </div>

        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <p class="float-right">
                        <a
                            class="btn btn-primary btn-fw"
                            href="{{ route( 'templates.create' ) }}">
                            Create new template
                        </a>
                    </p>
                    <div id="accordion" class="pt-5">

                        @foreach($templates as $template)
                        <div class="card border-0 wow fadeInUp" style="visibility: visible; animation-name: fadeInUp;">
                            <div class="card-header p-0" id="heading-{{ $template->id }}">
                                <div class="row">
                                    <div class="col">
                                            <button
                                                class="btn btn-link accordion-title border-0 collapsed"
                                                data-toggle="collapse"
                                                data-target="#collapse-{{ $template->id }}"
                                                aria-expanded="false"
                                                aria-controls="#collapse-{{ $template->id }}"
                                            >
                                                <i class="mdi mdi-plus"></i>
                                                {{ $template->template_name }}
                                            </button>
                                        </div>
                                    <div class="col accordion-title-others">
                                            <div class="row">
                                                <div class="col py-2">
                                                    <strong>Template: </strong> {{ $template->has_custom_mail_template }}
                                                </div>
                                                <div class="col-2 py-2 text-right">
                                                    <span class="btn btn-outline-success btn-rounded btn-sm">{{ $template->status }}</span>
                                                </div>
                                                <div class="col-3 py-2 pr-4 text-right">
                                                    <div class="dropdown">
                                                        <button class="btn btn-outline-primary dropdown-toggle" type="button"
                                                                id="dropdownMenuOutlineButton1" data-toggle="dropdown"
                                                                aria-haspopup="true" aria-expanded="false"> Action
                                                        </button>
                                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuOutlineButton1">
                                                            <a class="dropdown-item" href="{{ url('noclick/mail/templates/'.$template->id.'/edit') }}">Edit info</a>
                                                            <div class="dropdown-divider"></div>
                                                            <!-- Trigger modal when clicking the "Delete" link -->
                                                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteMailTemplate" data-template-id="{{ $template->id }}">
                                                                Delete
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                            </div>
                            <div id="collapse-{{ $template->id }}" class="collapse " aria-labelledby="heading-{{ $template->id }}" data-parent="#accordion">
                                <div class="card-body accordion-body">
                                    <p><strong>Command name: </strong> {!! $template->noclickCommand->name ?? '<span class="text-danger">Command not set yet.</span>' !!}</p>
                                    <p><strong>To email addresses: </strong> <span class="text-primary">{{ $template->to_email_addresses }}</span></p>
                                    <p><strong>CC email addresses: </strong> <span class="text-primary">{{ $template->cc_email_addresses }}</span></p>
                                    <p>
                                        <strong>Date position in subject: </strong>
                                        @if($template->has_subject_date === 'Before subject')
                                            <i>{{ __('Date set ') . $template->has_subject_date}} </i>  - {{ $template->subject }}
                                        @elseif($template->has_subject_date === 'After subject')
                                            {{ $template->subject }} - <i>{{ __('Date set ') . $template->has_subject_date}}</i>
                                        @else
                                            {{ $template->subject }}
                                        @endif
                                    </p>

                                    <p><strong>Greeting: </strong> {{ $template->greeting }}</p>
                                    <p><strong>Mail body content: </strong> {{ $template->mail_body_content }}</p>
                                    <p><strong>Inline date: </strong> {{ $template->has_inline_date }}</p>
                                    <p><strong>Mail template: </strong> {{ $template->has_custom_mail_template }}</p>
                                    <p><strong>Signature: </strong>
                                        <a href="{{ $template->signature ? asset('assets/images/signature/signature_001.png') : asset('assets/images/signature/signature_002.png') }}">
                                            {{ $template->signature }}
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>

                        @endforeach

                    </div>

                </div>
            </div>
        </div>

    </div>
    <!-- #Data display Area -->

    <!-- Delete Mail Template for confirmation -->
    <div class="modal fade" id="deleteMailTemplate" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
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




