<?php

namespace App\Console\Commands;

use App\Traits\ImportHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class _6_LazyCollectionWithChunkingAndPDO extends Command
{
    use ImportHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-6';

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
        $this->lazyCollectionWithChunkingAndPdo($filePath);
    }

    private function lazyCollectionWithChunkingAndPdo(string $filePath): void
    {
        // 100 10ms / 0.23MB
        // 1K 51ms / 0.23MB
        // 10K 234ms / 0.23MB
        // 100K 2s / 0.23MB
        // 1M 20s / 0.23MB

        $now = now()->format('Y-m-d H:i:s');
        $pdo = DB::connection()->getPdo();

        LazyCollection::make(function () use ($filePath) {
            $handle = fopen($filePath, 'rb');
            fgetcsv($handle); // skip header

            while (($line = fgetcsv($handle)) !== false) {
                yield $line;
            }
            fclose($handle);
        })
            ->filter(fn($row) => filter_var($row[2], FILTER_VALIDATE_EMAIL))  // Nice filtering syntax
            ->chunk(1000)
            ->each(function ($chunk) use ($pdo, $now) {
                //It uses PDO prepared statements, which reduces ORM overhead.

                // Build SQL for this chunk
                $placeholders = rtrim(str_repeat('(?,?,?,?,?,?,?,?,?),', $chunk->count()), ',');
                $sql = 'INSERT INTO customers (custom_id, name, email, company, city, country, birthday, created_at, updated_at)
                VALUES ' . $placeholders;

                // Prepare values
                $values = $chunk->flatMap(fn($row) => [
                    $row[0], $row[1], $row[2], $row[3], $row[4],
                    $row[5], $row[6], $now, $now,
                ])->all();

                $pdo->prepare($sql)->execute($values);
            });
    }

}
