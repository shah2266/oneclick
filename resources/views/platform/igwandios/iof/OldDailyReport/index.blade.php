@extends('layouts.master')

@section('title','IOF Report')
@section('currentPage','IOF Report')
@section('content')

<!-- Report Area -->
<div class="row">
    <div class="col-xs-12">
        <div class="box box-solid">
            <div class="box-header">
                <h3 class="box-title">Bangla Trac IOS and IGW Report for IOF</h3>
                <span class="pull-right">
                    <a href="{{ URL::to('platform/igwandios/report/iof/exec') }}" class="btn btn-success">Run exe for report</a>
                </span>
            </div>
            <div class="box-body">

                @include('includes.display-message')

                <table class="table table-hover">
                    <tr>
                        <th>Serial</th>
                        <th>Report Name</th>
                        <th>Filename</th>
                        <th>Action
                            <div class="btn-group pull-right">
                                <button type="button" class="btn btn-warning">Action</button>
                                <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">
                                    <span class="caret"></span>
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    <li>
                                        <a href="{{ URL::to( 'platform/igwandios/report/iof/callsummary/old/zip/download' )  }}"> <i class="fa fa-file-zip-o"></i> Zip Download</a>
                                        <a href="{{ URL::to( 'platform/igwandios/report/iof/callsummary/old/clean/directory' )  }}"> <i class="fa fa-trash"></i> Clean Directory</a>
                                    </li>
                                    <li class="divider"></li>
                                </ul>
                            </div>
                        </th>
                    </tr>
                    <?php
                    $i = 1;
                    foreach($files as $filename) {
                        $fn = explode("/", $filename);
                        $onlyFileName = $fn['1'];
                    ?>
                    <tr>
                        <td>{{ $i }}</td>
                        <td>Bangla Trac IOS and IGW Report for IOF</td>
                        <td valign="middle">{{ $fn['1'] }}</td>
                        <td>
                            <div class="margin">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-success">Action</button>
                                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                        <span class="caret"></span>
                                        <span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu" role="menu">
                                        <li>
                                            <a href="{{ URL::to( '/platform/igwandios/report/iof/callsummary/old/'. $onlyFileName )  }}"> <i class="fa fa-download"></i> Download</a>
                                        </li>
                                        <li>
                                            <a href="{{ URL::to( '/platform/igwandios/report/iof/callsummary/old/delete/'. $onlyFileName) }}"><i class="fa fa-trash"></i> Permanently delete</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php  $i++; }  ?>
                </table>

            </div>
        </div>
    </div>
</div>
<!-- #Report Area -->
@endsection
