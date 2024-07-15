<?php

namespace Tests\Services;

use PHPUnit\Framework\TestCase;
use App\Services\ParseData;
use SimpleXMLElement;

class test_B_ParseDataTest extends TestCase
{
    private $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new ParseData();
    }

    public function testGetTagNames()
    {
        $xmlFile = 'tests/data/sample.xml';
        $xmlString = file_get_contents($xmlFile);
        $xmlElement = new SimpleXMLElement($xmlString);

        $result = $this->parser->getTagNamesWithRelationships($xmlElement);

        $expectedResult = [
            'root' => ['catalog'],
            'catalog' => ['item'],
            'item' => ['entity_id', 'CategoryName', 'sku', 'name', 'description', 'shortdesc', 'price', 'link', 'image', 'Brand', 'Rating', 'CaffeineType', 'Count', 'Flavored', 'Seasonal', 'Instock', 'Facebook', 'IsKCup']
        ];

        $this->assertEquals($expectedResult, $result);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
