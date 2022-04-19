<?php
// #!/usr/bin/env php
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\ConfirmationQuestion;

(new SingleCommandApplication())
    ->setName('Mutagen sync sessions utility') // Optional
    ->setVersion('1.0.0') // Optional
    ->addArgument('sess', InputArgument::OPTIONAL, 'session search string')

    ->addOption('inactive', 'i', InputOption::VALUE_NONE,"show only inactive sessions")
    ->addOption('active', 'a', InputOption::VALUE_NONE,"show only active sessions")
    ->addOption('selfupdate', null, InputOption::VALUE_NONE,"self update the phar file")
    ->addOption('pause', null, InputOption::VALUE_NONE,"pause sessions")
    ->addOption('resume', null, InputOption::VALUE_NONE,"resume sessions")
    ->addOption('reset', null, InputOption::VALUE_NONE,"reset sessions")
    ->addOption('flush', null, InputOption::VALUE_NONE,"flush sessions")

    ->setCode(function (InputInterface $input, OutputInterface $output) {


        // simple self update
        // if cmd selfupdate and it's phar, proceed
        if ($input->getOption("selfupdate") && strlen(Phar::running()) > 0) {
            $self=Phar::running(false);
            $output->writeln("Updating file: $self");
            $new=$self.'.tmp';
            if(copy("https://github.com/magmaconsulting/mutagen-sync-cli-utility/raw/main/bin/msu",$new)) {
                if (md5_file($new) == md5_file($self)) {
                    @unlink($new);
                    $output->writeln("Command not updated: the current version is already the newest");
                } else {
                    @rename($new,$self);
                    @chmod($self,0755);
                    $output->writeln("Command updated");
                }
            } else {
                $output->writeln("<error>Error downloading the new command</error>");
            }
            return;
        }

        $testCommand = strpos(PHP_OS, 'WIN') === 0 ? 'where' : 'command -v';
        if (!is_executable(trim((string)shell_exec("$testCommand mutagen    ")))) {
            $output->writeln("<error>Command mutagen not found</error>");
            return;
        }

        $cmd="mutagen sync list";
        $out=[];
        try {
            exec($cmd, $out);
        } catch (Exception $e) {
            $output->writeln("<error>Could not execute command: $cmd </error>");
        }

        $s=0;
        $sessions=[];

        $runningStatuses=["Watching for changes","Scanning files","Connecting to","Staging files"];

        foreach ($out as $o) {
            if (str_contains($o,"Name")) {
                $s++;
                $name=trim(ltrim($o,"Name:"));
                $sessions[$s]["name"]=$name;
            }
            if (str_contains($o,"Status")) {
                $status=trim(ltrim($o,"Status:"));
                $status=ltrim(rtrim($status,"]"),"[");
                $sessions[$s]["status"]=$status;
                $sessions[$s]["active"]=false;
                foreach($runningStatuses as $runningStatus) {
                    if (str_starts_with($status,$runningStatus)) {
                        $sessions[$s]["active"]=true;
                        break;
                    }
                }
            }
        }
        usort($sessions,function($a,$b){return strcmp($a["name"], $b["name"]);});

        $sessions=array_filter($sessions,function  ($session) use ($input){
            $sess=$input->getArgument("sess");
            if (!empty($sess) && !str_contains($session['name'],$sess)) {
                return false;
            }
            if ($input->getOption("active") && !$session['active']){
                return false;
            }
            if ($input->getOption("inactive") && $session['active']){
                return false;
            }
            return true;
        });
        if (!count($sessions)) {
            $output->writeln("No sessions found");
        }

        $output->writeln("Mutagen sync list");
        $table = new Table($output);
        $table->setHeaders(['num', 'Name', 'Active','Status']);
        $i=0;
        $show='enbaled';
        foreach($sessions as $session) {
            $table->setRow($i++,[$i,$session['name'],($session['active']?"<info>active</info>":"inactive"),$session['status']]);
        }
        $table->render();


        foreach(['pause','resume','reset','flush'] as $mscmd) {
            if ($input->getOption($mscmd)){
                $n=count($sessions);
                if (!$input->getOption('no-interaction')) {
                   if ($mscmd=='resume' && $n>4) {
                        $output->writeln("<error>You cannot resume more than 4 sessions at a time</error>");
                        return;
                    }
                    $output->writeln("WARNING: you are about to $mscmd $n sessions.");
                    $question = new ConfirmationQuestion('Are you sure you want to continue? [No] ', false);
                    $this->questionHelper = $this->getHelper('question');
                    if (!$this->questionHelper->ask($input, $output, $question)) {
                        return;
                    }
                }
                foreach($sessions as $session){
                    $cmd=sprintf("mutagen sync $mscmd %s",$session['name']);
                    $output->writeln($cmd);
                    system($cmd);
                }
                break;
            }
        }



    })
    ->run();