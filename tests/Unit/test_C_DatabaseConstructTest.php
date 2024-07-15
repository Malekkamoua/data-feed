<?php

use PHPUnit\Framework\TestCase;
use App\Services\DatabaseConstruct;

class test_C_DatabaseConstructTest extends TestCase
{
    protected $databaseConstruct;

    protected function setUp(): void
    {
        $this->databaseConstruct = new DatabaseConstruct();
    }

    public function testCreateDataBaseFeedFile()
    {
        $tagNames = [
            'catalog' => ['item'],
            'item' => ['entity_id', 'CategoryName', 'sku', 'name']
        ];

        // Call the method being tested without saving to the database
        $this->databaseConstruct->createDataBaseFile($tagNames, false);

        // Check if the file is created
        $fileName = 'storage\app\public\sql_files\database_schema.sql';
        $this->assertFileExists($fileName);

        // Verify the contents of the file
        $expectedContent = "CREATE TABLE IF NOT EXISTS item (";
        $actualContent = file_get_contents($fileName);
        $this->assertStringContainsString($expectedContent, $actualContent);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
