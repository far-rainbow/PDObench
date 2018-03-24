<?php

require 'config.php';

printf("\nTest DB with truncate table and fill it with %d rows of random values\n\n", COUNT);

$tests = prepareTests($servers);

printf("\n================================================================================\n\n");

foreach ($tests as $idx => $test) :
    
    printf("%s\ttest\t", $servers[$idx]['host']);
    
    if ($test) {
        $resultMSec = test($test);
        printf("%s\t\t= %.2fs\n", chr(8), $resultMSec);
    } else {
        printf("Skip...\n");
    }
endforeach
;

function prepareTests($servers)
{
    foreach ($servers as $host) :
        try {
            printf("%s\tinit\t", $host['host']);
            $tests[] = new PDO('mysql:host=' . $host['host'] . ';dbname=' . $host['dbname'], $host['user'], $host['password']);
            printf("\tOk!\n");
        } catch (PDOException $e) {
            $tests[] = null;
            printf("\tFailed! -- " . $e->getMessage() . PHP_EOL);
        }
    endforeach
    ;
    
    return $tests;
}

/**
 * Test procedure
 * 
 * @param PDO $test
 * @return float overall test time in milliseconds
 */
function test($test)
{
    if ($test) {
        
        $anim = array(
            "|",
            "/",
            "-",
            "\\"
        );
        $count = COUNT;
        $timer = microtime(true);
        
        $test->exec("truncate table a");
        
        $rnd = rand(0, 999999);
        $test->exec("CREATE table b_$rnd like a");
        
        $rnd = null;
        if (TRANS)
            $test->exec("start transaction");
        
        for ($i = 0; $i < $count; $i ++) {
            $rnd = rand(0, 999999);
            $r = $test->query("INSERT INTO a (rnd) values ($rnd)");
            if ($i % 100 < 1) {
                printf(chr(8));
                printf("..");
            } else {
                printf(chr(8));
                printf($anim[$i % 4]);
            }
        }
        
        if (TRANS)
            $test->exec("commit");
        
        //$r = $test->query("drop table b");
        
        return microtime(true) - $timer;
    } else {
        return null;
    }
}