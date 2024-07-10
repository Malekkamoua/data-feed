<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ValidateInput
{
    public function readAndCheck($filePath, $xmlContent, $xmlObject)
    {
        if (!file_exists($filePath)) {
            Log::error("File not found: $filePath");
            return [
                'status' => false,
                'message' => "File not found: $filePath"
            ];
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
            return [
                'status' => false,
                'message' => "Failed to parse XML",
                'errors' => $errors
            ];
        }

        return [
            'status' => true,
            'message' => "XML is well-formed"
        ];
    }

    protected function formatError($error)
    {
        return sprintf("Error in line %d: %s", $error->line, trim($error->message));
    }
}
