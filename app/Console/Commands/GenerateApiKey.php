<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'key:api';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the applications api key';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Generate a random string of 64 chars
        $key = bin2hex(random_bytes(32));


        // Output the key
        $this->info("Your API key is: $key");

        // edit the env file
        file_put_contents(app()->environmentFilePath(), str_replace(
            'API_TOKEN="' . env('API_TOKEN') . '"', 'API_TOKEN="' . $key . '"', file_get_contents(app()->environmentFilePath())
        ));
    }
}
