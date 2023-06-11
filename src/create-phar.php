<?php
/*
 *  Usage: php -d phar.readonly=0 create-phar.php
 */

try
{
    $pharFile = '../bin/msu.phar';
    $finalFile = '../bin/msu';
    $mainFile='msu.php';
    $buildFromDir='/.';

    // clean up
    if (file_exists($pharFile))
    {
        unlink($pharFile);
    }
    if (file_exists($finalFile))
    {
        unlink($finalFile);
    }
    if (file_exists($pharFile . '.gz'))
    {
        unlink($pharFile . '.gz');
    }

    // create phar
    $phar = new Phar($pharFile);

    // start buffering. Mandatory to modify stub to add shebang
    $phar->startBuffering();

    // Create the default stub from main.php entrypoint
    $defaultStub = $phar->createDefaultStub($mainFile);

    // Add the rest of the apps files
    //$phar->buildFromDirectory(__DIR__ . '/app');
    $phar->buildFromDirectory(__DIR__ . $buildFromDir);

    // Customize the stub to add the shebang
    $stub = "#!/usr/bin/env php \n" . $defaultStub;

    // Add the stub
    $phar->setStub($stub);

    $phar->stopBuffering();

    // plus - compressing it into gzip
    $phar->compressFiles(Phar::GZ);

    # Make the file executable
    chmod(__DIR__ . '/'.$pharFile, 0770);
    rename($pharFile, $finalFile);

    echo "$finalFile successfully created" . PHP_EOL;
}
catch (Exception $e)
{
    echo $e->getMessage();
}