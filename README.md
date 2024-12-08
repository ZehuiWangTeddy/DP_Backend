```markdown
# **DP_Backend**

## **Environment Setup**
1. Install the following:
   - [PHP](https://www.php.net/downloads)
   - [Laravel](https://laravel.com/docs/11.x)
   - [PostgreSQL 16](https://www.postgresql.org/download/)

2. Verify installations:
   ```bash
   php -v
   composer -v
   ```

## **Setting Up the `.env` File**
1. Create a `.env` file in the `backend` directory.
2. Add the following content:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=netfilex
   DB_USERNAME=laravel
   DB_PASSWORD=laravel
   
   SESSION_DRIVER=file
   SESSION_LIFETIME=120
   SESSION_ENCRYPT=false
   SESSION_PATH=/
   SESSION_DOMAIN=null
   ```

## **Installing Dependencies**
1. Run the following commands in the `backend` directory:
   - Install PHP dependencies:
     ```bash
     composer install
     ```
   - Install Node.js dependencies:
     ```bash
     npm install
     ```

## **Database Setup**
1. Execute the `create_database.sql` file in PostgreSQL using a database tool such as pgAdmin or the PostgreSQL CLI.
2. Verify the database schema matches the requirements.

## **Run Database Migrations**
1. Run the migrations to create tables:
   ```bash
   php artisan migrate
   ```

## **Starting the Development Server**
1. Start the Laravel development server:
   ```bash
   php artisan serve
   ```
2. Access the application at `http://localhost:8000`.

## **Testing**
1. Populate the database with seed data:
   ```bash
   php artisan db:seed
   ```
2. Clear cached configurations if needed:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```
## **Add API secret**
1. Add API secret in .env file
    ```bash
        php artisan jwt:secret
    ```

## **Notes**
- Ensure PostgreSQL is running locally or on a configured server.
- Use proper credentials in the `.env` file for the database connection.
```