@extends('layouts.master')

@section('title','Cross-Check')
@section('currentPage','Cross-Check')

@section('content')
    @include('server.database.data-cross-check')
@endsection
