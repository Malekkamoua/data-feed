<?php

namespace App\Services;

use App\Helpers\FileHelper;
use App\Helpers\DatabaseHelper;


class DatabaseConstruct
{
    public function createDataBaseFile($tagNames, $saveInDatabase = true)
    {

        $requests = [];
        foreach ($tagNames as $tagName => $children) {
            //remove root 
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

        //Save in file
        $sqlContent = implode(";\n", $requests) . ";";
        $fileName = 'database_schema.sql';
        $msg = "Database file created successfully at";
        FileHelper::createFile($fileName, $sqlContent, $msg);

        if ($saveInDatabase) {
            // Execute the SQL script
            $msg = 'Table creation executed successfully.';
            DatabaseHelper::runDatabaseCommands($requests, $msg);
        }

    }

}
