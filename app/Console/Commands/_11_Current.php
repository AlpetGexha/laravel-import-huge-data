<?php

namespace App\Console\Commands;

use App\Traits\ImportHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;

class _11_Current extends Command
{
    use ImportHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-11';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handleImport($filePath): void
    {
        $this->concurrent($filePath);
    }

    private function concurrent(string $filePath): void
    {
        // 100 168ms
        // 1K 172ms
        // 10K 234ms
        // 100K 595ms
        // 1M 4.36s
        // 2M 8.8s

        $now = now()->format('Y-m-d H:i:s');
        $numberOfProcesses = 10;
        $chunkSize = 1000;

        $tasks = [];
        for ($i = 0; $i < $numberOfProcesses; $i++) {
            $tasks[] = function () use ($filePath, $i, $numberOfProcesses, $now, $chunkSize) {
                DB::reconnect();

                $handle = fopen($filePath, 'r');
                fgets($handle); // Skip header
                $currentLine = 0;
                $customers = [];

                while (($line = fgets($handle)) !== false) {
                    // Each process takes every Nth line
                    if ($currentLine++ % $numberOfProcesses !== $i) {
                        continue;
                    }

                    $row = str_getcsv($line);
                    $customers[] = [
                        'custom_id' => $row[0],
                        'name' => $row[1],
                        'email' => $row[2],
                        'company' => $row[3],
                        'city' => $row[4],
                        'country' => $row[5],
                        'birthday' => $row[6],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if (count($customers) === $chunkSize) {
                        DB::table('customers')->insert($customers);
                        $customers = [];
                    }
                }

                if (! empty($customers)) {
                    DB::table('customers')->insert($customers);
                }

                fclose($handle);

                return true;
            };
        }

        Concurrency::run($tasks);
    }

}
