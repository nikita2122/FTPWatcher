<?php
/**
 * Created by PhpStorm.
 * User: R
 * Date: 9/19/2022
 * Time: 1:14 PM
 */

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;
use function Symfony\Component\Console\Input\hasArgument;

class LogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function logview (Request $request, $file)
    {
        if (!$this->checkFtp($request))
            return redirect('/ftp');
        $conn = $this->ftpConn;

        $ftpaddr = $request->session()->get('ftpaddress');
        $banServer = '/BanHwID/BanHwID.txt';
        $banLocal = $ftpaddr.'BanHwId.txt';

        $ret = @ftp_nb_get($conn, $banLocal, $banServer, FTP_BINARY);
        while ($ret == FTP_MOREDATA) {
            $ret = ftp_nb_continue($conn);
        }

        $banlist = [];
        $banfp = @fopen($banLocal, "r");
        if ($banfp) {
            while (($buffer = fgets($banfp, 4096)) !== false) {
                if (strlen($buffer) > 0)
                    array_push($banlist, chop($buffer));
            }
        }

        $files = [];
        $list = ftp_mlsd($conn, '/Logs/');

        if (is_array($list)) {
            foreach ($list as $item) {
                if ($item['type'] == 'file') {
                    array_push($files, $item);
                }
            }
        }
        $contents = [];

        if ($file != '') {
            $localFile = 'assets/logs/' . $file;
            $serverFile = '/Logs/' . $file;

            $conn = $this->ftpConn;
            $ret = @ftp_nb_get($conn, $localFile, $serverFile, FTP_BINARY);
            while ($ret == FTP_MOREDATA) {
                $ret = ftp_nb_continue($conn);
            }

            $fp = @fopen($localFile, "r");
            if ($fp) {
                $detect = '';
                $time = '';
                $path = '';
                $hardware = '';
                while (($buffer = fgets($fp, 4096)) !== false) {
                    if (str_starts_with($buffer, 'Local Time:')) $time = substr($buffer,14);
                    else if (str_starts_with($buffer, 'Hack Detect:')) $detect = substr($buffer,16);
                    else if (str_starts_with($buffer, 'Full Path:')) $path = substr($buffer,16);
                    else if (str_starts_with($buffer, 'HardwareID:')) $hardware = chop(substr($buffer,16));
                    else {
                        $isbanned = false;
                        foreach ($banlist as $ban) {
                            if (strcmp($ban, $hardware) == 0) {
                                $isbanned = true;
                                break;
                            }
                        }
                        array_push($contents, [
                            'time' => $time,
                            'detect' => $detect,
                            'path' => $path,
                            'hardware' => $hardware,
                            'isbanned' => $isbanned
                        ]);
                    }
                }
            }
        }
        return view('logs', ['files' => $files, 'contents' => $contents, 'banlist'=>$banlist]);
    }

    public function logfiles(Request $request)
    {
        return $this->logview($request, '');
    }

    public function logcontent(Request $request, $file)
    {
        return $this->logview($request, $file);
    }

    public function banadd (Request $request)
    {
        $hardware = $request->hardware;
        if (!$this->checkFtp($request))
            return redirect('/ftp');
        $conn = $this->ftpConn;

        $ftpaddr = $request->session()->get('ftpaddress');
        $banServer = '/BanHwID/BanHwID.txt';
        $banLocal = $ftpaddr.'BanHwId.txt';

        $ret = @ftp_nb_get($conn, $banLocal, $banServer, FTP_BINARY);
        while ($ret == FTP_MOREDATA) {
            $ret = ftp_nb_continue($conn);
        }

        $content = file_get_contents($banLocal);
        $content = $content.$hardware."\n";
        file_put_contents($banLocal, $content);

        $putret = @ftp_nb_put($conn, $banServer, $banLocal, FTP_BINARY);
        while ($putret == FTP_MOREDATA) {
            $putret = ftp_nb_continue($putret);
        }
        if ($putret != FTP_FINISHED) {
            return response()->json('failed');
        }
        return response()->json('success');
    }

    public function bandelete (Request $request)
    {
        $hardware = $request->hardware;
        if (!$this->checkFtp($request))
            return redirect('/ftp');
        $conn = $this->ftpConn;

        $ftpaddr = $request->session()->get('ftpaddress');
        $banServer = '/BanHwID/BanHwID.txt';
        $banLocal = $ftpaddr.'BanHwId.txt';

        $ret = @ftp_nb_get($conn, $banLocal, $banServer, FTP_BINARY);
        while ($ret == FTP_MOREDATA) {
            $ret = ftp_nb_continue($conn);
        }

        $content = file_get_contents($banLocal);
        $content = str_replace($hardware."\n", "",$content);
        $content = str_replace("\n".$hardware, "",$content);

        file_put_contents($banLocal, $content);

        $putret = @ftp_nb_put($conn, $banServer, $banLocal, FTP_BINARY);
        while ($putret == FTP_MOREDATA) {
            $putret = ftp_nb_continue($putret);
        }
        if ($putret != FTP_FINISHED) {
            return response()->json('failed');
        }
        return response()->json('success');
    }

}