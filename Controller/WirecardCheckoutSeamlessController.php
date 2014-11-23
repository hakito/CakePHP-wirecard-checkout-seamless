<?php

/**
 * @property WirecardCheckoutSeamlessComponent $WirecardCheckoutSeamless
 */
class WirecardCheckoutSeamlessController extends AppController
{

    public $components = array('WirecardCheckoutSeamless.WirecardCheckoutSeamless');

    public function process($id)
    {
        $this->WirecardCheckoutSeamless->HandleConfirmationUrl($id, $_POST);
        $this->autoRender = false;
    }

}
