<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateSqliteToPgsql extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:sqlite-to-pgsql';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from local SQLite database to PostgreSQL database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('--------------------------------------------');
        $this->info('Starting Migration: SQLite -> PostgreSQL');
        $this->info('--------------------------------------------');

        // Force SQLite connection to use the local file path, even if DB_DATABASE env is overridden by PGSQL
        config(['database.connections.sqlite.database' => database_path('database.sqlite')]);

        // 1. Run migrations on pgsql connection first to prepare tables
        $this->info('Step 1: Running migrations on the PostgreSQL connection...');
        try {
            $this->call('migrate', [
                '--database' => 'pgsql',
                '--force' => true,
            ]);
        } catch (\Exception $e) {
            $this->error('Failed to run migrations on PostgreSQL connection: ' . $e->getMessage());
            return 1;
        }

        // Tables to migrate
        $tables = ['users', 'memories', 'wishlists'];

        // Disable foreign key checks temporarily if needed, though clean migration doesn't strictly require it
        $this->info('Step 2: Copying table data...');

        foreach ($tables as $table) {
            $this->info(" - Processing table: {$table}");

            if (!Schema::connection('sqlite')->hasTable($table)) {
                $this->warn("   Table '{$table}' does not exist in SQLite database. Skipping.");
                continue;
            }

            // Clear target table in PostgreSQL to prevent duplicate key violations
            DB::connection('pgsql')->table($table)->truncate();

            // Fetch records from local SQLite
            $rows = DB::connection('sqlite')->table($table)->get();

            if ($rows->isEmpty()) {
                $this->info("   Table '{$table}' is empty in SQLite database. Skipping data copy.");
                continue;
            }

            // Prepare records for bulk insert
            $data = [];
            foreach ($rows as $row) {
                $data[] = (array) $row;
            }

            // Insert into PostgreSQL
            try {
                DB::connection('pgsql')->table($table)->insert($data);
                $this->info("   Successfully copied " . count($data) . " rows to PostgreSQL.");
            } catch (\Exception $e) {
                $this->error("   Failed to insert data into PostgreSQL for table '{$table}': " . $e->getMessage());
                continue;
            }

            // Reset primary key sequence in PostgreSQL (crucial so that future inserts don't throw duplicate key errors)
            $this->info("   Resetting database primary key sequence...");
            try {
                DB::connection('pgsql')->statement("
                    SELECT setval(
                        pg_get_serial_sequence('{$table}', 'id'),
                        COALESCE((SELECT MAX(id) FROM {$table}), 1)
                    );
                ");
                $this->info("   Sequence reset successfully.");
            } catch (\Exception $e) {
                $this->warn("   Could not reset sequence for table '{$table}' (it might not have a serial sequence): " . $e->getMessage());
            }
        }

        $this->info('--------------------------------------------');
        $this->info('Migration completed successfully!');
        $this->info('--------------------------------------------');

        return 0;
    }
}
