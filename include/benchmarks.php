<?php
    require_once 'basic.php';
    
    require_once 'Benchmark/Timer.php';
    require_once ($localBasePath.'/include/connection.class.php');   

    function InsertBenchmarkDB(&$t, $table='benchmark', $page = null)
    {
        $n_of_vk_fields_avalable = 6;
        /* Use a table like this:        
        
            DROP TABLE benchmark;
            CREATE TABLE benchmark (
                     tt TIMESTAMP(8),
                     ip VARCHAR(16),
                     pag VARCHAR(100),
                     k1 VARCHAR(10),
                     v1 FLOAT,
                     k2 VARCHAR(10),
                     v2 FLOAT,
                     k3 VARCHAR(10),
                     v3 FLOAT,
                     k4 VARCHAR(10),
                     v4 FLOAT,
                     k5 VARCHAR(10),
                     v5 FLOAT,
                     k6 VARCHAR(10),
                     v6 FLOAT,
                     tot FLOAT                     
            );
            Note: The DB MUST be already connected.
                  The timestamp tt and pag are optional.
                  In order to remove the IP it's necessary a little code change
                  k5, v5 and following keys can be added.
        */
        
        
        global $_SERVER;
                
    
        $fields = 'ip';
        $values = "'{$_SERVER['REMOTE_ADDR']}'";

        if ($page===null) $page = DB::get()->quote($_SERVER['REQUEST_URI']); // an alternative may be: $_SERVER['PHP_SELF'];
        if (strlen($page)>0) //In case you don't want to use $pag you have to pass an empty string ''
        {
            $fields .= ', pag';
            $values .= ", $page";
        }
    
        $p = $t->getProfiling();
        $n = count($p); 
        
        //Now we always skip the first one and if we still have more than $n_of_vk_fields_avalable values we keep skipping the first that do not fit
        
        $skip = ($n > $n_of_vk_fields_avalable) ? ($n - $n_of_vk_fields_avalable) : 1;
        
        foreach ($p as $k => &$v) 
        {
            if($k >= $skip)
            {
                $i = $k - $skip + 1;
                $fields .= ", k$i, v$i";
                $values .= ", '{$p[$k]['name']}', {$p[$k]['diff']}";
            }
        }

        $fields .= ", tot";
        $values .= ", {$p[$k]['total']}";
                          
         
        $query1 = "INSERT INTO $table ($fields) VALUES ($values)";
         
        try { 
           $boInclusao = DB::get()->executeDML($query1);     //### MAybe I should check the result to be sure it has been written
        } catch (Exception $e) {
           //### It must be handle better: writing in the log file (that must be set) and 
           print_r($e);
        }        
    }

    /* 
     *  Example
     *
     *  Do something and save it to Database
     
     //Establish connection
     try {
        DB::get()->connect();
     } catch (Exception $e) {
        print_r($e); //### It must be handle better: writing in the log file (that must be set) and 
     }


    //I create timer object and start it (TRUE)
    $timer = new Benchmark_Timer(TRUE);
    //$timer->start(); //No need to start it. It is already.

    //DO something HERE
    echo '<table border="1">';
    foreach($_SERVER as $k => $v) {
        echo '<tr><td>'.$k.'</td><td>'.$v.'</td></tr>';
    }
    echo '</table>';
    
    $timer->setMarker('IDidSomething');

    //DO something else HERE

    $timer->stop(); //Or use $timer->setMarker('IDidStElse');


    //$timer->display(); // if you want to display immidiately and to output html formated

    //$profiling = $timer->getProfiling(); // get the profiler info as an associative array    
    //echo "<br> This is the array: <br> <pre>";
    //print_r($profiling);
    //echo "</pre><br>";

    InsertBenchmarkDB($timer);  //Save the data on DB
    
    */
        
?>
