<?php

use PHPUnit\Framework\TestCase;
use App\Services\DatabaseFeed;

class test_D_DatabaseFeedTest extends TestCase
{
    protected $databaseFeed;

    protected function setUp(): void
    {
        parent::setUp();
        $this->databaseFeed = new DatabaseFeed();
    }

    public function testInsertDataSavesToFile()
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

        $this->databaseFeed->insertData($xml, false);
        // Assert that file 'database_feed.sql' is created
        $filePath = 'storage\app\public\sql_files\database_feed.sql';
        $this->assertFileExists($filePath);

        // Assert that the file content matches expected SQL structure
        $actualContent = file_get_contents($filePath);

        // Verify the structure and some specific values excluding uuid as it's randomly generated
        $expectedSqlStructure = "entity_id, CategoryName, sku, name, description, shortdesc, price, link, image, Brand, Rating, CaffeineType, Count, Flavored, Seasonal, Instock, Facebook, IsKCup ) VALUES";
        $this->assertStringContainsString($expectedSqlStructure, $actualContent);

        $this->assertStringContainsString("'340'", $actualContent);  // Check for entity_id
        $this->assertStringContainsString("'Green Mountain Ground Coffee'", $actualContent);  // Check for CategoryName
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}

