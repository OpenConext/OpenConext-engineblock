# Database Backend Support

EngineBlock can be configured to use either **MariaDB** or **PostgreSQL** as its database backend.

The database backend is selected through the database configuration, allowing the same EngineBlock setup to run against either supported database without changing the Doctrine connection configuration.

## Supported Databases

Other versions will most likely work, but these are the versions that have been tested.

| Database   | Driver      | Version            |
| ---------- | ----------- | ------------------ |
| MariaDB    | `pdo_mysql` | `10.11.13-MariaDB` |
| PostgreSQL | `pdo_pgsql` | `17`               |

## Selecting a Database

The database connection is configured using the following parameters:

```yaml
parameters:
    database.driver: pdo_pgsql
    database.server_version: '17'
    database.host: postgres
    database.port: '5432'
    database.user: ebrw
    database.password: secret
    database.dbname: eb
```

For MariaDB, use:

```yaml
parameters:
    database.driver: pdo_mysql
    database.server_version: 10.11.13-MariaDB
    database.host: mariadb
    database.port: '3306'
    database.user: ebrw
    database.password: secret
    database.dbname: eb
```

The Doctrine configuration uses these parameters for the connection:

```yaml
doctrine:
    dbal:
        default_connection: engineblock
        connections:
            engineblock:
                driver: "%database.driver%"
                dbname: "%database.dbname%"
                host: "%database.host%"
                port: "%database.port%"
                user: "%database.user%"
                password: "%database.password%"
                server_version: "%database.server_version%"
```

This means that changing the database backend only requires changing the database parameters; the Doctrine connection itself remains the same.


## PostgreSQL Support

PostgreSQL support requires the PHP PostgreSQL PDO extension to be installed in the container.

Install the required PostgreSQL development libraries and PHP extension:

```bash
apt-get update
apt-get install -y libpq-dev
docker-php-ext-install pdo_pgsql
service apache2 reload
```

This adds the `pdo_pgsql` driver required by Doctrine to connect to PostgreSQL.

The PostgreSQL-specific container setup is therefore an additional requirement when running EngineBlock with PostgreSQL.

## Database Migrations

Database migrations are separated into:
```text
migrations/
├── MariaMigrations/
└── PostgresMigrations/
```

To run migrations for mariadb use;
```shell
./bin/console doctrine:migrations:migrate
```

To run migrations for postgresql use;
```shell
APP_ENV=postgres ./bin/console doctrine:migrations:migrate
```
To run migrations using postgres in other env you will have to copy [config/packages/postgres/doctrine_migrations.yaml](config/packages/postgres/doctrine_migrations.yaml)
to the required env folder.


For MariaDB, it uses the MariaMigrations folder: [doctrine_migrations.yaml](config/packages/doctrine_migrations.yaml)
```yaml
doctrine_migrations:
    migrations_paths:
        OpenConext\EngineBlock\Doctrine\Migrations:
            '%kernel.project_dir%/migrations/MariaMigrations'
    storage:
        table_storage:
            table_name: 'migration_versions'

```
For PostgresSQL, it uses the PostgresMigrations folder: [doctrine_migrations.yaml](config/packages/postgres/doctrine_migrations.yaml)
```yaml
doctrine_migrations:
    migrations_paths:
        OpenConext\EngineBlock\Doctrine\Migrations:
            '%kernel.project_dir%/migrations/PostgresMigrations'
        OpenConext\EngineBlock\Doctrine\SharedMigrations:
            '%kernel.project_dir%/migrations/SharedMigrations'
```
## How It Works

The database selection follows this flow:

```text
Database configuration
        │
        ▼
database.driver
        │
        ├── pdo_mysql ──► MariaDB
        │
        └── pdo_pgsql ──► PostgreSQL
        │
        ▼
     Doctrine DBAL
        │
        ▼
    EngineBlock
```

The application therefore does not need separate Doctrine connection configurations for each database. The driver, server version and connection details are provided through parameters and consumed by the existing Doctrine configuration.

## Behat tests

To run the behat tests with PostgreSQL, you will need to adjust some configuration.

In [behat.sh](ci/qa/behat.sh) you will need to adjust the migrations running:
```shell
echo -e "\nInstalling database fixtures...\n"
./bin/console doctrine:schema:drop --force --env=ci
./bin/console doctrine:query:sql "DROP TABLE IF EXISTS sso_provider_roles_eb5"
./bin/console doctrine:query:sql "DROP TABLE IF EXISTS migration_versions"
./bin/console doctrine:migrations:migrate --env=ci --no-interaction
```
In [ci](config/packages/ci) ensure the correct database connection is used:
```yaml
doctrine_migrations:
    migrations_paths:
        OpenConext\EngineBlock\Doctrine\Migrations:
            '%kernel.project_dir%/migrations/PostgresMigrations'

parameters:
    database.driver: pdo_pgsql
    database.server_version: '17'
    database.host: postgres
    database.port: '5432'
    database.user: ebrw
    database.password: secret
    database.dbname: eb
    database.test.driver: pdo_pgsql
    database.test.server_version: '17'
    database.test.host: postgres
    database.test.port: '5432'
    database.test.user: eb_testrw
    database.test.password: secret
    database.test.dbname: eb_test
```
