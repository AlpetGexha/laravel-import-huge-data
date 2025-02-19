# Import CSV File to Database

This is a simple project to import a CSV file to a database using Laravel.

## Installation

```bash
composer install
php artisan storage:link
php artisan migrate
```

Generate the csv file with the following command: 
```
php artisan csv:generate
```

Have FUN! 

### 1 Approach: One By One

The most simples approach is to import one row at a time. This is the slowest approach, but it's the easiest to
implement.
It can run out of memory if you have a huge CSV file.

### 2 Approach: Collect & Insert

This approach is a bit faster than the previous one. It collects a certain amount of rows and inserts them at once.
This use only 1 query to insert all the rows.
It can run out of memory if you have a huge CSV file.
If the insert its to big they will be a problem with the max_allowed_packet

### 3 Approach: Chunk

We use the same approach as the previous one, but we use the `chunk` method from Laravel to split the CSV file into
smaller parts.
for every 1000 we make one query to insert the rows.
But if the file its to big we will have a problem with Memory while loading the file

### 4 Approach: Lazy Collection

Laravel provides a `LazyCollection` class leverages PHP's generators to allow you to work with very large datasets while
keeping memory usage low.
Its a bit more complex compared to the previous ones, its a much slower approach but it will not have memory problems (
Till 1M rows)

### 5 Approach: Lazy Collection With Chunking

We use the same approach as the previous one, but we use the `chunk` method from Laravel to split the CSV file into
Again its not Faster and Again we will have problem with the Memory while loading the file on largerst file

### 6 Approach: Lazy Collection With Chunking & PDO

Using PDO to insert the rows is faster than using Eloquent (which reduces ORM overhead) . We use the same approach as
the previous one, but with PDO statements.
Till now it work with 1M rows and we use so much less memory (0.23 MB on every file)
The only problem for that its it take 20-25seconds to import 1M rows

### 7 Approach: Manual Streaming With Chunking

Trying to use the simplest Streaming approach with chunking, we use the `fgetcsv` function to read the CSV file line by
line and insert
The result its not good, it takes to much time and the memory using it fail on 1M rows

### 8 Approach: Manual Streaming with Chunking & PDO

Using the same approach as the previous one, but with PDO statements.
The result its on every file we use 0MB of memory and the time its 28-31 seconds for 1M rows
It work on large file but the time its not the best

### 9 Approach: PDO Prepared Statements

Using the PDO directly with prepared statements, we are using the raw SQL to insert the rows.
We dont have the memory problem but the time its not the best, it takes 5 minutes for 1M rows

### 10 Approach:  PDO Prepared Statements with Chunking

Using the same approach as the previous one, but with PDO statements and chunking.
The result its with 0MB of memory and the chunking make the file much faster

### 11 Approach: Concurrently

Sometimes you may need to execute several slow tasks which do not depend on one another. In many cases, significant
performance improvements can be realized by executing the tasks concurrently. Laravel's `Concurrency` facade provides a
simple, convenient API for executing closures concurrently.
In our case, we use 10 concurrent processes to insert the rows. witch make the result much faster
The timing much depends on the server and the number of concurrent processes they can handle and how well they can
handle

### 12 Approach: Load Data Local Infile

This is one of the fastest methods for bulk data insertion in MySQL
It is much faster than inserting records one by one (like with INSERT statements) because it minimizes communication
between the application and the database, handling bulk loading on the database server directly.

#### What is Local Data Infile?

The SQL command LOAD DATA LOCAL INFILE is a MySQL-specific command used to load data directly from a file into a table.

The LOAD DATA LOCAL INFILE command is designed to load data from a file located on the client's server rather than the
database server. Here are the key aspects: File Location: Unlike LOAD DATA INFILE, the file is on the client's machine.

Advantages of LOAD DATA LOCAL INFILE:
**Performance**: This is the fastest method for bulk data import in MySQL. It bypasses the need for individual insert
**statements**, which can be very slow for large datasets.
**Low Memory Usage**: Unlike processing each row in PHP, this method is executed entirely within the database, making it
more memory efficient.
**Minimal Disk I/O**: Data is read in bulk, reducing the number of database connections and the overhead associated with
individual insert statements.

**Security**: The LOCAL keyword can introduce potential security risks because it allows loading data from the local
filesystem. Ensure that the files are trusted and the MySQL user has proper permissions.

This method is highly optimized for importing large datasets into MySQL and is ideal for bulk operations where
performance and speed are essential. It works best when the data is clean, and the file format is well-structured (CSV
format in this case).

### Report & Benchmark

This Relly depends on the machine or the server you are using, but for me, for my llaptop the this is the results

## Function Performance Report

## Function Performance Report

