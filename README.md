## Quick start
Assuming you are on a blank machine, make sure these dependencies are installed:
 - docker
 - docker-compose
 
 (and these, if you choose to run the script locally and not in container)
 - php7.4-cli
 - php7.4-mysql

and run:
```
sudo docker-compose up -d
```
to start the mariadb databaseserver, Adminer for database management and build the docker image with the script.

mariadb's port 3306 (sql) and Adminer's port 80 (http) is exposed to the host. mariadb's port is exposed to be able to run the script locally.

Connect using these credentials:
```
user: root
password: cegopassword
database: cegodatabase
```

The script can be run directly from the host machine, or through docker.

## Script usage
From the script directory, run:
```
./cegoassignment.php [OPTIONS]...

      --help                  Print this help message
      --query="<SQL query>"   Accepts query input like "SELECT * FROM table;"
      --output="FILE"         Location of local CSV file to be written
      --includeheader         Include csv header
      --delete                Delete retrieved rows in database, used with --output
      --verify                Used with --output and --delete to verify filecontent before deletion
```
Example for running it locally, from the script directory:
```
./cegoassignment.php --query="SELECT * FROM users ORDER BY firstname LIMIT 5" --output="output.csv" --includeheader --delete --verify
```

Example for running it through docker:
```
docker run --network cegonetwork -it --rm -v $(pwd)/script:/script/output cegoscript --query="SELECT * FROM users ORDER BY firstname LIMIT 5" --output=/script/output/output.csv --includeheader --delete --verify
```

Both will output 5 rows as CSV to output.csv and remove the rows from the database after checking that the correct data is saved to the file.
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
