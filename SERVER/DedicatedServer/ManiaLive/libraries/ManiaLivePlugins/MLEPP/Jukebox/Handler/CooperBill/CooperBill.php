<?php

namespace ManiaLivePlugins\MLEPP\Jukebox\Handler\CooperBill;

/**
 * Description of CooperBill
 *
 * @author oliver
 */
class CooperBill {

    private $Bills;

    function __construct() {
        $this->Bills = array();
    }

    /**
     * Will ad a bill to be handle
     * @param Bill The bill you want to add
     */
    public function addBill(Bill $bill){
        $id = $bill->getBillId();
        $this->Bills[$id] = $bill;
    }

    public function getBill($id){
        return (isset($this->Bills[$id]) ? $this->Bills[$id] : nul);
    }


    public function doBill($id, $state){
        if(isset($this->Bills[$id])){
             switch ( $state ){
                 case 4 :
                     echo "do";
                    $this->Bills[$id]->doBill();
                    break;
             }

        }

    }


}
?>
