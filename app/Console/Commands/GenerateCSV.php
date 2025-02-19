<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SplFileObject;

class GenerateCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csv:generate {rows}';

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

        $header = [
            'custom_id',
            'name',
            'email',
            'company',
            'city',
            'country',
            'birthday',
        ];

        $rows = $this->argument('rows');

        // count the time it takes to generate the CSV
        $start = microtime(true);

        $this->generateCSV($header, $rows);

        $end = microtime(true);

        $this->info("Generated CSV with {$rows} rows in " . round($end - $start, 2) . " seconds.");
    }

    private function generateCSV(array $header, int $rows): void
    {
        $filePath = storage_path("app/public/customers-{$rows}.csv");

        if (file_exists($filePath)) {
            $this->error("File already exists: {$filePath}");
            return;
        }

        $file = new SplFileObject($filePath, 'w');
        $file->fputcsv($header);

        $batchSize = 1000; // Write every 1000 rows for efficiency
        $buffer = [];

        // Precompute possible birthdates to reduce Carbon calls
        $dates = array_map(fn($age) => now()->subYears($age)->format('Y-m-d'), range(18, 65));

        for ($i = 0; $i < $rows; $i++) {
            $buffer[] = [
                $i + 1,
                'Customer ' . ($i + 1),
                'customer' . ($i + 1) . '@example.com',
                'Company ' . ($i + 1),
                'City ' . ($i + 1),
                'Country ' . ($i + 1),
                $dates[array_rand($dates)], // Faster date selection
            ];

            if (count($buffer) >= $batchSize) {
                foreach ($buffer as $row) {
                    $file->fputcsv($row);
                }
                $buffer = []; // Clear memory
            }
        }

        // Write remaining rows
        foreach ($buffer as $row) {
            $file->fputcsv($row);
        }
    }


}
