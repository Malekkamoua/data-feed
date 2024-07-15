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
        $xml = <<<XML
                <catalog>
                    <item>
                        <entity_id>340</entity_id>
                        <CategoryName>
                            <![CDATA[Green Mountain Ground Coffee]]>
                        </CategoryName>
                        <sku>20</sku>
                        <name>
                            <![CDATA[Green Mountain Coffee French Roast Ground Coffee 24 2.2oz Bag]]>
                        </name>
                        <description></description>
                        <shortdesc>
                            <![CDATA[Green Mountain Coffee French Roast Ground Coffee 24 2.2oz Bag steeps cup after cup of smoky-sweet, complex dark roast coffee from Green Mountain Ground Coffee.]]>
                        </shortdesc>
                        <price>41.6000</price>
                        <link>
                            http://www.coffeeforless.com/green-mountain-coffee-french-roast-ground-coffee-24-2-2oz-bag.html
                        </link>
                        <image>http://mcdn.coffeeforless.com/media/catalog/product/images/uploads/intro/frac_box.jpg</image>
                        <Brand>
                            <![CDATA[Green Mountain Coffee]]>
                        </Brand>
                        <Rating>0</Rating>
                        <CaffeineType>Caffeinated</CaffeineType>
                        <Count>24</Count>
                        <Flavored>No</Flavored>
                        <Seasonal>No</Seasonal>
                        <Instock>Yes</Instock>
                        <Facebook>1</Facebook>
                        <IsKCup>0</IsKCup>
                    </item>
                </catalog>
                XML;

        $xmlElement = new SimpleXMLElement($xml);

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
