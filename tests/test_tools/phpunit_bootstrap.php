<?php

require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . '/../../vendor/pradosoft/prado/framework/Prado.php');

$application = new \Prado\Test\Unit\Harness\TTestApplication();

// Prado applies extra.prado class maps and message files only for installed packages.
// The extension under test is the root package, so they are registered here.
if (method_exists(\Prado\Prado::class, 'registerClassMap')) {
	\Prado\Prado::registerClassMap(json_decode(file_get_contents(__DIR__ . '/../../config/classes.json'), true));
}
\Prado\Exceptions\TException::addMessageFile(__DIR__ . '/../../src/Data/SqlMap/DataMapper/messages.txt');
