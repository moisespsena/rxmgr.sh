<?php
class Actions {
    function _e($data, $status=-1) {
        echo json_encode(array('error' => true, 'data' => $data, 'status' => $status));
        die();
    }

    function _cmd($cmd, &$decode=true, &$c=null) {
        $r = cmd($cmd, $decode, $c);
        if($c->status) {
            $this->_e($c->err, $c->status);
        }
        return $r;
    }
    
    function _s($data, $refresh=false) {
        echo json_encode(array('data' => $data, 'error' => false, 'refresh' => $refresh));
        die();
    }
    
    function create_display($options) {
       $display = $this->_cmd("create_display('{$options->user}', '-geometry {$options->width}x{$options->height}', password='{$options->password}')");
       $this->_s("Display #{$display[1]->ID} CRIADO com sucesso!", true);
    }

    function close_display($options) {
       $display = $this->_cmd("close_display('{$options->user}', '{$options->display}', password='{$options->password}')");
       $this->_s("Display {$options->display} ENCERRADO com sucesso!");
    }

}
