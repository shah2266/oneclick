@php use Carbon\Carbon; @endphp
@extends('layouts.master-mail-template')

@include('emails.partials')

<!-- Additional content -->
@section('content')
<!-- Today call summary -->
@foreach ($tableContent as $row)
    {!! $row !!}
@endforeach

@endsection

<!-- Signature -->
@section('signature')
    @include('includes.signature')
@endsection
