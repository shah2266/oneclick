@extends('layouts.master-mail-template')

@include('emails.partials')

<!-- Additional content -->
@section('content')
<table border="1" style="border-collapse: collapse; width: 100%; text-align: center; font-family: 'Times New Roman', Times, serif; font-size: 14px;">
    <tr style="font-weight: bold;">
        <td colspan="11">Date</td>
    </tr>
    @foreach ($tblContent as $row)
        {!! $row !!}
    @endforeach
</table>
@endsection

<!-- Signature -->
@section('signature')
    @include('includes.signature')
@endsection
