-- ==========================================================================
-- Schema repair for cerr database
--
-- The DB was rebuilt without primary keys, sequences, foreign keys, indexes,
-- or proper column types. This script restores them in place, preserving all
-- existing rows.
--
-- Run inside a transaction. Any failure rolls everything back.
-- ==========================================================================

BEGIN;

-- --------------------------------------------------------------------------
-- helper: backfill NULL ids using row_number ordered by created_at, id
-- --------------------------------------------------------------------------

-- users
UPDATE users SET id = sub.new_id
FROM (
    SELECT ctid, COALESCE(id, (SELECT COALESCE(MAX(id), 0) FROM users) + ROW_NUMBER() OVER (ORDER BY created_at NULLS LAST, ctid)) AS new_id
    FROM users WHERE id IS NULL
) sub
WHERE users.ctid = sub.ctid;

-- categories
UPDATE categories SET id = sub.new_id
FROM (
    SELECT ctid, (SELECT COALESCE(MAX(id), 0) FROM categories) + ROW_NUMBER() OVER (ORDER BY created_at NULLS LAST, ctid) AS new_id
    FROM categories WHERE id IS NULL
) sub
WHERE categories.ctid = sub.ctid;

-- category_translations
UPDATE category_translations SET id = sub.new_id
FROM (
    SELECT ctid, (SELECT COALESCE(MAX(id), 0) FROM category_translations) + ROW_NUMBER() OVER (ORDER BY created_at NULLS LAST, ctid) AS new_id
    FROM category_translations WHERE id IS NULL
) sub
WHERE category_translations.ctid = sub.ctid;

-- tags
UPDATE tags SET id = sub.new_id
FROM (
    SELECT ctid, (SELECT COALESCE(MAX(id), 0) FROM tags) + ROW_NUMBER() OVER (ORDER BY created_at NULLS LAST, ctid) AS new_id
    FROM tags WHERE id IS NULL
) sub
WHERE tags.ctid = sub.ctid;

-- news
UPDATE news SET id = sub.new_id
FROM (
    SELECT ctid, (SELECT COALESCE(MAX(id), 0) FROM news) + ROW_NUMBER() OVER (ORDER BY created_at NULLS LAST, ctid) AS new_id
    FROM news WHERE id IS NULL
) sub
WHERE news.ctid = sub.ctid;

-- news_translations
UPDATE news_translations SET id = sub.new_id
FROM (
    SELECT ctid, (SELECT COALESCE(MAX(id), 0) FROM news_translations) + ROW_NUMBER() OVER (ORDER BY created_at NULLS LAST, ctid) AS new_id
    FROM news_translations WHERE id IS NULL
) sub
WHERE news_translations.ctid = sub.ctid;

-- pages
UPDATE pages SET id = sub.new_id
FROM (
    SELECT ctid, (SELECT COALESCE(MAX(id), 0) FROM pages) + ROW_NUMBER() OVER (ORDER BY ctid) AS new_id
    FROM pages WHERE id IS NULL
) sub
WHERE pages.ctid = sub.ctid;

-- page_translations
UPDATE page_translations SET id = sub.new_id
FROM (
    SELECT ctid, (SELECT COALESCE(MAX(id), 0) FROM page_translations) + ROW_NUMBER() OVER (ORDER BY ctid) AS new_id
    FROM page_translations WHERE id IS NULL
) sub
WHERE page_translations.ctid = sub.ctid;

-- migrations
UPDATE migrations SET id = sub.new_id
FROM (
    SELECT ctid, (SELECT COALESCE(MAX(id), 0) FROM migrations) + ROW_NUMBER() OVER (ORDER BY batch, ctid) AS new_id
    FROM migrations WHERE id IS NULL
) sub
WHERE migrations.ctid = sub.ctid;

-- --------------------------------------------------------------------------
-- column type fixes (text -> timestamp / bigint)
-- --------------------------------------------------------------------------

-- news.scheduled_at, news.updated_at
ALTER TABLE news
    ALTER COLUMN scheduled_at TYPE timestamp without time zone
        USING NULLIF(scheduled_at, '')::timestamp without time zone;
ALTER TABLE news
    ALTER COLUMN updated_at TYPE timestamp without time zone
        USING NULLIF(updated_at, '')::timestamp without time zone;

-- pages timestamps
ALTER TABLE pages
    ALTER COLUMN created_at TYPE timestamp without time zone
        USING NULLIF(created_at, '')::timestamp without time zone;
ALTER TABLE pages
    ALTER COLUMN updated_at TYPE timestamp without time zone
        USING NULLIF(updated_at, '')::timestamp without time zone;

