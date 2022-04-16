<?php
try
{
    $pharFile = 'bin/msu';
    $destFile=$_SERVER['HOME'].'/bin/msu';

    // clean up
    if (!file_exists($pharFile)) {
        echo "$pharFile not found".PHP_EOL;
        return;
    }
    if(!@copy($pharFile,$destFile)) {
       throw new Exception("$pharFile not copied".PHP_EOL);
    }
    if (!chmod($destFile, 0770)) {
        echo("warning: could not ste execution permission to '$destFile' ".PHP_EOL);
    }

    echo "$pharFile installed to '$destFile'".PHP_EOL;
    return;
}
catch (\Throwable $e)
{
    echo $e->getMessage();
}