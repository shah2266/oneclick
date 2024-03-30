@extends('layouts.master-mail-template')

@section('title', 'IOF daily call summary report')
@section('warning')
<h3 style="color:red;">[Test Mail, ignore it.]</h3>
@endsection
@section('custom-padding', '150px')
@section('greeting', 'Dear Concern,')
@section('message', 'This is test mail. Testing date at: ')
{{--@section('message', 'Please find attachment, Here I have attached Bangla Trac Communications Limited IOS--}}
{{--and IGW Report as per as your format for the date of ')--}}

@section('date', \Carbon\Carbon::yesterday()->format('d-M-Y'))
