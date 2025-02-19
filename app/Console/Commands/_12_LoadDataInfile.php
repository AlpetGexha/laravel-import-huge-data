<?php

namespace App\Console\Commands;

use App\Traits\ImportHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

class _12_LoadDataInfile extends Command
{
    use ImportHelper;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import-12';

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
        $this->loadDataInfile($filePath);
    }

    private function loadDataInfile(string $filePath): void
    {
        // MySQL specific, fastest approach
        // 100 10ms / 0MB
        // 1K 29ms / 0MB
        // 10K 115ms / 0MB
        // 100K 567ms / 0MB
        // 1M 5s / 0MB
        // 2M 11s / 0MB

        $pdo = DB::connection()->getPdo();
        $pdo->setAttribute(PDO::MYSQL_ATTR_LOCAL_INFILE, true);

        $filepath = str_replace('\\', '/', $filePath);

        $query = <<<SQL
            LOAD DATA LOCAL INFILE '$filepath'
            INTO TABLE customers
            FIELDS TERMINATED BY ','
            ENCLOSED BY '"'
            LINES TERMINATED BY '\n'
            IGNORE 1 LINES
            (@col1, @col2, @col3, @col4, @col5, @col6, @col7)
            SET
                custom_id = @col1,
                name = @col2,
                email = @col3,
                company = @col4,
                city = @col5,
                country = @col6,
                birthday = @col7,
                created_at = NOW(),
                updated_at = NOW()
    SQL;

        $pdo->exec($query);
    }

}
