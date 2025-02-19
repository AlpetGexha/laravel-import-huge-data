<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Traits\ImportHelper;
use Illuminate\Console\Command;

class _2_CollectAndInsert extends Command
{
    use ImportHelper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-2';

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
        $this->collectAndInsert($filePath);
    }

    private function collectAndInsert(string $filePath): void
    {
        // Collect all and single insert
        // Shows prepared statement limit with large datasets
        // 100 16ms / 0.05MB
        // 1K 62ms / 0.57MB
        // 10K prepared statement issue
        // 100K memory issue
        // 1M memory issue
        // conclusion: prepared statement placeholder issue and memory again

        $now = now()->format('Y-m-d H:i:s');

        $allCustomers = collect(file($filePath))
            ->skip(1)
            ->map(fn ($line) => str_getcsv($line))
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
            ]);

        Customer::insert($allCustomers->all());
    }

}
