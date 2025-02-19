<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Traits\ImportHelper;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;

class _4_LazyCollection extends Command
{
    use ImportHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-4';

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
        $this->lazyCollection($filePath);
    }

    private function lazyCollection(string $filePath): void
    {
        // 100 66ms / 0.39MB
        // 1K 37ms / 1.47MB
        // 10K 3s / 12MB
        // 100K 39s / 120MB
        // 1M memory issue

        $now = now()->format('Y-m-d H:i:s');

        LazyCollection::make(function () use ($filePath) {
            $handle = fopen($filePath, 'r');
            fgets($handle); // skip header

            while (($line = fgets($handle)) !== false) {
                yield str_getcsv($line);
            }
            fclose($handle);
        })
            ->each(function ($row) use ($now) {
                // Directly insert each row
                Customer::insert([
                    'custom_id' => $row[0],
                    'name' => $row[1],
                    'email' => $row[2],
                    'company' => $row[3],
                    'city' => $row[4],
                    'country' => $row[5],
                    'birthday' => $row[6],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }


}
