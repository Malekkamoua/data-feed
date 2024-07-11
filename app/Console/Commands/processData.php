<?php

namespace App\Console\Commands;

use App\Services\ParseData;
use App\Services\DatabaseFeed;
use App\Services\validateInput;
use Illuminate\Console\Command;
use App\Services\DatabaseConstruct;

class processData extends Command
{
    protected $signature = 'app:process-data {file}';
    protected $description = 'Check XML file for errors and return the line where it has a problem';
    protected $validateInput;
    protected $parseData;
    protected $databaseConstruct;
    protected $databaseFeed;

    public function __construct(ValidateInput $validateInput, ParseData $parseData, DatabaseConstruct $databaseConstruct, DatabaseFeed $databaseFeed)
    {
        parent::__construct();
        $this->validateInput = $validateInput;
        $this->parseData = $parseData;
        $this->databaseConstruct = $databaseConstruct;
        $this->databaseFeed = $databaseFeed;
    }

    public function handle()
    {

        $filePath = $this->argument('file');
        $xmlContent = file_get_contents($filePath);
        $xmlObject = simplexml_load_string($xmlContent);

        $this->validateInput->readAndCheck($filePath, $xmlContent, $xmlObject);

        $tagNames = $this->parseData->getTagNamesWithRelationships($xmlObject);

        $this->databaseConstruct->createDataBaseFile($tagNames);
        $this->databaseFeed->insertData($filePath);
    }


}
