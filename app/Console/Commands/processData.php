<?php

namespace App\Console\Commands;

use Exception;
use GuzzleHttp\Client;
use App\Services\ParseData;
use App\Services\DatabaseFeed;
use App\Helpers\DatabaseHelper;
use App\Services\validateInput;
use Illuminate\Console\Command;
use App\Services\DatabaseConstruct;
use Illuminate\Support\Facades\Log;

class processData extends Command
{
    protected $signature = 'app:process-data';
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

            $dbHost = "";
            $dbPort = "";
            $dbUsername = "";
            $dbPassword = "";

            $dbConnection = $this->choice(
                'Data source: ',
                ['mysql', 'sqlite'],
                0
            );

            $dbName = $this->ask('Database name');

            if ($dbConnection == "mysql") {
                $dbHost = $this->ask('Database host');
                $dbPort = $this->ask('Database port');
                $dbUsername = $this->ask('Username');
                $dbPassword = $this->ask('Password');
            }

            DatabaseHelper::updateEnvFile([
                'DB_CONNECTION' => $dbConnection,
                'DB_PORT' => $dbPort,
                'DB_HOST' => $dbHost,
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

            $filePath = $this->ask('Please provide the complete path to your XML file', 'storage/data/feed-small.xml');
            $validationResult = $this->validateInput->readAndCheck($filePath);

            if ($validationResult['status']) {
                $this->info($validationResult['message']);
                $xmlObject = $validationResult['xmlObject'];
                $xmlContent = $validationResult['xmlContent'];
            } else {
                foreach ($validationResult['errors'] as $error) {
                    return $this->error($error);
                }
            }

        } else {
            $url = $this->ask('Please provide the link to your XML data', 'https://thetestrequest.com/articles.xml');
            //Verifies the SSL certiciate of the website to ensure it's not a threat
            $caPath = storage_path('cert/cacert.pem');
            $client = new Client([
                'verify' => $caPath,
            ]);

            try {
                $response = $client->get($url);
                $this->info('XML data fetched successfully.');
                $xmlContent = $response->getBody()->getContents();
                $xmlObject = simplexml_load_string($xmlContent);
            } catch (Exception $e) {
                Log::error('Failed to fetch the XML data. Error: ' . $e->getMessage());
                return $this->error('Failed to fetch the XML data. Error: ' . $e->getMessage());
            }
        }

        $tagNames = $this->parseData->getTagNamesWithRelationships($xmlObject);
        $this->databaseConstruct->createDataBaseFile($tagNames, $saveInDatabase);
        $this->databaseFeed->insertData($xmlContent, $saveInDatabase);

        return 0;
    }

}
