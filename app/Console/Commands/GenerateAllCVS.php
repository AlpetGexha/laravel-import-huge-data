<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateAllCVS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:generate-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $names = [
            '100',
            '1000',
            '10000',
            '100000',
            '1000000',
            '2000000',
            '3000000',
        ];

        foreach ($names as $name) {
            $this->call('csv:generate', ['rows' => $name]);
        }
    }
}
