<?php

/*
 * Заполните config.php и запустите скрипт.
 *
 */
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
        if ($host['enabled']) {
            try {
                printf("%s\tinit\t", $host['host']);
                $tests[] = new PDO('mysql:host=' . $host['host'] . ';dbname=' . $host['dbname'], $host['user'], $host['password']);
                printf("\tOk!" . PHP_EOL);
            } catch (PDOException $e) {
                $tests[] = null;
                printf("\tFailed! -- " . $e->getMessage());
            }
        } else {
            printf("%s\tdisabled\t", $host['host']);
            $tests[] = null;
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
        
        $count = COUNT;
        $timer = microtime(true);
        $anim = array(
            "|",
            "/",
            "-",
            "\\"
        );
        
        tabTruncate('a', $test);
        tabCreate(rand(0, 999999), $test);
        
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
        
        // $r = $test->query("drop table b");
        
        return microtime(true) - $timer;
    } else {
        return null;
    }
}

/**
 * Create table with random name b_xxxxxx
 *
 * @param int $rnd
 * @param PDO $test
 * @return bool $ret
 */
function tabCreate($rnd, $test)
{
    $ret = $test->exec("CREATE table b_$rnd like a; INSERT INTO b_tables (b_table_name) values ('b_$rnd');");
    return $ret;
}

/**
 * Truncate table
 *
 * @param string $tabName
 * @param PDO $test
 * @return bool $ret
 */
function tabTruncate($tabName, $test)
{
    $ret = $test->exec("truncate table $tabName");
    return $ret;
}