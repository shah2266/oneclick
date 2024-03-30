@extends('layouts.master-mail-template')


@include('emails.partials')

<!-- Additional content -->
@section('content')
    {!! $cdrStatus !!}
@endsection

<!-- Signature -->
@section('signature')
    @include('includes.signature')
@endsection

