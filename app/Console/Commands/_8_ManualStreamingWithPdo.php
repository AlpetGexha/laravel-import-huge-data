<?php

namespace App\Console\Commands;

use App\Traits\ImportHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class _8_ManualStreamingWithPdo extends Command
{
    use ImportHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-8';

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
        $this->manualStreamingWithPdo($filePath);
    }

    private function manualStreamingWithPdo(string $filePath): void
    {
        // 100 7ms / 0MB
        // 1K 78ms / 0MB
        // 10K 328ms / 0MB
        // 100K 2.9s / 0MB
        // 1M 28s / 0MB

        $data = [];
        $handle = fopen($filePath, 'rb');
        fgetcsv($handle); // skip header
        $now = now()->format('Y-m-d H:i:s');
        $pdo = DB::connection()->getPdo();

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

            if (count($data) === 1000) {
                // Build the SQL query for the chunk
                $columns = array_keys($data[0]);
                $placeholders = rtrim(str_repeat('(?,?,?,?,?,?,?,?,?),', count($data)), ',');

                $sql = 'INSERT INTO customers (' . implode(',', $columns) . ') VALUES ' . $placeholders;

                // Flatten the data array for the query
                $values = [];
                foreach ($data as $row) {
                    $values = array_merge($values, array_values($row));
                }

                $pdo->prepare($sql)->execute($values);
                $data = [];
            }
        }

        if (!empty($data)) {
            $columns = array_keys($data[0]);
            $placeholders = rtrim(str_repeat('(?,?,?,?,?,?,?,?,?),', count($data)), ',');

            $sql = 'INSERT INTO customers (' . implode(',', $columns) . ') VALUES ' . $placeholders;

            $values = [];
            foreach ($data as $row) {
                $values = array_merge($values, array_values($row));
            }

            $pdo->prepare($sql)->execute($values);
        }

        fclose($handle);
    }

}
