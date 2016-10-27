<?php

use MLocati\IDNA\IdnaMapping\Table;

$defaultSavePath = implode(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'src', 'IdnaMap.php']);

$arguments = [];
$options = [];

$stopArgs = false;
foreach ($argv as $argi => $arg) {
    if ($argi === 0) {
        continue;
    }
    if ($stopArgs === true) {
        $arguments[] = $arg;
    } elseif ($arg === '--') {
        $stopArgs = true;
    } elseif ($arg === '-' && count($arguments) === 1) {
        $arguments[] = $arg;
    } elseif (isset($arg[0]) && $arg[0] === '-') {
        $options[] = $arg;
    } else {
        $arguments[] = $arg;
    }
}

if (empty($arguments) || count(array_intersect($options, ['-h', '--help', '/?'])) > 0) {
    $myName = basename(__FILE__, '.php');
    echo <<<EOT
This command is useful to parse the Unicode IDNA Mapping Table and create a PHP class from it.
We could parse this file on the fly at each run, but it would be really slow.

You can find the IDNA Mapping Table http://www.unicode.org/Public/idna/
The latest version is usually located at http://www.unicode.org/Public/idna/latest/IdnaMappingTable.txt

Syntax: $myName --debug <input-file> [<output-file>]\n";
- --debug: create the class with comments and superfluous code
- <input-file>: a local copy of the IDNA Mapping Table file (or its URL if your PHP installation supports it).
- <output-file>: where to save the generated PHP code. If not specified we'll output it to STDOUT.
  You can specify - to save to the default position ($defaultSavePath)

EOT;
    exit(1);
}
if (count($arguments) > 2) {
    echo "Too many arguments\n";
    exit(1);
}

$debug = false;
foreach ($options as $option) {
    switch ($option) {
        case '--debug':
            $debug = true;
            break;
        default:
            echo "Unknown option: $option\n";
            exit(1);
    }
}

try {
    require_once dirname(__DIR__).'/autoload.php';

    $filename = isset($arguments[1]) ? $arguments[1] : '';
    if ($filename === '-') {
        $filename = $defaultSavePath;
    }

    if ($filename !== '') {
        echo 'Loading file... ';
    }
    $table = Table::load($arguments[0]);
    if ($filename !== '') {
        echo "done.\n";
    }

    if ($filename !== '') {
        echo 'Creating PHP code... ';
    }
    $opts = [];
    if ($debug) {
        $opts['comments'] = true;
        $opts['disallowed'] = true;
    }
    $phpCode = $table->buildMapClass($opts);
    if ($filename !== '') {
        echo "done.\n";
    }

    if ($filename === '') {
        echo $phpCode;
    } else {
        echo 'Saving to '.$filename.'... ';
        if (@file_put_contents($filename, $phpCode) === false) {
            throw new Exception('Failed!');
        }
        echo "done.\n";
    }

    exit(0);
} catch (Exception $x) {
    echo $x->getMessage();
    exit(1);
}
die();
