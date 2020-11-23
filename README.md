## Quick start
Assuming you are on a blank machine, make sure these dependencies are installed:
 - php7.4-cli
 - php7.4-mysql
 - docker
 - docker-compose

and run:
```
sudo docker-compose up -d
```
to start the mariadb databaseserver and Adminer for database management.

Adminer's port 80 (http) is exposed to the host.

## Script usage
From the script directory, run:
```
./cegoassignment.php [OPTIONS]...

      --help                  Print this help message
      --query="<SQL query>"   Accepts query input like "SELECT * FROM database;"
      --output="FILE"         Location of local CSV file to be written
      --includeheader         Include csv header
      --delete                Delete retrieved rows in database, used with --output
      --verify                Used with --output and --delete to verify filecontent before deletion
```
Example:
```
./cegoassignment.php --query="SELECT * FROM users ORDER BY firstname LIMIT 5" --output="output.csv" --includeheader --delete --verify
```
This will output 5 rows as CSV to out.csv and remove the rows from the database after checking that the correct data is saved to the file.
Make sure that you have write permissions to the output folder/file.

---
---

# Job interview assignment
We kindly ask you to solve the task below. By solving and submitting this assignment you provide us with insights in how you solve real-world problems. What we will be looking at are topics such as: choice of technology, structuring of code, use of VCS, selection of 3rd party libraries, documentation etc.

## The task
Develop a solution that, given a select query, can read data from a database, write it to a local file and then delete the data from the database. The solution should verify that data is written to the file, and that data integrity is maintained, before deleting it from the database.

- Use Bash, PHP, JavaScript or Go as the language
- Use MySQL, MariaDB, CockroachDB or SQLite as the database

Please use the data set provided in the SQL dump in this repo. Please also consider that your solution should be able to handle much larger data sets.

## Expectations
Make a copy of this repo. Solve the task below. Push your code to a public repo, and send us the link as a reply to our email.

Your solution should include a short readme describing your solution, how to use/test it and any final considerations such as known errors, next steps, security concerns etc. Don’t worry we are not expecting this thing to be perfect.
