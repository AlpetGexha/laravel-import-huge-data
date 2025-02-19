<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Traits\ImportHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class _7_ManualStreaming extends Command
{
    use ImportHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-7';

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
        $this->manualStreaming($filePath);
    }

    private function manualStreaming(string $filePath): void
    {
        // Read and insert in chunks
        // Better memory management
        // 100 13ms / 0.05MB
        // 1K 39ms / 0.57MB
        // 10K 224ms / 5.69MB
        // 100K 1.8s / 56MB
        // 1M memory issue

        $data = [];
        $chunkSize = 1000;
        $handle = fopen($filePath, 'rb');
        fgetcsv($handle); // skip header
        $now = now()->format('Y-m-d H:i:s');

        while (($row = fgetcsv($handle)) !== false) {
            $data[] = [
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

            if (count($data) === $chunkSize) {
                Customer::insert($data);
                $data = [];
            }
        }

        if (! empty($data)) {
            Customer::insert($data);
        }

        fclose($handle);
    }

}
