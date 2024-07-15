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
        $xmlFile = 'tests/data/sample.xml';
        $xmlString = file_get_contents($xmlFile);

        $this->databaseFeed->insertData($xmlString, false);
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

