<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BenchmarkCommand extends Command
{
    protected $signature = 'benchmark';

    private function data(): array
    {
        $data = [];

        for ($i = 0; $i < 5_000; $i++) {
            $data[] = [
                'col_index' => Str::random(10),
                'col_string' => Str::random(10),
                'col_int' => fake()->numberBetween(),
                'col_float' => fake()->randomFloat(),
            ];
        }

        return $data;
    }

    public function handle(): void
    {
        $this->line('Reset previous state');

        if (!File::exists(base_path('database.sqlite'))) {
            File::put(base_path('database.sqlite'), '');
        }

        Schema::connection('sqlite')->dropIfExists('test');
        Schema::connection('mysql')->dropIfExists('test');

        $this->line('Create SQLite test table');

        DB::connection('sqlite')->statement('CREATE TABLE test (
            id INTEGER PRIMARY KEY,
            col_index TEXT NOT NULL,
            col_string TEXT NOT NULL,
            col_int INTEGER NOT NULL,
            col_float REAL NOT NULL
        )');

        DB::connection('sqlite')->statement('CREATE INDEX test_col_index ON test (col_index)');

        DB::connection('sqlite')->statement('PRAGMA journal_mode = WAL');

        DB::connection('sqlite')->statement('PRAGMA synchronous = NORMAL');

        DB::connection('sqlite')->statement('PRAGMA cache_size=10000');

        $this->line('Create MySQL test table');

        DB::connection('mysql')->statement('CREATE TABLE test (
            id BIGINT UNSIGNED AUTO_INCREMENT,
            col_index VARCHAR(255) NOT NULL,
            col_string VARCHAR(255) NOT NULL,
            col_int INT UNSIGNED NOT NULL,
            col_float FLOAT NOT NULL,
            PRIMARY KEY (id)
        )');

        DB::connection('mysql')->statement('CREATE INDEX test_col_index_index ON test (col_index)');

        $this->line('Generate random seed data');
        $this->newLine();

        $data = $this->data();

        $results = [
            'mysql-single-write' => $this->benchmarkMySQLSingleWrites($data[0]),
            'sqlite-single-write' => $this->benchmarkSQLiteSingleWrites($data[0]),
            'mysql-bulk-write' => $this->benchmarkMySQLBulkWrites($data),
            'sqlite-bulk-write' => $this->benchmarkSQLiteBulkWrites($data),
            'mysql-single-read' => $this->benchmarkMySQLSingleReads(),
            'sqlite-single-read' => $this->benchmarkSQLiteSingleReads(),
            'mysql-bulk-read' => $this->benchmarkMySQLBulkReads(),
            'sqlite-bulk-read' => $this->benchmarkSQLiteBulkReads(),
        ];

        $this->table(array_keys($results), [array_values($results)]);
    }

    private function benchmarkMySQLSingleWrites(array $data): string
    {
        $this->info('Benchmark single line writes for MySQL');

        $start = microtime(true);

        for ($i = 0; $i < 5_000; $i++) {
            DB::connection('mysql')->table('test')->insert($data);
        }

        $result = round(5_000 / (microtime(true) - $start), 2) . ' rps';

        $this->line('Found: ' . $result);
        $this->newLine();

        return $result;
    }

    private function benchmarkSQLiteSingleWrites(array $data): string
    {
        $this->info('Benchmark single line writes for SQLite');

        $start = microtime(true);

        for ($i = 0; $i < 5_000; $i++) {
            DB::connection('sqlite')->table('test')->insert($data);
        }

        $result = round(5_000 / (microtime(true) - $start), 2) . ' rps';

        $this->line('Found: ' . $result);
        $this->newLine();

        return $result;
    }

    private function benchmarkMySQLBulkWrites(array $data): string
    {
        $this->info('Benchmark bulk writes for MySQL');

        $start = microtime(true);

        for ($i = 0; $i < 200; $i++) {
            DB::connection('mysql')->table('test')->insert($data);
        }

        $result = round(200 / (microtime(true) - $start), 2) . ' rps';

        $this->line('Found: ' . $result);
        $this->newLine();

        return $result;
    }

    private function benchmarkSQLiteBulkWrites(array $data): string
    {
        $this->info('Benchmark bulk writes for SQLite');

        $start = microtime(true);

        for ($i = 0; $i < 200; $i++) {
            DB::connection('sqlite')->table('test')->insert($data);
        }

        $result = round(200 / (microtime(true) - $start), 2) . ' rps';

        $this->line('Found: ' . $result);
        $this->newLine();

        return $result;
    }

    private function benchmarkMySQLSingleReads(): string
    {
        $this->info('Benchmark single line reads for MySQL');

        $start = microtime(true);

        for ($i = 0; $i < 5_000; $i++) {
            DB::connection('mysql')->table('test')->limit(1)->orderBy('id', 'DESC')->get();
        }

        $result = round(5_000 / (microtime(true) - $start), 2) . ' rps';

        $this->line('Found: ' . $result);
        $this->newLine();

        return $result;
    }

    private function benchmarkSQLiteSingleReads(): string
    {
        $this->info('Benchmark single line reads for SQLite');

        $start = microtime(true);

        for ($i = 0; $i < 5_000; $i++) {
            DB::connection('sqlite')->table('test')->limit(1)->orderBy('id', 'DESC')->get();
        }

        $result = round(5_000 / (microtime(true) - $start), 2) . ' rps';

        $this->line('Found: ' . $result);
        $this->newLine();

        return $result;
    }

    private function benchmarkMySQLBulkReads(): string
    {
        $this->info('Benchmark bulk reads for MySQL');

        $start = microtime(true);

        for ($i = 0; $i < 5_000; $i++) {
            DB::connection('mysql')->table('test')->limit(5_000)->orderBy('id', 'DESC')->get();
        }

        $result = round(5_000 / (microtime(true) - $start), 2) . ' rps';

        $this->line('Found: ' . $result);
        $this->newLine();

        return $result;
    }

    private function benchmarkSQLiteBulkReads(): string
    {
        $this->info('Benchmark bulk reads for SQLite');

        $start = microtime(true);

        for ($i = 0; $i < 5_000; $i++) {
            DB::connection('sqlite')->table('test')->limit(5_000)->orderBy('id', 'DESC')->get();
        }

        $result = round(5_000 / (microtime(true) - $start), 2) . ' rps';

        $this->line('Found: ' . $result);
        $this->newLine();

        return $result;
    }
}
