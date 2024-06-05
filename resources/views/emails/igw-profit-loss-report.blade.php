@php use Carbon\Carbon; @endphp
@extends('layouts.master-mail-template')

@include('emails.partials')

<!-- Additional content -->
@section('content')

    <!-- Current month profit -->
    {!! $totalProfit !!}

    <!-- Day wise profit loss -->
    {!! $dayWise !!}

@endsection

<!-- Signature -->
@section('signature')
    @include('includes.signature')
@endsection
