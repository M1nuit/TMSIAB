<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Handler\CooperBill;

/**
 * Description of Bill
 *
 * @author oliver
 */
class Bill {

    private $billId;
    private $action_methode;
    private $action_plugin;

    private $login;
    private $param;

    function __construct($billId, $action_methode, $action_plugin, $param, $login) {
        $this->billId = $billId;
        $this->action_methode = $action_methode;
        $this->action_plugin = $action_plugin;

        $this->param = $param;
        $this->login = $login;
    }

    public function getBillId() {
        return $this->billId;
    }

    public function setBillId($billId) {
        $this->billId = $billId;
        return $this;
    }

    public function getAction_methode() {
        return $this->action_methode;
    }

    public function setAction_methode($action_methode) {
        $this->action_methode = $action_methode;
        return $this;
    }

    public function getAction_plugin() {
        return $this->action_plugin;
    }

    public function setAction_plugin($action_plugin) {
        $this->action_plugin = $action_plugin;
        return $this;
    }

    public function doBill(){
        if(method_exists($this->action_plugin, $this->action_methode))
            call_user_func (array($this->action_plugin, $this->action_methode), $this->login, $this->param);
    }

}

?>
