<?php
ini_set('display_errors', 1);
require_once('template.php');
require_once('actions.php');
require_once('../bin/sclient.php');

function main() {
    $data = cmd("list_displays()");
    pre_html('Gerenciamento de Área de Trabalho Remota');

    foreach($data as $user => $l) {
       echo "<h2>$user [<a href=\"javascript:void(0);\" data-user=\"{$user}\" data-action=\"create_display\" title=\"Criar novo Área de Trabalho\">&nbsp;+&nbsp;</a>]</h2>\n";
       echo "<ul>\n";
       foreach($l as $i) {
           list($proc, $cmd) = $i;
           $url = "{$_SERVER['SERVER_NAME']}:{$cmd->rfbport}";
           echo "<li>Área de Trabalho <b>:{$cmd->ID}</b> ({$cmd->geometry}) - <b>{$url}</b>\n";
           $connections = [];
           exec("netstat -atnp 2>/dev/null | grep ESTA | grep ':{$cmd->rfbport}' | awk '{print $5}' | grep -v ':{$cmd->rfbport}$'", $connections);
           echo "[<a href=\"javascript:void(0);\" data-user=\"{$proc->user}\" data-display=\"{$cmd->ID}\" data-action=\"close_display\">encerrar</a>]";
           if(count($connections) > 0) {
               echo "<div><b>Conexões ativas:</b><ul>";
               foreach($connections as $con) {
                   echo "<li>$con</li>";
               }
               echo "</ul></div>\n";
           }
           echo "</li>\n";
       }
       echo "</ul>\n";
    }
    ?>
    </ul>
    <?php
       end_html();
}

if($_SERVER['REQUEST_METHOD'] == 'GET') {
    main();
}
else if($_SERVER['REQUEST_METHOD'] == 'POST' && array_key_exists('action', $_POST)) {
    try {
        $actions = new Actions();
        $actionName = $_POST['action'];
        $actions->$actionName((object) $_POST);
    } catch(CommandException $e) {
	echo json_encode(array('error' => true, 'message' => $e->getMessage(), 'status' => $e->getCode(), 'command' => $e->cmd->getFullCommand(),
            'stdout' => $e->cmd->stdout(), 'stderr' => $e->cmd->stderr()));
        die();
    }
}
