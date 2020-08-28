#!/usr/bin/env php
<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../config.php';

use Symfony\Component\Console\Application;

$application = new Application();

$application->add(new \App\Command\DownloadCustomersCommand());

$application->run();