-- Step 1
-- Role: laravel
-- DROP ROLE IF EXISTS laravel; 

CREATE ROLE laravel WITH
  LOGIN                
  NOSUPERUSER          
  INHERIT              
  CREATEDB             
  NOCREATEROLE        
  NOREPLICATION        
  NOBYPASSRLS          
  ENCRYPTED PASSWORD 'SCRAM-SHA-256$4096:ETL4Li5VV34Zqmegnvphmg==$iKnAIOq/ZyvG0Id9xl59+W5sY0OUHKzzzEoSVxX34VU=:gYYXysykYa/B6EYxUlSO7VbnwWCjjVJHcT9HG1ndkDc='; -- Set the encrypted password for this role.

-- Step 2
-- Role: admin_role
-- DROP ROLE IF EXISTS admin_role; 

CREATE ROLE admin_role WITH
  NOLOGIN              -- This role cannot log in directly to the database.
  NOSUPERUSER          -- This role does not have superuser privileges.
  INHERIT              -- This role inherits the permissions of roles it is granted.
  CREATEDB             -- This role can create databases.
  NOCREATEROLE         -- This role cannot create other roles.
  NOREPLICATION        -- This role does not have replication privileges.
  NOBYPASSRLS;         -- This role cannot bypass Row-Level Security (RLS).

-- Step 3
GRANT admin_role TO laravel;  -- Grants the 'admin_role' to 'laravel', allowing 'laravel' to inherit all privileges of 'admin_role'.

-- Step 4
-- Database: netfilex
-- DROP DATABASE IF EXISTS netfilex; 

CREATE DATABASE netfilex
    WITH
    OWNER = laravel           
    ENCODING = 'UTF8'         
    LC_COLLATE = 'C'          
    LC_CTYPE = 'C'            
    LOCALE_PROVIDER = 'libc'  
    TABLESPACE = pg_default   
    CONNECTION LIMIT = -1     
    IS_TEMPLATE = False;      

-- Step 3
GRANT TEMPORARY, CONNECT ON DATABASE netfilex TO PUBLIC; -- Grants 'TEMPORARY' and 'CONNECT' privileges to all users (PUBLIC) for the 'netfilex' database.

GRANT ALL ON DATABASE netfilex TO laravel; -- Grants all privileges on the 'netfilex' database to the 'laravel' role.
