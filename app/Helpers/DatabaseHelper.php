<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

class DatabaseHelper
{

    /**
     * Used to update the .env file with user database credentials
     * @param array $data
     * @return void
     */
    public static function updateEnvFile(array $data)
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            Log::error('The .env file does not exist.');
            return;
        }

        $envContent = File::get($envPath);
        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $envContent);
    }


    /**
     * Runs the php artisan migrate in order to create the database and it's relevant tables
     * @return void
     */
    public static function makeMigration()
    {
        try {
            $output = new BufferedOutput();
            //Needed so the .env changes are taken in consideration otherwise we'll have caching problems
            Artisan::call('config:cache', [], $output);
            Artisan::call('migrate', [], $output);
            $outputContent = $output->fetch();
            echo "Migrations executed successfully:\n$outputContent";

        } catch (Exception $e) {
            echo "Error running migrations: " . $e->getMessage();
            Log::error("Error running migrations: {$e->getMessage()}");
        }
    }

    /**
     * Runs database commands that where constructed previously
     * @param mixed $requests
     * @return void
     */
    public static function runDatabaseCommands($requests, $msg)
    {
        try {

            foreach ($requests as $statement) {
                $trimmedStatement = trim($statement);
                if (!empty($trimmedStatement)) {
                    DB::unprepared($trimmedStatement . ';');
                }
            }

            echo $msg . PHP_EOL;

        } catch (Exception $e) {
            echo 'Error executing SQL script.' . PHP_EOL;
            Log::error("Error executing SQL script: {$e->getMessage()}");
        }
    }

}