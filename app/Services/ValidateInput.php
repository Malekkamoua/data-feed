<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ValidateInput
{
    public function readAndCheck($filePath, $xmlContent, $xmlObject)
    {
        if (!file_exists($filePath)) {
            Log::error("File not found: $filePath");
            echo "File not found: $filePath" . PHP_EOL;
        }

        libxml_use_internal_errors(true);

        if ($xmlObject === false) {
            $errors = [];
            foreach (libxml_get_errors() as $error) {
                $formattedError = $this->formatError($error);
                $errors[] = $formattedError;
                Log::error($formattedError);
            }
            libxml_clear_errors();
            echo "Failed to parse XML" . PHP_EOL;
        }

        echo "XML is well-formed" . PHP_EOL;
    }

    protected function formatError($error)
    {
        return sprintf("Error in line %d: %s", $error->line, trim($error->message));
    }
}
