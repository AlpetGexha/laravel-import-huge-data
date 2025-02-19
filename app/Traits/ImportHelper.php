<?php

namespace App\Traits;

use App\Models\Customer;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use PDO;
use PDOStatement;

use function Laravel\Prompts\select;

trait ImportHelper
{
    protected float $benchmarkStartTime;

    protected int $benchmarkStartMemory;

    protected int $startRowCount;

    protected int $startQueries;

    public function handle(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        Customer::truncate();
        $filePath = $this->selectFile();
        $this->startBenchmark();

        try {
            $this->handleImport($filePath);
        } catch (\Exception $e) {
            $this->error(get_class($e).' '.Str::of($e->getMessage())->limit(100)->value());
        }

        $this->endBenchmark();
    }

    protected function selectFile(): string
    {
        $file = select(
            label: 'What file do you want to import?',
            options: ['CSV 100 Customers', 'CSV 1K Customers', 'CSV 10K Customers', 'CSV 100K Customers', 'CSV 1M Customers', 'CSV 2M Customers']
        );

        return match ($file) {
            'CSV 100 Customers' => storage_path('app/public/customers-100.csv'),
            'CSV 1K Customers' => storage_path('app/public/customers-1000.csv'),
            'CSV 10K Customers' => storage_path('app/public/customers-10000.csv'),
            'CSV 100K Customers' => storage_path('app/public/customers-100000.csv'),
            'CSV 1M Customers' => storage_path('app/public/customers-1000000.csv'),
            'CSV 2M Customers' => storage_path('app/public/customers-2000000.csv'),
            'CSV 3M Customers' => storage_path('app/public/customers-3000000.csv'),
        };
    }

    protected function startBenchmark(string $table = 'customers'): void
    {
        $this->startRowCount = DB::table($table)->count();
        $this->benchmarkStartTime = microtime(true);
        $this->benchmarkStartMemory = memory_get_usage();
        DB::enableQueryLog();
        $this->startQueries = DB::select("SHOW SESSION STATUS LIKE 'Questions'")[0]->Value;
    }

    protected function endBenchmark(string $table = 'customers'): void
    {
        $executionTime = microtime(true) - $this->benchmarkStartTime;
        $memoryUsage = round((memory_get_usage() - $this->benchmarkStartMemory) / 1024 / 1024, 2);
        $queriesCount = DB::select("SHOW SESSION STATUS LIKE 'Questions'")[0]->Value - $this->startQueries - 1; // Subtract the Questions query itself

        // Get row count after we've stopped tracking queries
        $rowDiff = DB::table($table)->count() - $this->startRowCount;

        $formattedTime = match (true) {
            $executionTime >= 60 => sprintf('%dm %ds', floor($executionTime / 60), $executionTime % 60),
            $executionTime >= 1 => round($executionTime, 2).'s',
            default => round($executionTime * 1000).'ms',
        };

        $this->newLine();
        $this->line(sprintf(
            '⚡ <bg=bright-blue;fg=black> TIME: %s </> <bg=bright-green;fg=black> MEM: %sMB </> <bg=bright-yellow;fg=black> SQL: %s </> <bg=bright-magenta;fg=black> ROWS: %s </>',
            $formattedTime,
            $memoryUsage,
            number_format($queriesCount),
            number_format($rowDiff)
        ));
        $this->newLine();
    }








    private function prepareChunkedStatement($chunkSize): PDOStatement
    {
        $rowPlaceholders = '(?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $placeholders = implode(',', array_fill(0, $chunkSize, $rowPlaceholders));

        return DB::connection()->getPdo()->prepare("
        INSERT INTO customers (custom_id, name, email, company, city, country, birthday, created_at, updated_at)
        VALUES {$placeholders}
    ");
    }
}
