@extends('layouts.master-mail-template')

@include('emails.partials')

<!-- Additional content -->
@section('content')
    <h4><u>BTrac IGW vs BTrac IOS</u></h4>
    <p style="margin-top: 20px; margin-bottom: 5px;"><u><b>Incoming:</b></u></p>
    <table border="1" style="border-collapse: collapse; width: 650px; text-align: center; font-family: 'Times New Roman', Times, serif; font-size: 14px;">
        <tr style="font-weight: bold;">
            <td colspan="8">Incoming</td>
        </tr>
        <tr style="font-weight: bold;">
            <td colspan="3">IGW</td>
            <td colspan="2" style="background: yellow;">Diff (IGW-IOS)</td>
            <td colspan="3">IOS</td>
        </tr>
        <tr style="font-weight: bold;">
            <td>Date</td>
            <td>No of Call</td>
            <td>Dur(Min)</td>
            <td style="background: yellow;">No of Call</td>
            <td style="background: yellow;">Dur(Min)</td>
            <td>Date</td>
            <td>No of Call</td>
            <td>Dur(Min)</td>
        </tr>
        <tr>
            @foreach($incoming as $key => $data)
                @if($key == 0 or $key == 5)
                    <td>{{$data }}</td>
                @else
                    @if($key == 3 or $key == 4)
                        <td style="background: yellow; color: red;">{{number_format($data) }}</td>
                    @else
                        <td>{{number_format($data) }}</td>
                    @endif
                @endif
            @endforeach
        </tr>
    </table>

    <p style="margin-top: 20px; margin-bottom: 5px;"><u><b>Outgoing:</b></u></p>
    <table border="1" style="border-collapse: collapse; width: 650px; text-align: center; font-family: 'Times New Roman', Times, serif; font-size: 14px;">
        <tr style="font-weight: bold;">
            <td colspan="8">Outgoing</td>
        </tr>
        <tr style="font-weight: bold;">
            <td colspan="3">IGW</td>
            <td colspan="2" style="background: yellow;">Diff(IGW-IOS)</td>
            <td colspan="3">IOS</td>
        </tr>
        <tr style="font-weight: bold;">
            <td>Date</td>
            <td>No of Call</td>
            <td>Dur(Min)</td>
            <td style="background: yellow;">No of Call</td>
            <td style="background: yellow;">Dur(Min)</td>
            <td>Date</td>
            <td>No of Call</td>
            <td>Dur(Min)</td>
        </tr>
        <tr>
            @foreach($outgoing as $key => $data)
                @if($key == 0 or $key == 5)
                    <td>{{$data }}</td>
                @else
                    @if($key == 3 or $key == 4)
                        <td style="background: yellow; color: red;">{{number_format($data) }}</td>
                    @else
                        <td>{{number_format($data) }}</td>
                    @endif
                @endif
            @endforeach
        </tr>
    </table>

@endsection

<!-- Signature -->
@include('emails.signature')
