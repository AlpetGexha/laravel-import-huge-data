<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Traits\ImportHelper;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;

class _5_LazyCollectionWithChunking extends Command
{
    use ImportHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-5';

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
        $this->lazyCollectionWithChunking($filePath);
    }

    private function lazyCollectionWithChunking(string $filePath): void
    {
        // Lazy loading with chunking
        // 100 16ms / 0.28MB
        // 1K 61ms / 0.8MB
        // 10K 275ms / 5.93MB
        // 100K 1.7s / 57MB
        // 1M memory issue if not tuned properly

        $now = now()->format('Y-m-d H:i:s');
        $chunkSize = 1000; // Define your chunk size

        LazyCollection::make(function () use ($filePath) {
            $handle = fopen($filePath, 'r');
            fgets($handle); // skip header

            while (($line = fgets($handle)) !== false) {
                yield str_getcsv($line);
            }
            fclose($handle);
        })
            ->map(fn ($row) => [
                'custom_id' => $row[0],
                'name' => $row[1],
                'email' => $row[2],
                'company' => $row[3],
                'city' => $row[4],
                'country' => $row[5],
                'birthday' => $row[6],
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->chunk($chunkSize)
            ->each(fn ($chunk) => Customer::insert($chunk->all()));
    }


}
