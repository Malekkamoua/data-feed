<?php

namespace App\Services;

use Exception;
use App\Helpers\KeyHelper;
use App\Helpers\FileHelper;
use App\Helpers\DatabaseHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class DatabaseFeed
{
    public function insertData($data, $saveInDatabase = true)
    {
        try {

            $xml = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
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


            $requests = [];

            foreach ($itemsData as $index => $item) {

                $keys = ['uuid'];
                $values = ["'" . KeyHelper::generateUuid8() . "'"];
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

            //Save in file
            $sqlContent = implode(";\n", $requests) . ";";
            $fileName = 'database_feed.sql';
            $directory = storage_path('app/public/sql_files/');

            file_put_contents($directory . $fileName, $sqlContent);

            echo "Database feed created successfully at app/public/sql_files/" . $fileName . PHP_EOL;

            if ($saveInDatabase) {
                // Execute the SQL script
                $operation = 'Table feed';
                DatabaseHelper::runDatabaseCommands($requests, $operation);
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

}