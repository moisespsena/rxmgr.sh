<?php
ini_set('display_errors', 1);
require_once('Cmd.php');
require_once('template.php');
require_once('actions.php');

function cmd($cmd, $decode = true) {
    $c = Cmd::make('python')->arg(".bin/parse.py")->arg("exec")->arg($cmd)->run();
    echo "COMMAND: " . $c->getFullCommand() . "\n";
    $data = $c->stdout();
    var_dump($data);
    if($data && $decode) {
        $data = json_decode(trim($data));
    }
    return $data;
}

$data = cmd("add_display('moi')");
echo "\n";
    var_dump($data);
echo "\n";
