<?php 

$phar = new Phar('nof5.phar', 0, 'nof5.phar');
$phar->buildFromDirectory('../src');
$phar->setStub($phar->createDefaultStub('load.php'));