| Index | Function Name                      | Data Size | Time Taken               | Memory Usage | SQL       |
|-------|------------------------------------|-----------|--------------------------|--------------|-----------|
| 1     | `basicOneByOne`                    | 100       | 130ms                    | 0.35MB       | 100       |
| 1     | `basicOneByOne`                    | 1K        | 549ms                    | 2MB          | 1,000     |
| 1     | `basicOneByOne`                    | 10K       | 5.7s                     | 19MB         | 10,000    |
| 1     | `basicOneByOne`                    | 100K      | Memory Issue             | Memory Issue | 100,000   |
| 1     | `basicOneByOne`                    | 1M        | Memory Issue             | Memory Issue | 1,000,000 |
| 2     | `collectAndInsert`                 | 100       | 16ms                     | 0.05MB       |           |
| 2     | `collectAndInsert`                 | 1K        | 62ms                     | 0.57MB       |           |
| 2     | `collectAndInsert`                 | 10K       | Prepared Statement Issue | Memory Issue |           |
| 2     | `collectAndInsert`                 | 100K      | Memory Issue             | Memory Issue |           |
| 2     | `collectAndInsert`                 | 1M        | Memory Issue             | Memory Issue |           |
| 3     | `collectAndChunk`                  | 100       | 15ms                     | 0.05MB       |           |
| 3     | `collectAndChunk`                  | 1K        | 65ms                     | 0.57MB       |           |
| 3     | `collectAndChunk`                  | 10K       | 246ms                    | 5.7MB        |           |
| 3     | `collectAndChunk`                  | 100K      | 2.6s                     | 56.97MB      |           |
| 3     | `collectAndChunk`                  | 1M        | Memory Issue             | Memory Issue |           |
| 4     | `lazyCollection`                   | 100       | 66ms                     | 0.39MB       | 100       |
| 4     | `lazyCollection`                   | 1K        | 37ms                     | 1.47MB       | 1,000     |
| 4     | `lazyCollection`                   | 10K       | 3s                       | 12MB         | 10,000    |
| 4     | `lazyCollection`                   | 100K      | 39s                      | 120MB        | 100,000   |
| 4     | `lazyCollection`                   | 1M        | Memory Issue             | Memory Issue |           |
| 5     | `lazyCollectionWithChunking`       | 100       | 16ms                     | 0.28MB       |           |
| 5     | `lazyCollectionWithChunking`       | 1K        | 61ms                     | 0.8MB        |           |
| 5     | `lazyCollectionWithChunking`       | 10K       | 275ms                    | 5.93MB       |           |
| 5     | `lazyCollectionWithChunking`       | 100K      | 1.7s                     | 57MB         |           |
| 5     | `lazyCollectionWithChunking`       | 1M        | Memory Issue             | Memory Issue |           |
| 6     | `lazyCollectionWithChunkingAndPdo` | 100       | 10ms                     | 0.23MB       |           |
| 6     | `lazyCollectionWithChunkingAndPdo` | 1K        | 51ms                     | 0.23MB       |           |
| 6     | `lazyCollectionWithChunkingAndPdo` | 10K       | 234ms                    | 0.23MB       |           |
| 6     | `lazyCollectionWithChunkingAndPdo` | 100K      | 2s                       | 0.23MB       |           |
| 6     | `lazyCollectionWithChunkingAndPdo` | 1M        | 20s                      | 0.23MB       |           |
| 7     | `manualStreaming`                  | 100       | 13ms                     | 0.05MB       |           |
| 7     | `manualStreaming`                  | 1K        | 39ms                     | 0.57MB       |           |
| 7     | `manualStreaming`                  | 10K       | 224ms                    | 5.69MB       |           |
| 7     | `manualStreaming`                  | 100K      | 1.8s                     | 56MB         |           |
| 7     | `manualStreaming`                  | 1M        | Memory Issue             | Memory Issue |           |
| 8     | `manualStreamingWithPdo`           | 100       | 7ms                      | 0MB          |           |
| 8     | `manualStreamingWithPdo`           | 1K        | 78ms                     | 0MB          |           |
| 8     | `manualStreamingWithPdo`           | 10K       | 328ms                    | 0MB          |           |
| 8     | `manualStreamingWithPdo`           | 100K      | 2.9s                     | 0MB          |           |
| 8     | `manualStreamingWithPdo`           | 1M        | 28s                      | 0MB 0MB      |           |
| 9     | `PDOPrepared`                      | 100       | 41ms                     | 0MB 0MB      | 100       |
| 9     | `PDOPrepared`                      | 1K        | 237ms                    | 0MB          | 1,000     |
| 9     | `PDOPrepared`                      | 10K       | 2.21s                    | 0MB          | 10,000    |
| 9     | `PDOPrepared`                      | 100K      | 25.27s                   | 0MB          | 100,000   |
| 9     | `PDOPrepared`                      | 1M        | 4m43s                    | 0MB          | 1,000,000 |
| 10    | `PDOPreparedChunked`               | 100       | 12ms                     | 0.15MB       |           |
| 10    | `PDOPreparedChunked`               | 1K        | 49ms                     | 0.74MB       |           |
| 10    | `PDOPreparedChunked`               | 10K       | 222ms                    | 0.74MB       |           |
| 10    | `PDOPreparedChunked`               | 100K      | 1.5s                     | 0.74MB       |           |
| 10    | `PDOPreparedChunked`               | 1M        | 15.3s                    | 0.74MB       |           |
| 11    | `concurrent`                       | 100       | 168ms                    |              |           |
| 11    | `concurrent`                       | 1K        | 172ms                    |              |           |
| 11    | `concurrent`                       | 10K       | 234ms                    |              |           |
| 11    | `concurrent`                       | 100K      | 595ms                    |              |           |
| 11    | `concurrent`                       | 1M        | 4.36s                    |              |           |
| 11    | `concurrent`                       | 2M        | 8.8s                     |              |           |
| 12    | `loadDataInfile`                   | 100       | 10ms                     | 0MB          | 1         |
| 12    | `loadDataInfile`                   | 1K        | 29ms                     | 0MB          | 1         |
| 12    | `loadDataInfile`                   | 10K       | 115ms                    | 0MB          | 1         |
| 12    | `loadDataInfile`                   | 100K      | 567ms                    | 0MB          | 1         |
| 12    | `loadDataInfile`                   | 1M        | 5s                       | 0MB          | 1         |
| 12    | `loadDataInfile`                   | 2M        | 11s                      | 0MB          | 1         |
