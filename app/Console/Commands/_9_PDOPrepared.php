<?php

namespace App\Console\Commands;

use App\Traits\ImportHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class _9_PDOPrepared extends Command
{
    use ImportHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-9';

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
        $this->PDOPrepared($filePath);
    }

    private function PDOPrepared(string $filePath): void
    {
        // Direct database connection with prepared statements
        // 100 41ms / 0 MB
        // 1K 237ms /
        // 10K 2.21s
        // 100K 25.27s
        // 1M 4m43s
        // 2M
        $now = now()->format('Y-m-d H:i:s');
        $handle = fopen($filePath, 'r');
        fgets($handle); // skip header

        try {
            $pdo = DB::connection()->getPdo();
            $stmt = $pdo->prepare('
            INSERT INTO customers (custom_id, name, email, company, city, country, birthday, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');

            while (($row = fgetcsv($handle)) !== false) {
                $stmt->execute([
                    $row[0],
                    $row[1],
                    $row[2],
                    $row[3],
                    $row[4],
                    $row[5],
                    $row[6],
                    $now,
                    $now,
                ]);
            }
        } finally {
            fclose($handle);
        }
    }

}
