<?php
require 'vendor/autoload.php';
$phpunit = new PHPUnit\TextUI\Command();
$phpunit->run(['vendor/bin/phpunit']);