-- page_translations timestamps
ALTER TABLE page_translations
    ALTER COLUMN created_at TYPE timestamp without time zone
        USING NULLIF(created_at, '')::timestamp without time zone;
ALTER TABLE page_translations
    ALTER COLUMN updated_at TYPE timestamp without time zone
        USING NULLIF(updated_at, '')::timestamp without time zone;

-- sessions: user_id is text but should be bigint (nullable)
ALTER TABLE sessions
    ALTER COLUMN user_id TYPE bigint
        USING NULLIF(user_id, '')::bigint;
-- sessions.last_activity is bigint per migration but Laravel uses integer; bigint is fine.

-- --------------------------------------------------------------------------
-- USERS
-- --------------------------------------------------------------------------
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

-- --------------------------------------------------------------------------
-- CATEGORIES
-- --------------------------------------------------------------------------
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

-- --------------------------------------------------------------------------
-- CATEGORY_TRANSLATIONS
-- --------------------------------------------------------------------------
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

-- --------------------------------------------------------------------------
-- TAGS
-- --------------------------------------------------------------------------
ALTER TABLE tags ALTER COLUMN id SET NOT NULL;
CREATE SEQUENCE IF NOT EXISTS tags_id_seq AS bigint OWNED BY tags.id;
SELECT setval('tags_id_seq', GREATEST((SELECT COALESCE(MAX(id), 0) FROM tags), 1));
ALTER TABLE tags ALTER COLUMN id SET DEFAULT nextval('tags_id_seq');
ALTER TABLE tags ADD PRIMARY KEY (id);
ALTER TABLE tags ALTER COLUMN name SET NOT NULL;
ALTER TABLE tags ADD CONSTRAINT tags_name_unique UNIQUE (name);

-- --------------------------------------------------------------------------
-- NEWS
-- --------------------------------------------------------------------------
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

-- --------------------------------------------------------------------------
-- NEWS_TRANSLATIONS
-- --------------------------------------------------------------------------
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

-- --------------------------------------------------------------------------
-- NEWS_TAGS  (empty table, recreate cleanly)
-- --------------------------------------------------------------------------
DROP TABLE IF EXISTS news_tags;
CREATE TABLE news_tags (
    news_id bigint NOT NULL REFERENCES news(id) ON DELETE CASCADE,
    tag_id  bigint NOT NULL REFERENCES tags(id) ON DELETE CASCADE,
    PRIMARY KEY (news_id, tag_id)
);

-- --------------------------------------------------------------------------
-- PAGES
-- --------------------------------------------------------------------------
ALTER TABLE pages ALTER COLUMN id SET NOT NULL;
CREATE SEQUENCE IF NOT EXISTS pages_id_seq AS bigint OWNED BY pages.id;
SELECT setval('pages_id_seq', GREATEST((SELECT COALESCE(MAX(id), 0) FROM pages), 1));
ALTER TABLE pages ALTER COLUMN id SET DEFAULT nextval('pages_id_seq');
ALTER TABLE pages ADD PRIMARY KEY (id);
ALTER TABLE pages ALTER COLUMN slug SET NOT NULL;
ALTER TABLE pages ADD CONSTRAINT pages_slug_unique UNIQUE (slug);

-- --------------------------------------------------------------------------
-- PAGE_TRANSLATIONS
-- --------------------------------------------------------------------------
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

-- --------------------------------------------------------------------------
-- MIGRATIONS
-- --------------------------------------------------------------------------
ALTER TABLE migrations ALTER COLUMN id SET NOT NULL;
CREATE SEQUENCE IF NOT EXISTS migrations_id_seq AS bigint OWNED BY migrations.id;
SELECT setval('migrations_id_seq', GREATEST((SELECT COALESCE(MAX(id), 0) FROM migrations), 1));
ALTER TABLE migrations ALTER COLUMN id SET DEFAULT nextval('migrations_id_seq');
ALTER TABLE migrations ADD PRIMARY KEY (id);
ALTER TABLE migrations ALTER COLUMN migration SET NOT NULL;
ALTER TABLE migrations ALTER COLUMN batch SET NOT NULL;

-- --------------------------------------------------------------------------
-- SESSIONS  (recreate cleanly; users will be logged out)
-- --------------------------------------------------------------------------
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

-- --------------------------------------------------------------------------
-- INFRASTRUCTURE TABLES (cache, cache_locks, jobs, job_batches, failed_jobs,
-- password_reset_tokens) — all empty, recreate from scratch
-- --------------------------------------------------------------------------
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
