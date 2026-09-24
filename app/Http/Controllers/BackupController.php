<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Spatie\DbDumper\Databases\MySql;
use ZipArchive;

class BackupController extends Controller
{
    protected function backupDir()
    {
        $dir = storage_path('app/backups');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        return $dir;
    }

    public function index()
    {
        $dir = $this->backupDir();
        $files = collect(File::files($dir))
            ->sortByDesc(fn ($file) => $file->getMTime())
            ->map(function ($file) {
                return [
                    'name' => $file->getFilename(),
                    'size' => number_format($file->getSize() / (1024 * 1024), 2) . ' MB',
                    'type' => strtoupper($file->getExtension()) === 'SQL' ? 'Database' : 'Storage',
                    'created_at' => Carbon::createFromTimestamp($file->getMTime())->format('d-m-Y h:i:s A'),
                ];
            })
            ->values();

        return view('backend.system.backup', compact('files'));
    }

    public function database()
    {
        $connection = config('database.connections.' . config('database.default'));
        $fileName = 'database_backup_' . date('Y-m-d_His') . '.sql';
        $path = $this->backupDir() . DIRECTORY_SEPARATOR . $fileName;

        try {
            MySql::create()
                ->setDbName($connection['database'])
                ->setUserName($connection['username'])
                ->setPassword($connection['password'])
                ->setHost($connection['host'])
                ->setPort((int) $connection['port'])
                ->dumpToFile($path);

            flash(translate('Database backup created successfully'))->success();
        } catch (Exception $e) {
            flash(translate('Database backup failed') . ': ' . $e->getMessage())->error();
        }

        return back();
    }

    public function storage()
    {
        $source = public_path('uploads');
        $fileName = 'storage_backup_' . date('Y-m-d_His') . '.zip';
        $path = $this->backupDir() . DIRECTORY_SEPARATOR . $fileName;

        if (!File::isDirectory($source)) {
            flash(translate('Nothing to backup, uploads directory not found'))->error();
            return back();
        }

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            flash(translate('Storage backup failed'))->error();
            return back();
        }

        $files = File::allFiles($source);
        foreach ($files as $file) {
            $relativePath = 'uploads/' . $file->getRelativePathname();
            $zip->addFile($file->getPathname(), str_replace('\\', '/', $relativePath));
        }
        $zip->close();

        flash(translate('Storage backup created successfully'))->success();
        return back();
    }

    public function download($file)
    {
        $fileName = basename($file);
        $path = $this->backupDir() . DIRECTORY_SEPARATOR . $fileName;

        if (!File::exists($path)) {
            flash(translate('File not found'))->error();
            return back();
        }

        return response()->download($path);
    }

    public function destroy($file)
    {
        $fileName = basename($file);
        $path = $this->backupDir() . DIRECTORY_SEPARATOR . $fileName;

        if (File::exists($path)) {
            File::delete($path);
            flash(translate('Backup deleted successfully'))->success();
        } else {
            flash(translate('File not found'))->error();
        }

        return back();
    }
}
