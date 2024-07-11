<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class DatabaseFeed
{
    public function insertData($filePath)
    {
        try {
            echo "Inserting data in database .." . PHP_EOL;

            $xml = simplexml_load_file($filePath, 'SimpleXMLElement', LIBXML_NOCDATA);
            $data = $this->parseXmlElement($xml);

            $itemsData = [];

            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $item) {
                        if (is_array($item)) {
                            $item['parentTagName'] = $key;
                            $itemsData[] = $item;
                        }
                    }
                } else {
                    $itemsData[] = ['parentTagName' => $key, 'value' => $value];
                }
            }

            // Define the directory and file name
            $directory = storage_path('app\public\sql_files');
            $fileName = 'database_feed.sql';
            $filePath = $directory . '/' . $fileName;

            // Check if the directory exists, if not, create it
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
            $requests = [];

            foreach ($itemsData as $index => $item) {

                $keys = ['id'];
                $values = ["'" . $this->generate_custom_uuid8() . "'"];
                foreach ($item as $key => $value) {
                    if ($key != "parentTagName" && $value != "parentTagName") {
                        array_push($keys, $key);
                        $escaped_text = str_replace("'", "''", $value);
                        array_push($values, "'" . $escaped_text . "'");
                    }
                }

                /*
                    Creating INSERT INTO table_name (column1, column2, column3, ...) VALUES (value1, value2, value3, ...);
                    Table name = $item['parentTagName']
                    Columns will be $colsString
                    Values will be $valsString
                */

                $cols = implode(", ", $keys);
                $colsString = "( " . $cols . " )";

                $vals = implode(", ", $values);
                $valsString = "( " . $vals . " )";

                $sql = "INSERT INTO " . $item['parentTagName'] . $colsString . " VALUES " . $valsString;
                //echo $sql;
                $requests[] = $sql;

            }

            $sqlContent = implode(";\n", $requests) . ";";
            File::put($filePath, $sqlContent);

            echo "Database feed file created successfully at " . $filePath . PHP_EOL;

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


        } catch (Exception $e) {
            echo "Error: " . $e;
            Log::error("Error: {$e}");
        }
    }
    function parseXmlElement($element, $parentTagName = null)
    {
        $parsedData = [];

        // Add the parent tag name if it exists
        if ($parentTagName) {
            $parsedData['parentTagName'] = $parentTagName;
        }

        foreach ($element->children() as $child) {
            $tagName = $child->getName();
            if (count($child->children()) > 0) {
                // If the child has its own children, parse recursively
                $parsedData[$tagName][] = $this->parseXmlElement($child, $tagName);
            } else {
                // Otherwise, just get the value (using trim to remove any extraneous whitespace)
                $parsedData[$tagName] = trim((string) $child);
            }
        }

        return $parsedData;
    }

    function generate_custom_uuid8()
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $uuid = '';
        for ($i = 0; $i < 8; $i++) {
            $uuid .= $chars[mt_rand(0, 61)];
        }
        return $uuid;
    }

}