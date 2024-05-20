@php use Carbon\Carbon; @endphp
@extends('layouts.master-mail-template')

@include('emails.partials')

<!-- Additional content -->
@section('content')
    <!-- Day wise profit loss -->
    @foreach($dayWise as $data)
        {!! $data !!}
    @endforeach

@endsection

<!-- Signature -->
@section('signature')
    @include('includes.signature')
@endsection
