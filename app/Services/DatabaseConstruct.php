<?php

namespace App\Services;

use App\Helpers\DatabaseHelper;
use Illuminate\Support\Facades\File;


class DatabaseConstruct
{
    public function createDataBaseFile($tagNames, $saveInDatabase = true)
    {

        $requests = [];
        foreach ($tagNames as $tagName => $children) {
            if ($tagName != "root") {
                $sql = "CREATE TABLE IF NOT EXISTS " . $tagName . " ( uuid CHAR(8) PRIMARY KEY ,";
                $childCount = count($children);

                foreach ($children as $index => $child) {
                    $sql .= $child . " varchar(500)";
                    if ($index < $childCount - 1) {
                        $sql .= ", ";
                    } else {
                        $sql .= ") ";
                    }
                }
                $requests[] = $sql;
            }
        }

        //Save in file
        $sqlContent = implode(";\n", $requests) . ";";
        $fileName = 'database_schema.sql';
        $directory = storage_path('app/public/sql_files/');

        file_put_contents($directory . $fileName, $sqlContent);

        echo "Database file created successfully at storage/app/public/sql_files/" . $fileName . PHP_EOL;

        if ($saveInDatabase) {
            // Execute the SQL script
            $operation = 'Table creation';
            DatabaseHelper::runDatabaseCommands($requests, $operation);
        }

    }

}