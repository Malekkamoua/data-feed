<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;


class FileHelper
{

    /**
     * Used to create files
     * @param array $data
     * @return void
     */
    public static function createFile(string $filename, string $content, string $msg)
    {

        $directory = storage_path('app\public\sql_files');
        $filePath = $directory . '/' . $filename;
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($filePath, $content);
        echo $msg . $filePath . PHP_EOL;

    }

}