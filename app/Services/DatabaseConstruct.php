<?php

namespace App\Services;

use Exception;
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

            $sql = "CREATE TABLE IF NOT EXISTS " . $tagName . " ( id CHAR(8) PRIMARY KEY ,";
            $childCount = count($children);

            foreach ($children as $index => $child) {
                $sql .= $child . " varchar(255)";
                if ($index < $childCount - 1) {
                    $sql .= ", ";
                } else {
                    $sql .= ") ";
                }
            }
            $requests[] = $sql;
        }

        $sqlContent = implode(";\n", $requests) . ";";

        // Define the directory and file name
        $directory = storage_path('app\public\sql_files');
        $fileName = 'database_schema.sql';
        $filePath = $directory . '/' . $fileName;
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($filePath, $sqlContent);
        echo "Database file created successfully at " . $filePath . PHP_EOL;

        // Execute the SQL script
        try {

            foreach ($requests as $statement) {
                $trimmedStatement = trim($statement);
                if (!empty($trimmedStatement)) {
                    DB::unprepared($trimmedStatement . ';');
                }
            }

            echo 'Table creations executed successfully.' . PHP_EOL;

        } catch (Exception $e) {
            echo 'Error executing SQL script.' . PHP_EOL;
            Log::error("Error executing SQL script: {$e->getMessage()}");
        }

    }

}
