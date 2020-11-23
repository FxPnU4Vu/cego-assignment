#!/usr/bin/env php
<?php
if (PHP_SAPI != "cli") {die("CLI only");}

$sql_address="127.0.0.1";
$sql_username="root";
$sql_password="cegopassword";
$sql_database="cegodatabase";
$sql_port="3306";
date_default_timezone_set('Europe/Copenhagen');
error_reporting(0);

if (!$link = mysqli_connect($sql_address,$sql_username,$sql_password,$sql_database,$sql_port)) {
    fwrite(STDERR,"Error connecting to MySQL.\n#".mysqli_connect_errno()." - ".mysqli_connect_error() . PHP_EOL);
    exit(1);
}
mysqli_set_charset($link,'utf8');


$allowedArgs = array(
                        "--help",
                        "--query",
                        "--output",
                        "--includeheader",
                        "--delete",
                        "--verify"
                    );

//Catch --help
if (in_array("--help",$argv)) { 
    fwrite(STDOUT,"Usage: ./cegoassignment.php [OPTIONS]...".PHP_EOL);
    fwrite(STDOUT,PHP_EOL);
    fwrite(STDOUT,"      --help                  Print this help message".PHP_EOL);
    fwrite(STDOUT,"      --query=\"<SQL query>\"   Accepts query input like \"SELECT * FROM table;\"".PHP_EOL);
    fwrite(STDOUT,"      --output=\"FILE\"         Location of local CSV file to be written".PHP_EOL);
    fwrite(STDOUT,"      --includeheader         Include csv header".PHP_EOL);
    fwrite(STDOUT,"      --delete                Delete retrieved rows in database, used with --output".PHP_EOL);
    fwrite(STDOUT,"      --verify                Used with --output and --delete to verify filecontent before deletion".PHP_EOL);
    exit(0);
}

//Build argument array
$argArr=array();
foreach (array_slice($argv,1) AS &$arg) {
    $argExplode=explode("=",$arg);
    if (!in_array($argExplode[0],$allowedArgs)) { 
        fwrite(STDERR,"Unknown argument ".$argExplode[0] . ", see --help".PHP_EOL);
        exit(1);
    }
    if (array_key_exists($argExplode[0],$argArr)) { 
        fwrite(STDERR,"Argument ".$argExplode[0]." already set".PHP_EOL);
        exit(1);
    }
    $argArr[$argExplode[0]] = (isset($argExplode[1]) ? $argExplode[1]:'');
    unset($argExplode);
}


if(empty($argArr) || (empty($argArr["--query"])) || (array_key_exists("--output",$argArr) && !$argArr['--output']<>"") || (array_key_exists("--verify",$argArr) && (!array_key_exists("--delete",$argArr) || !array_key_exists("--output",$argArr)))) {
        fwrite(STDERR,"Missing arguments. See --help" . PHP_EOL);
    exit(1);
}

//Lets go down the rabbit hole
if ($returnQuery = returnQuery($argArr["--query"])) {
    if (array_key_exists("--output",$argArr)) {
        if (writeLocalFile(arrayToCSV($returnQuery,array_key_exists("--includeheader",$argArr)),$argArr["--output"])) {
            fwrite(STDOUT,"Saved local file".PHP_EOL);
            if(array_key_exists("--delete",$argArr) && array_key_exists("--verify",$argArr)) {
                if(verifyLocalFile(arrayToCSV($returnQuery,array_key_exists("--includeheader",$argArr)),$argArr["--output"])) {
                    fwrite(STDOUT,"Integrity of local file verified before deletion".PHP_EOL);
                    if(deleteQuery($returnQuery,$argArr["--query"])) {
                        fwrite(STDOUT,"Deleted returned rows from database".PHP_EOL);
                        exit(0);
                    }else{
                        fwrite(STDERR,"Failed to delete returned rows from database" . PHP_EOL);
                        exit(1);
                    }
                }else{
                    fwrite(STDERR,"Local file content does not match dataset" . PHP_EOL);
                    exit(1);
                }
            }elseif(array_key_exists("--delete",$argArr)){
                if(deleteQuery($returnQuery,$argArr["--query"])) {
                    fwrite(STDOUT,"Deleted returned rows from database".PHP_EOL);
                    exit(0);
                }else{
                    fwrite(STDERR,"Failed to delete returned rows from database" . PHP_EOL);
                    exit(1);
                }
            }
        }else{
            fwrite(STDERR,"Error saving file" . PHP_EOL);
            exit(1);
        }
    }else{
        fwrite(STDOUT,arrayToCSV($returnQuery,array_key_exists("--includeheader",$argArr)). PHP_EOL);
        if(array_key_exists("--delete",$argArr)){
            if(deleteQuery($returnQuery,$argArr["--query"])) {
                fwrite(STDOUT,"Deleted returned rows from database".PHP_EOL);
                exit(0);
            }else{
                fwrite(STDERR,"Failed to delete returned rows from database" . PHP_EOL);
                exit(1);
            }
        }
    }
}else{
    fwrite(STDOUT,"Query returned no results" . PHP_EOL);
    exit(0);   
}


function returnQuery($sqlQuery) {
    global $link;
    $sqlReturn = array();
    $queryResult = mysqli_query($link, $sqlQuery);
    while ($sqlRow = mysqli_fetch_array($queryResult,MYSQLI_ASSOC)) {
        $sqlReturn[] = $sqlRow;
    }
    if (!empty($sqlReturn)) {
        return $sqlReturn;
    }
    return false;
}

function arrayToCSV(array $sqlArray, bool $header) {
    $csv = fopen('php://memory', 'r+');
    if ($header) {
        fputcsv($csv, array_keys($sqlArray[0]));
    }
    foreach($sqlArray AS &$csvRow) {
        if (fputcsv($csv, $csvRow) === false) { 
            return false;
        }
    }
    rewind($csv);
    $csvBuffer = stream_get_contents($csv);
    return rtrim($csvBuffer);
}

function writeLocalFile(string $csvBuffer, string $fileLocation) {
    if (!is_writable($fileLocation)) {
        fwrite(STDERR,"File location is not writeable.".PHP_EOL);
    }elseif(file_put_contents($fileLocation, $csvBuffer, LOCK_EX)) {
        return true;
    }
    return false;
}

function verifyLocalFile(string $csvBuffer, string $fileLocation) {
    if (!is_readable($fileLocation)) {
        fwrite(STDERR,"File location is not readable.".PHP_EOL);
    }elseif ($fileContent = file_get_contents($fileLocation)) {
        if ($fileContent === $csvBuffer) {
            return true;
        }
    }
    return false;
}

function deleteQuery(array $sqlArray, string $sqlQuery) {
    global $link;

    if(!preg_match('/^.*FROM\s(.+?)\s/m', $sqlQuery, $match)) {
        fwrite(STDERR,"Problem occurred while preparing to delete result.".PHP_EOL);
        exit(1);  
    }

    $whereString = "";
    foreach ($sqlArray AS &$val) {
        $whereString .= "(";
        foreach ($val AS $column=>&$data) { 
            $whereString .= "$column = '".$data."'";
            $whereString .= next($val) ? " AND " : NULL;
        }
        $whereString .= next($sqlArray) ? ") OR " : ")";
    }
    $deleteQuery = "DELETE FROM ".$match[1]." WHERE ".$whereString.";";
	if (mysqli_query($link, $deleteQuery)) {
        return true;
    }
    return false;
}
