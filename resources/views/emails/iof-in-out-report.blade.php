@extends('layouts.master-mail-template')

@include('emails.partials')

<!-- Additional content -->
@section('content')
<table border="1" style="border-collapse: collapse; width: 670px; text-align: center; font-family: 'Times New Roman', Times, serif; font-size: 14px;">
    <tr style="font-weight: bold;">
        <td>Bangla Trac Communications Limited</td>
        <td colspan="2">Incoming</td>
        <td colspan="2">Outgoing</td>
    </tr>
    <tr style="font-weight: bold;">
        <td>Date</td>
        <td>IOS Minutes</td>
        <td>IGW Minutes</td>
        <td>IOS Minutes</td>
        <td>IGW Minutes</td>
    </tr>
    @foreach ($tableContent as $row)
        {!! $row !!}
    @endforeach
</table>


@endsection

<!-- Signature -->
@section('signature')
    @include('includes.signature')
@endsection
