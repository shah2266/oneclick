<?php
namespace App\Http\Controllers\SERVER;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DatabaseStatusController extends Controller
{
    /**
     * sqlsrv1 -> IGW (new) DB connection
     * sqlsrv2 -> IOS (new) DB connection
     */

    public function index() {

        $servers = array(
            1 => 'IGW: 192.168.200.161',
            2 => 'IOS: 192.168.200.162'
        );

        $infos = [];
        foreach ($servers as $key=> $value) {
            $infos[$value] = array_values(DB::connection('sqlsrv'.$key)->select("SELECT db_name() dbName, Sum(s.SizeMB)/1024 SizeGB, Sum(s.Used_MB)/1024 UsedGB, Sum(s.Free_MB)/1024 FreeGB FROM(
                SELECT b.groupname, sum(a.Size/128) SizeMB, sum(cast(fileproperty(name, 'SpaceUsed') as int)/128) Used_MB,
                sum(a.Size/128-cast(fileproperty(name,'SpaceUsed') as int)/128) Free_MB FROM sys.sysfiles a,sys.sysfilegroups b WHERE a.groupid=b.groupid GROUP BY b.groupname)s"));
        }

        return view('server.database.index', compact('infos'));

    }

}
