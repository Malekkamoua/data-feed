<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ValidateInput
{
    public function readAndCheck($filePath)
    {
        $errors = [];

        try {
            // Check if the file exists
            if (!file_exists($filePath)) {
                throw new \Exception("File not found or cannot be opened: $filePath");
            }

            // Load XML content
            $xmlContent = file_get_contents($filePath);
            libxml_use_internal_errors(true);
            $xmlObject = simplexml_load_string($xmlContent);

            if ($xmlObject === false) {
                foreach (libxml_get_errors() as $error) {
                    $formattedError = sprintf("Error in line %d: %s", $error->line, trim($error->message));
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
                'message' => "XML is well-formed",
                'xmlObject' => $xmlObject,
                'xmlContent' => $xmlContent
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return [
                'status' => false,
                'message' => $e->getMessage(),
                'errors' => [$e->getMessage()]
            ];
        }
    }
}
