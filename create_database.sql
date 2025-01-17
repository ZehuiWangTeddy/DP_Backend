-- Step 1
-- Role: laravel
-- DROP ROLE IF EXISTS laravel; 

CREATE ROLE laravel WITH
  LOGIN                -- Allows this role to log in to the database.
  NOSUPERUSER          -- This role does not have superuser privileges.
  INHERIT              -- This role inherits the permissions of roles it is granted.
  NOCREATEDB           -- This role does not have the ability to create databases.
  NOCREATEROLE         -- This role cannot create other roles.
  NOREPLICATION        -- This role does not have replication privileges.
  NOBYPASSRLS          -- This role cannot bypass Row-Level Security (RLS).
  ENCRYPTED PASSWORD 'SCRAM-SHA-256$4096:ETL4Li5VV34Zqmegnvphmg==$iKnAIOq/ZyvG0Id9xl59+W5sY0OUHKzzzEoSVxX34VU=:gYYXysykYa/B6EYxUlSO7VbnwWCjjVJHcT9HG1ndkDc='; -- Set the encrypted password for this role.

-- Step 2
-- Database: netflix
-- DROP DATABASE IF EXISTS netflix; 

CREATE DATABASE netflix
    WITH
    OWNER = laravel           -- Sets 'laravel' as the owner of the database.
    ENCODING = 'UTF8'         -- Sets the database encoding to UTF-8.
    LC_COLLATE = 'C'          -- Sets the locale for sorting order (collation).
    LC_CTYPE = 'C'            -- Sets the locale for character classification (ctype).
    LOCALE_PROVIDER = 'libc'  -- Specifies the provider for locale settings.
    TABLESPACE = pg_default   -- Uses the default tablespace for the database.
    CONNECTION LIMIT = -1     -- No connection limit for the database.
    IS_TEMPLATE = False;      -- This database is not a template database (cannot be used as a template for creating other databases).

-- Step 3
GRANT TEMPORARY, CONNECT ON DATABASE netflix TO PUBLIC; 

GRANT ALL ON DATABASE netflix TO laravel; 
