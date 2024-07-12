<?php

namespace App\Console\Commands;

use App\Services\ParseData;
use App\Services\DatabaseFeed;
use App\Services\validateInput;
use Illuminate\Console\Command;
use App\Helpers\DatabaseHelper;
use App\Services\DatabaseConstruct;
use Illuminate\Support\Facades\Log;

class processData extends Command
{
    protected $signature = 'app:process-data';
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
        $this->info('Hello user :) ');

        $outputChoice = $this->choice(
            'How do you want your output: ',
            ['Saved in database directly', 'Simply generate database scripts (create - insert)'],
            0
        );

        $saveInDatabase = $outputChoice == 'Saved in database directly' ? true : false;

        if ($saveInDatabase) {

            $this->info('Please provide your database credentials');
            $dbConnection = $this->ask('Database connection (mysql) ');
            $dbHost = $this->ask('Database host');
            $dbPort = $this->ask('Database port');
            $dbName = $this->ask('Database name ');
            $dbUsername = $this->ask('Username ');
            $dbPassword = $this->ask('Password ');

            if (!$dbName || !$dbUsername) {
                $this->error('Database name and username are required.');
                Log::error('Database name and username are required.');
                return 1;
            }

            DatabaseHelper::updateEnvFile([
                'DB_CONNECTION' => $dbConnection ? $dbConnection : 'mysql',
                'DB_PORT' => $dbPort ? $dbPort : '3306',
                'DB_HOST' => $dbHost ? $dbHost : '127.0.0.1',
                'DB_DATABASE' => $dbName,
                'DB_USERNAME' => $dbUsername,
                'DB_PASSWORD' => $dbPassword,
            ]);

            $this->info('Database configuration updated successfully.');
            $this->info('Press enter to continue..');

            DatabaseHelper::makeMigration();
        }

        $dataSourceChoice = $this->choice(
            'Data source: ',
            ['disk', 'API'],
            0
        );

        if ($dataSourceChoice == 'disk') {
            $filePath = $this->ask('Please provide the complete path to your XML file');
            $xmlContent = file_get_contents($filePath);
            $xmlObject = simplexml_load_string($xmlContent);

            $this->validateInput->readAndCheck($filePath, $xmlContent, $xmlObject);


            $tagNames = $this->parseData->getTagNamesWithRelationships($xmlObject);

            $this->databaseConstruct->createDataBaseFile($tagNames, $saveInDatabase);
            $this->databaseFeed->insertData($filePath, $saveInDatabase);
        } else {
            $this->info('Not ready yet :(');
        }

        return 0;
    }

}
