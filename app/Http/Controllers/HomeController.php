<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if (!$this->checkFtp($request))
            return redirect('/ftp');
        $conn = $this->ftpConn;

        $banExist = $this->is_folder($conn, 'BanHwID');
        $logsExist = $this->is_folder($conn, 'Logs');
        $screensExist = $this->is_folder($conn, 'ScreenShots');
        $banFileExist = false;
        if($banExist) {
            $banFileExist = (@ftp_nb_get($conn, 'Temp.txt', '/BanHwID/BanHwID.txt',FTP_BINARY) != FTP_FAILED);
        }
        $complete = $banExist && $logsExist && $screensExist && $banFileExist;
        $incomplete = !$complete && ($banExist || $logsExist || $screensExist || $banFileExist);
        return view('home', [
            'complete' => $complete,
            'incomplete' => $incomplete
        ]);
    }
}
