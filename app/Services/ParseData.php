<?php

namespace App\Services;

use Exception;
use SimpleXMLElement;
use Illuminate\Support\Facades\Log;

class ParseData
{
    public function getTagNamesWithRelationships(SimpleXMLElement $xmlElement)
    {
        try {
            echo "Parsing file .." . PHP_EOL;

            $tagNames = [];
            foreach ($xmlElement->xpath('//*') as $element) {
                $tagName = $element->getName();
                $this->trackParentRelationships($element, $tagName, $tagNames);
            }

            return $tagNames;

        } catch (Exception $e) {
            Log::error("An Error has occured: $e");
            return 1;
        }

    }

    private function trackParentRelationships(SimpleXMLElement $element, $tagName, &$tagNames)
    {
        // Check if the current element has a parent
        if ($element->xpath('parent::*')) {
            $parent = $element->xpath('parent::*')[0];
            $parentTagName = $parent->getName();

            //Nessecary for database scripting: (eg created-at => created_at --format accepted by databse)
            $tagName = str_replace("-", "_", $tagName);

            // Track relationships
            if (!isset($tagNames[$parentTagName])) {
                $tagNames[$parentTagName] = [];
            }

            // Avoid duplicate child entries
            if (!in_array($tagName, $tagNames[$parentTagName])) {
                $tagNames[$parentTagName][] = $tagName;
            }
        } else {
            // Elements with no parents (root elements)
            if (!isset($tagNames['root'])) {
                $tagNames['root'] = [];
            }

            // Avoid duplicate entries
            if (!in_array($tagName, $tagNames['root'])) {
                $tagNames['root'][] = $tagName;
            }
        }
    }
}