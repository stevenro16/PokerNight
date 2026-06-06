<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportSqliteData extends Command
{
    protected $signature = 'db:dump {--output=DB_backup.sql}';
    protected $description = 'Export SQLite data as MySQL-compatible INSERT statements';

    // Tables in FK-safe insertion order; framework tables at the end
    private array $tableOrder = [
        'users',
        'poker_groups',
        'group_members',
        'group_players',
        'poker_nights',
        'game_attendees',
        'game_images',
        'night_comments',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'sessions',
    ];

    public function handle(): int
    {
        $outputFile = $this->option('output');

        $existingTables = collect(DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"))
            ->pluck('name')
            ->toArray();

        $ordered = array_filter($this->tableOrder, fn($t) => in_array($t, $existingTables));
        $remaining = array_diff($existingTables, $this->tableOrder, ['migrations']);
        $tables = array_merge(array_values($ordered), array_values($remaining));

        $lines = [];
        $lines[] = '-- Poker Night — Data Backup';
        $lines[] = '-- Generated: ' . now()->toDateTimeString();
        $lines[] = '-- Run deploy/mysql-schema.sql first to create tables, then import this file.';
        $lines[] = '';
        $lines[] = 'SET FOREIGN_KEY_CHECKS = 0;';
        $lines[] = "SET NAMES 'utf8mb4';";
        $lines[] = '';

        foreach ($tables as $table) {
            $rows = DB::table($table)->get()->map(fn($r) => (array) $r)->toArray();

            if (empty($rows)) {
                continue;
            }

            $lines[] = "-- {$table} (" . count($rows) . ' rows)';

            foreach ($rows as $row) {
                $cols = '`' . implode('`, `', array_keys($row)) . '`';
                $vals = implode(', ', array_map([$this, 'escape'], array_values($row)));
                $lines[] = "INSERT INTO `{$table}` ({$cols}) VALUES ({$vals});";
            }

            $lines[] = '';
        }

        $lines[] = 'SET FOREIGN_KEY_CHECKS = 1;';

        file_put_contents($outputFile, implode("\n", $lines) . "\n");

        $rowCount = array_sum(array_map(fn($t) => DB::table($t)->count(), $tables));
        $this->info("Exported {$rowCount} rows across " . count($tables) . " tables → {$outputFile}");

        return 0;
    }

    private function escape(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        // Escape for MySQL string literal
        return "'" . str_replace(
            ['\\', "'", "\n", "\r", "\x1a"],
            ['\\\\', "\\'", '\\n', '\\r', '\\Z'],
            (string) $value
        ) . "'";
    }
}
