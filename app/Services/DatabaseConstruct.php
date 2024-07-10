<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class DatabaseConstruct
{
    public function createDataBaseFile($tagNames)
    {
        echo "Creating database file .." . PHP_EOL;

        $requests = [];
        foreach ($tagNames as $tagName => $children) {

            $sql = "CREATE TABLE IF NOT EXISTS " . $tagName . " ( ";
            $childCount = count($children);

            foreach ($children as $index => $child) {
                $sql .= $child . " varchar(255)";
                if ($index < $childCount - 1) {
                    $sql .= ", ";
                }
            }

            $sql .= ", id CHAR(36) PRIMARY KEY)";
            $requests[] = $sql;
        }

        $sqlContent = implode(";\n", $requests) . ";";

        // Define the directory and file name
        $directory = storage_path('app\public\sql_files');
        $fileName = 'database_schema.sql';
        $filePath = $directory . '/' . $fileName;

        // Check if the directory exists, if not, create it
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
        File::put($filePath, $sqlContent);
        echo "Database file created successfully at " . $filePath . PHP_EOL;

        // Execute the SQL script
        try {
            DB::unprepared($sql);
            echo 'SQL script executed successfully.';
        } catch (\Exception $e) {
            echo 'Error executing SQL script.';
            Log::error("Error executing SQL script: {$e->getMessage()}");
        }


        return response()->json(['message' => 'File created successfully!', 'file' => $filePath]);
    }

}
