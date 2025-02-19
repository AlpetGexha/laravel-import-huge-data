<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Traits\ImportHelper;
use Illuminate\Console\Command;

class _3_CollectAndChunk extends Command
{
    use ImportHelper;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-3';

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
        $this->collectAndChunk($filePath);
    }

    private function collectAndChunk(string $filePath): void
    {
        // Collect all but insert in chunks
        // Still has memory issues with large files
        // 100 15ms / 0.05MB
        // 1K 65ms / 0.57MB
        // 10K 246ms / 5,7MB
        // 100K 2.6s / 56.97MB
        // 1M memory issue (while file loading)
        // Conclusion super fast till 1M

        $now = now()->format('Y-m-d H:i:s');

        collect(file($filePath))
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
            ])
            ->chunk(1000)
            ->each(fn ($chunk) => Customer::insert($chunk->all()));
    }


}
