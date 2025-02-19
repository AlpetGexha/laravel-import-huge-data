<?php

namespace App\Console\Commands;

use App\Traits\ImportHelper;
use Illuminate\Console\Command;

class _10_PDOPreparedChunked extends Command
{
    use ImportHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-10';

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
        $this->PDOPreparedChunked($filePath);
    }

    private function PDOPreparedChunked(string $filePath): void
    {
        // Direct database connection with prepared statements
        // 100 12ms / 0.15MB
        // 1K 49ms / 0.74MB
        // 10K 222ms / 0.74MB
        // 100K 1.5s / 0.74MB
        // 1M 15.3s / 0.74MB
        // 2M 24s / 0.74MN

        $now = now()->format('Y-m-d H:i:s');
        $handle = fopen($filePath, 'r');
        fgetcsv($handle); // skip header
        $chunkSize = 500;
        $chunks = [];

        try {
            $stmt = $this->prepareChunkedStatement($chunkSize);

            while (($row = fgetcsv($handle)) !== false) {
                $chunks = array_merge($chunks, [
                    $row[0], $row[1], $row[2], $row[3], $row[4],
                    $row[5], $row[6], $now, $now,
                ]);

                if (count($chunks) === $chunkSize * 9) {  // 9 columns
                    $stmt->execute($chunks);
                    $chunks = [];
                }
            }

            // Handle remaining records
            if (!empty($chunks)) {
                $remainingRows = count($chunks) / 9;
                $stmt = $this->prepareChunkedStatement($remainingRows);
                $stmt->execute($chunks);
            }
        } finally {
            fclose($handle);
        }
    }

}
