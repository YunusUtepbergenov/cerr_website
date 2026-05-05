<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class RepairSchema extends Command
{
    protected $signature = 'db:repair-schema
        {--dry-run : Audit only, do not modify the database}
        {--force : Skip the interactive confirmation prompt}
        {--no-backup : Skip the pg_dump step (assumes you have a backup)}';

    protected $description = 'Restore primary keys, sequences, foreign keys, indexes, and column types on a PostgreSQL database that was rebuilt without them.';

    /**
     * Tables expected to have an `id` primary key in this app.
     */
    private const ID_TABLES = [
        'users',
        'categories',
        'category_translations',
        'tags',
        'news',
        'news_translations',
        'pages',
        'page_translations',
        'migrations',
    ];

    public function handle(): int
    {
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'pgsql') {
            $this->error("This command only supports PostgreSQL. Current driver: {$driver}");

            return self::FAILURE;
        }

        $this->info('=== Schema audit ===');

        $auditFindings = $this->audit();

        if (empty($auditFindings)) {
            $this->info('Nothing to repair — every audited table already has a primary key.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->warn('The following problems were detected:');
        foreach ($auditFindings as $line) {
            $this->line('  • '.$line);
        }
        $this->newLine();

        $rowsBefore = $this->rowCounts();
        $this->info('Current row counts (must match after repair):');
        foreach ($rowsBefore as $table => $count) {
            $this->line(sprintf('  %-25s %d rows', $table, $count));
        }
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->info('Dry run — no changes made. Re-run without --dry-run to repair.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Apply schema repair to the connected database now?', false)) {
            $this->info('Aborted.');

            return self::SUCCESS;
        }

        if (! $this->option('no-backup')) {
            $this->info('Attempting pg_dump backup…');
            $backupPath = $this->backup();

            if ($backupPath === null) {
                if (! $this->option('force') && ! $this->confirm('Backup failed. Continue anyway? (NOT RECOMMENDED)', false)) {
                    $this->error('Aborted — please take a manual backup and re-run with --no-backup.');

                    return self::FAILURE;
                }
            } else {
                $this->info("Backup written to: {$backupPath}");
            }
        }

        $this->info('Applying repair script…');

        try {
            DB::unprepared($this->repairSql());
        } catch (Throwable $e) {
            $this->error('Repair failed: '.$e->getMessage());
            $this->error('The transaction was rolled back. Database is unchanged.');

            return self::FAILURE;
        }

        $rowsAfter = $this->rowCounts();
        $mismatches = [];
        foreach ($rowsBefore as $table => $before) {
            $after = $rowsAfter[$table] ?? null;
            if ($after !== $before && ! in_array($table, ['sessions'], true)) {
                $mismatches[] = sprintf('%s: before=%d after=%s', $table, $before, $after ?? 'missing');
            }
        }

        if (! empty($mismatches)) {
            $this->error('Row count mismatch detected:');
            foreach ($mismatches as $m) {
                $this->line('  • '.$m);
            }
            $this->error('Restore from backup if data was lost.');

            return self::FAILURE;
        }

        $this->info('Repair complete.');
        $this->info('All row counts match.');
        $this->warn('Note: the sessions table was rebuilt — all users will need to log in again.');

        return self::SUCCESS;
    }

    /**
     * Build a list of human-readable problems found in the schema.
     *
     * @return list<string>
     */
    private function audit(): array
    {
        $findings = [];

        foreach (self::ID_TABLES as $table) {
            if (! $this->tableExists($table)) {
                $findings[] = "{$table}: table missing";

                continue;
            }
            if (! $this->hasPrimaryKey($table)) {
                $findings[] = "{$table}: missing primary key on id";
            }
            if (! $this->columnIsNotNull($table, 'id')) {
                $findings[] = "{$table}: id column is nullable";
            }
            if ($this->columnDefault($table, 'id') === null) {
                $findings[] = "{$table}: id has no autoincrement sequence";
            }
            $nullIds = (int) DB::table($table)->whereNull('id')->count();
            if ($nullIds > 0) {
                $findings[] = "{$table}: {$nullIds} row(s) have NULL id";
            }
        }

        $textTimestampColumns = [
            'news' => ['scheduled_at', 'updated_at'],
            'pages' => ['created_at', 'updated_at'],
            'page_translations' => ['created_at', 'updated_at'],
        ];
        foreach ($textTimestampColumns as $table => $cols) {
            if (! $this->tableExists($table)) {
                continue;
            }
            foreach ($cols as $col) {
                $type = $this->columnType($table, $col);
                if ($type === 'text') {
                    $findings[] = "{$table}.{$col}: stored as text, should be timestamp";
                }
            }
        }

        if ($this->tableExists('sessions') && $this->columnType('sessions', 'user_id') === 'text') {
            $findings[] = 'sessions.user_id: stored as text, should be bigint';
        }

        if ($this->tableExists('news_tags') && $this->columnType('news_tags', 'news_id') === 'text') {
            $findings[] = 'news_tags: news_id/tag_id stored as text, should be bigint';
        }

        return $findings;
    }

    /**
     * @return array<string, int>
     */
    private function rowCounts(): array
    {
        $tables = ['users', 'categories', 'category_translations', 'tags', 'news', 'news_translations', 'news_tags', 'pages', 'page_translations', 'videos', 'sessions'];
        $counts = [];
        foreach ($tables as $t) {
            if ($this->tableExists($t)) {
                $counts[$t] = (int) DB::table($t)->count();
            }
        }

        return $counts;
    }

    private function tableExists(string $table): bool
    {
        $row = DB::selectOne('SELECT to_regclass(?) AS r', [$table]);

        return $row !== null && $row->r !== null;
    }

    private function hasPrimaryKey(string $table): bool
    {
        $rows = DB::select(
            'SELECT 1 FROM pg_index WHERE indrelid = ?::regclass AND indisprimary',
            [$table]
        );

        return count($rows) > 0;
    }

    private function columnIsNotNull(string $table, string $column): bool
    {
        $row = DB::selectOne(
            'SELECT is_nullable FROM information_schema.columns WHERE table_name = ? AND column_name = ?',
            [$table, $column]
        );

        return $row !== null && $row->is_nullable === 'NO';
    }

    private function columnDefault(string $table, string $column): ?string
    {
        $row = DB::selectOne(
            'SELECT column_default FROM information_schema.columns WHERE table_name = ? AND column_name = ?',
            [$table, $column]
        );

        return $row?->column_default;
    }

    private function columnType(string $table, string $column): ?string
    {
        $row = DB::selectOne(
            'SELECT data_type FROM information_schema.columns WHERE table_name = ? AND column_name = ?',
            [$table, $column]
        );

        return $row?->data_type;
    }

    private function backup(): ?string
    {
        $config = config('database.connections.'.config('database.default'));
        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 5432;
        $user = $config['username'] ?? 'postgres';
        $pass = $config['password'] ?? '';
        $db = $config['database'] ?? '';

        $dir = storage_path('backups');
        if (! is_dir($dir) && ! @mkdir($dir, 0755, true)) {
            $this->warn("Could not create backup directory at {$dir}.");

            return null;
        }

        $path = $dir.'/cerr-pre-repair-'.date('Ymd-His').'.dump';

        $cmd = sprintf(
            'PGPASSWORD=%s pg_dump -h %s -p %s -U %s -F c -f %s %s 2>&1',
            escapeshellarg((string) $pass),
            escapeshellarg((string) $host),
            escapeshellarg((string) $port),
            escapeshellarg((string) $user),
            escapeshellarg($path),
            escapeshellarg((string) $db)
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0) {
            $this->warn('pg_dump failed:');
            foreach ($output as $line) {
                $this->line('  '.$line);
            }
            @unlink($path);

            return null;
        }

        return $path;
    }

    private function repairSql(): string
    {
        return <<<'SQL'
BEGIN;

UPDATE users SET id = sub.new_id
FROM (
    SELECT ctid, COALESCE(id, (SELECT COALESCE(MAX(id), 0) FROM users) + ROW_NUMBER() OVER (ORDER BY created_at NULLS LAST, ctid)) AS new_id
    FROM users WHERE id IS NULL
) sub
WHERE users.ctid = sub.ctid;

UPDATE categories SET id = sub.new_id
FROM (
    SELECT ctid, (SELECT COALESCE(MAX(id), 0) FROM categories) + ROW_NUMBER() OVER (ORDER BY created_at NULLS LAST, ctid) AS new_id
    FROM categories WHERE id IS NULL
) sub
WHERE categories.ctid = sub.ctid;

UPDATE category_translations SET id = sub.new_id
FROM (
    SELECT ctid, (SELECT COALESCE(MAX(id), 0) FROM category_translations) + ROW_NUMBER() OVER (ORDER BY created_at NULLS LAST, ctid) AS new_id
    FROM category_translations WHERE id IS NULL
) sub
WHERE category_translations.ctid = sub.ctid;

UPDATE tags SET id = sub.new_id
FROM (
    SELECT ctid, (SELECT COALESCE(MAX(id), 0) FROM tags) + ROW_NUMBER() OVER (ORDER BY created_at NULLS LAST, ctid) AS new_id
    FROM tags WHERE id IS NULL
) sub
WHERE tags.ctid = sub.ctid;

UPDATE news SET id = sub.new_id
FROM (
    SELECT ctid, (SELECT COALESCE(MAX(id), 0) FROM news) + ROW_NUMBER() OVER (ORDER BY created_at NULLS LAST, ctid) AS new_id
    FROM news WHERE id IS NULL
) sub
WHERE news.ctid = sub.ctid;

UPDATE news_translations SET id = sub.new_id
FROM (
    SELECT ctid, (SELECT COALESCE(MAX(id), 0) FROM news_translations) + ROW_NUMBER() OVER (ORDER BY created_at NULLS LAST, ctid) AS new_id
    FROM news_translations WHERE id IS NULL
) sub
WHERE news_translations.ctid = sub.ctid;

UPDATE pages SET id = sub.new_id
FROM (
    SELECT ctid, (SELECT COALESCE(MAX(id), 0) FROM pages) + ROW_NUMBER() OVER (ORDER BY ctid) AS new_id
    FROM pages WHERE id IS NULL
) sub
WHERE pages.ctid = sub.ctid;

UPDATE page_translations SET id = sub.new_id
FROM (
    SELECT ctid, (SELECT COALESCE(MAX(id), 0) FROM page_translations) + ROW_NUMBER() OVER (ORDER BY ctid) AS new_id
    FROM page_translations WHERE id IS NULL
) sub
WHERE page_translations.ctid = sub.ctid;

UPDATE migrations SET id = sub.new_id
FROM (
    SELECT ctid, (SELECT COALESCE(MAX(id), 0) FROM migrations) + ROW_NUMBER() OVER (ORDER BY batch, ctid) AS new_id
    FROM migrations WHERE id IS NULL
) sub
WHERE migrations.ctid = sub.ctid;

DO $$ BEGIN
    IF (SELECT data_type FROM information_schema.columns WHERE table_name='news' AND column_name='scheduled_at') = 'text' THEN
        ALTER TABLE news ALTER COLUMN scheduled_at TYPE timestamp without time zone USING NULLIF(scheduled_at, '')::timestamp without time zone;
    END IF;
    IF (SELECT data_type FROM information_schema.columns WHERE table_name='news' AND column_name='updated_at') = 'text' THEN
        ALTER TABLE news ALTER COLUMN updated_at TYPE timestamp without time zone USING NULLIF(updated_at, '')::timestamp without time zone;
    END IF;
    IF (SELECT data_type FROM information_schema.columns WHERE table_name='pages' AND column_name='created_at') = 'text' THEN
        ALTER TABLE pages ALTER COLUMN created_at TYPE timestamp without time zone USING NULLIF(created_at, '')::timestamp without time zone;
    END IF;
    IF (SELECT data_type FROM information_schema.columns WHERE table_name='pages' AND column_name='updated_at') = 'text' THEN
        ALTER TABLE pages ALTER COLUMN updated_at TYPE timestamp without time zone USING NULLIF(updated_at, '')::timestamp without time zone;
    END IF;
    IF (SELECT data_type FROM information_schema.columns WHERE table_name='page_translations' AND column_name='created_at') = 'text' THEN
        ALTER TABLE page_translations ALTER COLUMN created_at TYPE timestamp without time zone USING NULLIF(created_at, '')::timestamp without time zone;
    END IF;
    IF (SELECT data_type FROM information_schema.columns WHERE table_name='page_translations' AND column_name='updated_at') = 'text' THEN
        ALTER TABLE page_translations ALTER COLUMN updated_at TYPE timestamp without time zone USING NULLIF(updated_at, '')::timestamp without time zone;
    END IF;
    IF (SELECT data_type FROM information_schema.columns WHERE table_name='sessions' AND column_name='user_id') = 'text' THEN
        ALTER TABLE sessions ALTER COLUMN user_id TYPE bigint USING NULLIF(user_id, '')::bigint;
    END IF;
END $$;

ALTER TABLE users ALTER COLUMN id SET NOT NULL;
CREATE SEQUENCE IF NOT EXISTS users_id_seq AS bigint OWNED BY users.id;
SELECT setval('users_id_seq', GREATEST((SELECT COALESCE(MAX(id), 0) FROM users), 1));
ALTER TABLE users ALTER COLUMN id SET DEFAULT nextval('users_id_seq');
ALTER TABLE users ADD PRIMARY KEY (id);
ALTER TABLE users ALTER COLUMN name SET NOT NULL;
ALTER TABLE users ALTER COLUMN email SET NOT NULL;
ALTER TABLE users ALTER COLUMN password SET NOT NULL;
ALTER TABLE users ALTER COLUMN role SET DEFAULT 'viewer';
UPDATE users SET role = 'viewer' WHERE role IS NULL;
ALTER TABLE users ALTER COLUMN role SET NOT NULL;
ALTER TABLE users ADD CONSTRAINT users_email_unique UNIQUE (email);

ALTER TABLE categories ALTER COLUMN id SET NOT NULL;
CREATE SEQUENCE IF NOT EXISTS categories_id_seq AS bigint OWNED BY categories.id;
SELECT setval('categories_id_seq', GREATEST((SELECT COALESCE(MAX(id), 0) FROM categories), 1));
ALTER TABLE categories ALTER COLUMN id SET DEFAULT nextval('categories_id_seq');
ALTER TABLE categories ADD PRIMARY KEY (id);
ALTER TABLE categories ALTER COLUMN slug SET NOT NULL;
UPDATE categories SET status = true WHERE status IS NULL;
ALTER TABLE categories ALTER COLUMN status SET DEFAULT true;
ALTER TABLE categories ALTER COLUMN status SET NOT NULL;
ALTER TABLE categories ADD CONSTRAINT categories_slug_unique UNIQUE (slug);

ALTER TABLE category_translations ALTER COLUMN id SET NOT NULL;
CREATE SEQUENCE IF NOT EXISTS category_translations_id_seq AS bigint OWNED BY category_translations.id;
SELECT setval('category_translations_id_seq', GREATEST((SELECT COALESCE(MAX(id), 0) FROM category_translations), 1));
ALTER TABLE category_translations ALTER COLUMN id SET DEFAULT nextval('category_translations_id_seq');
ALTER TABLE category_translations ADD PRIMARY KEY (id);
ALTER TABLE category_translations ALTER COLUMN category_id SET NOT NULL;
ALTER TABLE category_translations ALTER COLUMN language SET NOT NULL;
ALTER TABLE category_translations ALTER COLUMN name SET NOT NULL;
ALTER TABLE category_translations
    ADD CONSTRAINT category_translations_category_id_foreign
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE;

ALTER TABLE tags ALTER COLUMN id SET NOT NULL;
CREATE SEQUENCE IF NOT EXISTS tags_id_seq AS bigint OWNED BY tags.id;
SELECT setval('tags_id_seq', GREATEST((SELECT COALESCE(MAX(id), 0) FROM tags), 1));
ALTER TABLE tags ALTER COLUMN id SET DEFAULT nextval('tags_id_seq');
ALTER TABLE tags ADD PRIMARY KEY (id);
ALTER TABLE tags ALTER COLUMN name SET NOT NULL;
ALTER TABLE tags ADD CONSTRAINT tags_name_unique UNIQUE (name);

ALTER TABLE news ALTER COLUMN id SET NOT NULL;
CREATE SEQUENCE IF NOT EXISTS news_id_seq AS bigint OWNED BY news.id;
SELECT setval('news_id_seq', GREATEST((SELECT COALESCE(MAX(id), 0) FROM news), 1));
ALTER TABLE news ALTER COLUMN id SET DEFAULT nextval('news_id_seq');
ALTER TABLE news ADD PRIMARY KEY (id);
ALTER TABLE news ALTER COLUMN slug SET NOT NULL;
UPDATE news SET is_main = false WHERE is_main IS NULL;
ALTER TABLE news ALTER COLUMN is_main SET DEFAULT false;
ALTER TABLE news ALTER COLUMN is_main SET NOT NULL;
UPDATE news SET view_count = 0 WHERE view_count IS NULL;
ALTER TABLE news ALTER COLUMN view_count SET DEFAULT 0;
ALTER TABLE news ALTER COLUMN view_count SET NOT NULL;
UPDATE news SET status = 'draft' WHERE status IS NULL OR status = '';
ALTER TABLE news ALTER COLUMN status SET DEFAULT 'draft';
ALTER TABLE news ALTER COLUMN status SET NOT NULL;
ALTER TABLE news ADD CONSTRAINT news_slug_unique UNIQUE (slug);
ALTER TABLE news
    ADD CONSTRAINT news_category_id_foreign
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL;
ALTER TABLE news
    ADD CONSTRAINT news_user_id_foreign
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

ALTER TABLE news_translations ALTER COLUMN id SET NOT NULL;
CREATE SEQUENCE IF NOT EXISTS news_translations_id_seq AS bigint OWNED BY news_translations.id;
SELECT setval('news_translations_id_seq', GREATEST((SELECT COALESCE(MAX(id), 0) FROM news_translations), 1));
ALTER TABLE news_translations ALTER COLUMN id SET DEFAULT nextval('news_translations_id_seq');
ALTER TABLE news_translations ADD PRIMARY KEY (id);
ALTER TABLE news_translations ALTER COLUMN news_id SET NOT NULL;
ALTER TABLE news_translations ALTER COLUMN lang SET NOT NULL;
ALTER TABLE news_translations ALTER COLUMN title SET NOT NULL;
UPDATE news_translations SET short_description = '' WHERE short_description IS NULL;
ALTER TABLE news_translations ALTER COLUMN short_description SET NOT NULL;
UPDATE news_translations SET content = '' WHERE content IS NULL;
ALTER TABLE news_translations ALTER COLUMN content SET NOT NULL;
UPDATE news_translations SET image_url = '' WHERE image_url IS NULL;
ALTER TABLE news_translations ALTER COLUMN image_url SET NOT NULL;
ALTER TABLE news_translations
    ADD CONSTRAINT news_translations_news_id_foreign
    FOREIGN KEY (news_id) REFERENCES news(id) ON DELETE CASCADE;

DROP TABLE IF EXISTS news_tags;
CREATE TABLE news_tags (
    news_id bigint NOT NULL REFERENCES news(id) ON DELETE CASCADE,
    tag_id  bigint NOT NULL REFERENCES tags(id) ON DELETE CASCADE,
    PRIMARY KEY (news_id, tag_id)
);

ALTER TABLE pages ALTER COLUMN id SET NOT NULL;
CREATE SEQUENCE IF NOT EXISTS pages_id_seq AS bigint OWNED BY pages.id;
SELECT setval('pages_id_seq', GREATEST((SELECT COALESCE(MAX(id), 0) FROM pages), 1));
ALTER TABLE pages ALTER COLUMN id SET DEFAULT nextval('pages_id_seq');
ALTER TABLE pages ADD PRIMARY KEY (id);
ALTER TABLE pages ALTER COLUMN slug SET NOT NULL;
ALTER TABLE pages ADD CONSTRAINT pages_slug_unique UNIQUE (slug);

ALTER TABLE page_translations ALTER COLUMN id SET NOT NULL;
CREATE SEQUENCE IF NOT EXISTS page_translations_id_seq AS bigint OWNED BY page_translations.id;
SELECT setval('page_translations_id_seq', GREATEST((SELECT COALESCE(MAX(id), 0) FROM page_translations), 1));
ALTER TABLE page_translations ALTER COLUMN id SET DEFAULT nextval('page_translations_id_seq');
ALTER TABLE page_translations ADD PRIMARY KEY (id);
ALTER TABLE page_translations ALTER COLUMN page_id SET NOT NULL;
ALTER TABLE page_translations ALTER COLUMN language SET NOT NULL;
ALTER TABLE page_translations ALTER COLUMN title SET NOT NULL;
UPDATE page_translations SET content = '' WHERE content IS NULL;
ALTER TABLE page_translations ALTER COLUMN content SET NOT NULL;
ALTER TABLE page_translations
    ADD CONSTRAINT page_translations_page_id_foreign
    FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE;

ALTER TABLE migrations ALTER COLUMN id SET NOT NULL;
CREATE SEQUENCE IF NOT EXISTS migrations_id_seq AS bigint OWNED BY migrations.id;
SELECT setval('migrations_id_seq', GREATEST((SELECT COALESCE(MAX(id), 0) FROM migrations), 1));
ALTER TABLE migrations ALTER COLUMN id SET DEFAULT nextval('migrations_id_seq');
ALTER TABLE migrations ADD PRIMARY KEY (id);
ALTER TABLE migrations ALTER COLUMN migration SET NOT NULL;
ALTER TABLE migrations ALTER COLUMN batch SET NOT NULL;

DROP TABLE IF EXISTS sessions;
CREATE TABLE sessions (
    id varchar(255) PRIMARY KEY,
    user_id bigint NULL,
    ip_address varchar(45) NULL,
    user_agent text NULL,
    payload text NOT NULL,
    last_activity integer NOT NULL
);
CREATE INDEX sessions_user_id_index ON sessions (user_id);
CREATE INDEX sessions_last_activity_index ON sessions (last_activity);

DROP TABLE IF EXISTS cache;
CREATE TABLE cache (
    key varchar(255) PRIMARY KEY,
    value text NOT NULL,
    expiration integer NOT NULL
);

DROP TABLE IF EXISTS cache_locks;
CREATE TABLE cache_locks (
    key varchar(255) PRIMARY KEY,
    owner varchar(255) NOT NULL,
    expiration integer NOT NULL
);

DROP TABLE IF EXISTS jobs;
CREATE TABLE jobs (
    id bigserial PRIMARY KEY,
    queue varchar(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer NULL,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);
CREATE INDEX jobs_queue_index ON jobs (queue);

DROP TABLE IF EXISTS job_batches;
CREATE TABLE job_batches (
    id varchar(255) PRIMARY KEY,
    name varchar(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text NULL,
    cancelled_at integer NULL,
    created_at integer NOT NULL,
    finished_at integer NULL
);

DROP TABLE IF EXISTS failed_jobs;
CREATE TABLE failed_jobs (
    id bigserial PRIMARY KEY,
    uuid varchar(255) UNIQUE NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP
);

DROP TABLE IF EXISTS password_reset_tokens;
CREATE TABLE password_reset_tokens (
    email varchar(255) PRIMARY KEY,
    token varchar(255) NOT NULL,
    created_at timestamp without time zone NULL
);

COMMIT;
SQL;
    }
}
