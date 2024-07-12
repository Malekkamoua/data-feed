<?php

namespace App\Services;

use App\Helpers\DatabaseHelper;
use App\Helpers\KeyHelper;
use Exception;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\Log;

class DatabaseFeed
{
    public function insertData($filePath, $saveInDatabase = true)
    {
        try {

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


            $requests = [];

            foreach ($itemsData as $index => $item) {

                $keys = ['id'];
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
            $fileName = 'database_schema.sql';
            $msg = "Database feed created successfully at";
            FileHelper::createFile($fileName, $sqlContent, $msg);


            if ($saveInDatabase) {
                // Execute the SQL script
                $msg = 'Table feed executed successfully.';
                DatabaseHelper::runDatabaseCommands($requests, $msg);
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