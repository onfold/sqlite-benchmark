# SQLite vs MySQL benchmark

This repository contains a **simplistic** benchmark of SQLite vs MySQL performance in a Laravel application. The goal is to show that in most cases, SQLite is a production-ready alternative to client-server databases.

It was used to write the following article: [Is it time to ditch MySQL and PostgreSQL for SQLite?](https://onfold.sh/blog/is-it-time-to-ditch-mysql-and-postgresql-for-sqlite)

The SQLite database is slightly modified for efficiency, with the [WAL](https://www.sqlite.org/wal.html) journal mode and [synchronous](https://www.sqlite.org/pragma.html#pragma_synchronous) set to `NORMAL`.

The following tests are performed:

- Single write: insert 5 000 rows one by one
- Bulk write: insert 200 times 5 000 rows at once
- Single read: read 5 000 rows one by one
- Bulk read: read 5 000 times 5 000 rows at once

The benchmark is performed on a 5-column table with the following structure:

- Auto increment ID
- Random text column with index
- Random text column
- Random integer column
- Random float column

The benchmark code can be seen in the `app/Console/Commands/BenchmarkCommand.php` file.

To run the benchmark, clone the repository and run `composer install`. Then, modify the `.env` file to match your MySQL database configuration.

Finally, run `php -d memory_limit=1G artisan benchmark` to run the tests.
