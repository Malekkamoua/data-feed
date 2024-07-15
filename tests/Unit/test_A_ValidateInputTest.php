<?php

use Tests\TestCase;
use App\Services\ValidateInput;
use Illuminate\Support\Facades\Log;

class test_A_ValidateInputTest extends TestCase
{

    private $validateInput;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validateInput = new ValidateInput();
    }

    public function testFileNotFound()
    {
        $filePath = 'non_existent_file.xml';

        $expected = [
            'status' => false,
            'message' => "File not found or cannot be opened: $filePath",
            'errors' => ["File not found or cannot be opened: $filePath"]
        ];

        $result = $this->validateInput->readAndCheck($filePath);

        $this->assertEquals($expected, $result);
    }

    public function testInvalidXml()
    {
        $filePath = 'tests/data/invalid.xml';
        $xmlContent = file_get_contents($filePath);
        file_put_contents($filePath, $xmlContent);

        $result = $this->validateInput->readAndCheck($filePath);

        $this->assertFalse($result['status']);
        $this->assertEquals('Failed to parse XML', $result['message']);
        $this->assertNotEmpty($result['errors']);

    }

    public function testValidXml()
    {
        $filePath = 'tests/data/valid.xml';
        $xmlContent = file_get_contents($filePath);
        file_put_contents($filePath, $xmlContent);

        $result = $this->validateInput->readAndCheck($filePath);

        $this->assertTrue($result['status']);
        $this->assertEquals('XML is well-formed', $result['message']);
        $this->assertInstanceOf(\SimpleXMLElement::class, $result['xmlObject']);
        $this->assertEquals($xmlContent, $result['xmlContent']);

    }

    public function testLogErrorOnFileNotFound()
    {
        $filePath = 'non_existent_file.xml';
        Log::shouldReceive('error')->once()->with("File not found or cannot be opened: $filePath");

        $this->validateInput->readAndCheck($filePath);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
