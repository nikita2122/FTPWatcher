<?php
/**
 * Created by PhpStorm.
 * User: R
 * Date: 9/19/2022
 * Time: 1:14 PM
 */

namespace App\Http\Controllers;

use GuzzleHttp\Promise\AggregateException;
use Illuminate\Http\Request;
use function PHPUnit\Framework\returnArgument;
use function Symfony\Component\HttpFoundation\add;

class ScreenShotController extends Controller
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

    public function folders (Request $request)
    {
        if (!$this->checkFtp($request))
            return redirect('/ftp');
        $folders = [];
        $conn = $this->ftpConn;
        $list = ftp_mlsd($conn, '/ScreenShots/');
        if (is_array($list)) {
            foreach ($list as $item) {
                if ($item['type'] == 'dir') {
                    array_push($folders, $item);
                }
            }
        }
        return view('screenfolders', ['folders' => $folders]);
    }

    public function images (Request $request, $folder)
    {
        if (!$this->checkFtp($request))
            return redirect('/ftp');
        $images = [];
        $conn = $this->ftpConn;
        $list = ftp_mlsd($conn, '/ScreenShots/'.$folder);
        foreach ($list as $item) {
            if ($item['type'] != 'dir' && $item['type'] != 'pdir' && $item['type'] != 'cdir') {
                array_push($images, $item);
            }
        }
        return view('screenshots', [
            'folder' => $folder,
            'images' => $images
        ]);
    }

    public function image (Request $request)
    {
        $folder = $request->folder;
        $image = $request->image;
        if (!$this->checkFtp($request))
            return redirect('/ftp');

        $localFile = 'assets/images/screenshots/'.$folder.$image;
        $serverFile = '/ScreenShots/' . $folder . '/' . $image;
        if (file_exists($localFile))
            return response()->json($localFile);

        $conn = $this->ftpConn;
        $ret = @ftp_nb_get($conn, $localFile, $serverFile, FTP_BINARY);
        while ($ret == FTP_MOREDATA) {
            $ret = ftp_nb_continue($conn);
        }
        return response()->json($localFile);
    }

    public function delfolder (Request $request)
    {
        $folder = $request->folder;
        if (!$this->checkFtp($request))
            return redirect('/ftp');

        $conn = $this->ftpConn;
        $serverFile = '/ScreenShots/' . $folder;

        $this->ftp_rdel($conn, $serverFile);

        return response()->json('success');
    }

    public function delete (Request $request)
    {
        $folder = $request->folder;
        $image = $request->image;
        if (!$this->checkFtp($request))
            return redirect('/ftp');

        $conn = $this->ftpConn;
        $localFile = 'assets/images/screenshots/'.$folder.$image;
        $serverFile = '/ScreenShots/' . $folder . '/' . $image;
        if (file_exists($localFile))
            unlink(realpath($localFile));

        ftp_delete($conn, $serverFile);

        return response()->json('success');
    }
}