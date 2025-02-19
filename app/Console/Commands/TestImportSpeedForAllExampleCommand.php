<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Process\Pool;
use Illuminate\Support\Facades\Process;

class TestImportSpeedForAllExampleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-import-speed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $command = [
            'import-1',
            'import-2',
            'import-3',
            'import-4',
            'import-5',
            'import-6',
            'import-7',
            'import-8',
            'import-9',
            'import-10',
            'import-11',
        ];

        $input = [0, 1, 2, 3, 4];

        $pool = Process::pool(function (Pool $pool) {
            $pool->input(1)->command('php artisan import-1');
            $pool->input(4)->command('php artisan import-1');
        })->start(function (string $type, string $output, int $key) {
            $line = collect(explode("\n", $output))
                ->first(fn($line) => str_contains($line, 'TIME'));

            $this->info($line);

            if ($type === 'err') {
                $this->info($output);
            }
        });


//        if($result->errorOutput()) {
//            $this->info($result->errorOutput());
//        } else {
//            $this->info('Command ran successfully');
//        }

    }
}
