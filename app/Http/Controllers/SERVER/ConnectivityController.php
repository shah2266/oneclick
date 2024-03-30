<?php

namespace App\Http\Controllers\SERVER;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConnectivityController extends Controller
{

    public function index() {

        //Server info
        $servers = array(
            0 => array('ServerName'=>'IGW New', 'IPAddress'=>'192.168.200.161'),
            1 => array('ServerName'=>'IOS New', 'IPAddress'=>'192.168.200.162'),
            2 => array('ServerName'=>'Storage', 'IPAddress'=>'192.168.200.137'),
            3 => array('ServerName'=>'IGW New**', 'IPAddress'=>'192.168.200.165'),
            4 => array('ServerName'=>'Billing App Server', 'IPAddress'=>'192.168.101.10'),
            5 => array('ServerName'=>'Billing SMS Server', 'IPAddress'=>'192.168.101.11'),
            6 => array('ServerName'=>'IGW Old', 'IPAddress'=>'192.168.200.236'),
            7 => array('ServerName'=>'IOS Old', 'IPAddress'=>'192.168.102.42'),
            8 => array('ServerName'=>'Bangla ICX', 'IPAddress'=>'172.16.5.76'),
            9 => array('ServerName'=>'Intrac NexHop', 'IPAddress'=>'192.168.200.152'),
            10 => array('ServerName'=>'Intrac Prosentel', 'IPAddress'=>'192.168.200.151'),
            11 => array('ServerName'=>'File Synchronizer', 'IPAddress'=>'192.168.200.235')
        );

        return view('server.connectivity.index', compact('servers'));

    }

    public function pingServer(Request $request): JsonResponse
    {
        $ip = $request->input('ip');
        //dump('Received ping request for IP: ' . $ip);

        exec("ping -n 3 $ip", $output, $status);

        return response()->json([
            'ip' => $ip,
            'output' => $output,
            'status' => $status
        ]);
    }


}
