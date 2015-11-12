<?php

$sock_file = getenv('RXMGR_SERVER_SOCK_FILE');

if(empty($sock_file)) {
    $sock_file = sys_get_temp_dir() . '/rxmgr.sock';
}

define('SOCK_FILE', $sock_file);

class CommandException extends Exception
{
    var $cmd = null;

    function __construct ($cmd)
    {
        parent::__construct($cmd->err, $cmd->status);
        $this->cmd = $cmd;
    }
}

function cmd($cmd, $decode=true, &$c=null) {
    $key = file_get_contents(__DIR__ . '/key');
    $message = (strlen($cmd) + strlen($key) + 1) . "\n$key\n$cmd\n";

    $rcv_msg = "";
    $socket = stream_socket_client('unix://' . SOCK_FILE,
                                    $errorno, $errorstr, 5);
    stream_set_timeout($socket, 10);

    if(!fwrite($socket, $message))
        die("Error while writing!!!");

    $data = "";

    while(($result = fread($socket, 1024))) {
        $data .= $result;
    }

    fclose($socket);

    $c = json_decode($data);

    if(! is_null($c)) {
        if($c->out && $decode) {
            $obj = json_decode(trim($c->out));
            if(is_null($obj)) {
                if($c->err) {
                   $c->err .= "\n";
                }
                else {
                    $c->err = "";
                }

                $c->err .= "[SCLIENT]: json_decode error server output.";
                $c->status = 10000;
            } else {
                $c->out = $obj;
            }
        }

        return $c->out;
    }
    else  {
        $c = (object) array('err' => '[SCLIENT]: server result is not a valid JSON data.', 'status' => 10001, 'out' => $data);
        return $data;
    }
}

if(isset($_SERVER['argv']) && count($_SERVER['argv']) == 2) {
    $c = null;
    cmd($_SERVER['argv'][1], true, $c);

    if($c->err) {
        file_put_contents('php://stderr', trim($c->err) . "\n", FILE_APPEND);
    }

    if($c->out || !$c->status) {
        print(json_encode($c->out) . "\n");
    }

    exit($c->status);
}
