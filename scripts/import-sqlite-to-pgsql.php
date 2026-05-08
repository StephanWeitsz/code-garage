<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$options = getopt('', [
    'execute',
    'truncate',
    'include-system',
    'only:',
    'exclude:',
]);

$dryRun = ! array_key_exists('execute', $options);
$truncate = array_key_exists('truncate', $options);
$includeSystem = array_key_exists('include-system', $options);
$only = csvOption($options['only'] ?? '');
$extraExcludes = csvOption($options['exclude'] ?? '');

ensureRequiredPdoDrivers();

$sqlite = DB::connection('sqlite_import');
$pgsql = DB::connection('pgsql_import');

$tables = collect($sqlite->select("
    select name
    from sqlite_master
    where type = 'table'
      and name not like 'sqlite_%'
    order by name
"))->pluck('name')->all();

$systemTables = [
    'cache',
    'cache_locks',
    'failed_jobs',
    'job_batches',
    'jobs',
    'migrations',
    'password_reset_tokens',
    'sessions',
];

if ($only !== []) {
    $tables = array_values(array_intersect($tables, $only));
}

$excludes = $includeSystem ? $extraExcludes : array_merge($systemTables, $extraExcludes);
$tables = array_values(array_diff($tables, $excludes));
$tables = orderTablesByForeignKeys($sqlite, $tables);
$tables = applyProjectTableOrder($tables);

if ($tables === []) {
    writeln('No tables selected for import.');
    exit(0);
}

writeln(($dryRun ? 'DRY RUN: would import' : 'Importing').' these tables in dependency order:');
foreach ($tables as $table) {
    $count = $sqlite->table($table)->count();
    writeln(sprintf('  - %s (%d rows)', $table, $count));
}

if ($dryRun) {
    writeln('');
    writeln('Nothing was written. Run with --execute to import, and add --truncate to clear target tables first.');
    exit(0);
}

ensureTargetTablesExist($tables);

if ($truncate) {
    $quotedTables = implode(', ', array_map('quoteIdentifier', $tables));
    $pgsql->statement("truncate table {$quotedTables} restart identity cascade");
    writeln('Target tables truncated.');
}

$pgsql->transaction(function () use ($sqlite, $pgsql, $tables): void {
    foreach ($tables as $table) {
        $inserted = 0;

        $sqlite->table($table)->orderBy('rowid')->chunk(500, function ($rows) use ($pgsql, $table, &$inserted): void {
            $payload = [];

            foreach ($rows as $row) {
                $payload[] = (array) $row;
            }

            if ($payload !== []) {
                $pgsql->table($table)->insert($payload);
                $inserted += count($payload);
            }
        });

        resetPostgresSequence($pgsql, $table);
        writeln(sprintf('Imported %d rows into %s.', $inserted, $table));
    }
});

writeln('Import complete.');

function csvOption(string $value): array
{
    if ($value === '') {
        return [];
    }

    return array_values(array_filter(array_map('trim', explode(',', $value))));
}

function ensureRequiredPdoDrivers(): void
{
    $missing = [];

    foreach (['sqlite', 'pgsql'] as $driver) {
        if (! in_array($driver, PDO::getAvailableDrivers(), true)) {
            $missing[] = 'pdo_'.$driver;
        }
    }

    if ($missing === []) {
        return;
    }

    throw new RuntimeException(sprintf(
        'Missing PHP PDO driver(s): %s. Install the PostgreSQL PDO extension for your PHP CLI before running this import.',
        implode(', ', $missing)
    ));
}

function orderTablesByForeignKeys($sqlite, array $tables): array
{
    $remaining = array_fill_keys($tables, true);
    $ordered = [];

    while ($remaining !== []) {
        $madeProgress = false;

        foreach (array_keys($remaining) as $table) {
            $parents = collect($sqlite->select('pragma foreign_key_list('.quoteIdentifier($table).')'))
                ->pluck('table')
                ->filter(fn ($parent) => isset($remaining[$parent]))
                ->unique()
                ->all();

            if ($parents === []) {
                $ordered[] = $table;
                unset($remaining[$table]);
                $madeProgress = true;
            }
        }

        if (! $madeProgress) {
            foreach (array_keys($remaining) as $table) {
                $ordered[] = $table;
            }

            break;
        }
    }

    return $ordered;
}

function applyProjectTableOrder(array $tables): array
{
    $preferredOrder = [
        'permissions',
        'roles',
        'role_has_permissions',
        'users',
        'model_has_permissions',
        'model_has_roles',
        'teams',
        'team_user',
        'team_invitations',
        'courses',
        'course_sections',
        'lessons',
        'assignments',
        'assignment_submissions',
        'enrollments',
        'lesson_completions',
        'payments',
        'posts',
        'post_replies',
        'events',
        'course_queries',
        'development_requests',
        'personal_access_tokens',
    ];

    return array_values(array_unique(array_merge(
        array_values(array_intersect($preferredOrder, $tables)),
        $tables
    )));
}

function ensureTargetTablesExist(array $tables): void
{
    foreach ($tables as $table) {
        if (! Schema::connection('pgsql_import')->hasTable($table)) {
            throw new RuntimeException("Target table [{$table}] does not exist. Run migrations on the Postgres database first.");
        }
    }
}

function resetPostgresSequence($pgsql, string $table): void
{
    if (! Schema::connection('pgsql_import')->hasColumn($table, 'id')) {
        return;
    }

    $sequence = $pgsql->selectOne("select pg_get_serial_sequence(?, 'id') as sequence", [$table])->sequence ?? null;

    if ($sequence === null) {
        return;
    }

    $quotedTable = quoteIdentifier($table);
    $pgsql->statement("
        select setval(
            ?,
            coalesce((select max(id) from {$quotedTable}), 1),
            exists(select 1 from {$quotedTable})
        )
    ", [$sequence]);
}

function quoteIdentifier(string $identifier): string
{
    return '"'.str_replace('"', '""', $identifier).'"';
}

function writeln(string $message): void
{
    fwrite(STDOUT, $message.PHP_EOL);
}
