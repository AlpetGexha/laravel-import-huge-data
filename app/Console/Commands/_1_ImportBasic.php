<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Traits\ImportHelper;
use Illuminate\Console\Command;

class _1_ImportBasic extends Command
{
    use ImportHelper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-1';

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
        $this->basicOneByOne($filePath);
    }

    private function basicOneByOne(string $filePath): void
    {
        // Most basic approach - one query per record
        // 100 130ms / 0.35MB
        // 1K 549ms / 2MB
        // 10K 5.7s / 19MB
        // 100K memory issue (from mapping)
        // 1M memory issue (from file loading)
        // conclusion: works but slow and in-efficient

        collect(file($filePath))
            ->skip(1)
            ->map(fn($line) => str_getcsv($line))
            ->map(fn($row) => [
                'custom_id' => $row[0],
                'name' => $row[1],
                'email' => $row[2],
                'company' => $row[3],
                'city' => $row[4],
                'country' => $row[5],
                'birthday' => $row[6],
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->each(fn($customer) => Customer::create($customer));
    }
}
