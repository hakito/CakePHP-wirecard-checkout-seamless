<?php

App::uses('Component', 'Controller');

use at\externet\WirecardCheckoutSeamless\Api;

class WirecardCheckoutSeamlessComponent extends Component
{

    public $dataStorageInitRequest;

    public function __construct($collection)
    {
        parent::__construct($collection);
        $this->dataStorageInitRequest = new Api\DataStorageInitRequest();
    }

    public function startup(\Controller $controller)
    {
        parent::startup($controller);
        $this->Controller = $controller;
    }

    /**
     * Initialize Wirecard data storage.
     *
     * @param string $orderIdent Unique reference to the order of your consumer.
     * @param string $language Language for returned texts and error messages.
     *                         Alphabetic with a fixed length of 2.
     * @return Api\DataStorageInitResponse
     */
    public function InitDataStorage($orderIdent, $language)
    {
        $config = Configure::read('WirecardCheckoutSeamless');
        $this->dataStorageInitRequest->SetCustomerId($config['customerId']);
        $this->dataStorageInitRequest->SetOrderIdent($orderIdent);
        $this->dataStorageInitRequest->SetLanguage($language);
        $this->dataStorageInitRequest->SetReturnUrl(Router::url('/eps_bank_transfer/CorsFallback.php', true));
        
        if (!empty($config['shopId']))
            $this->dataStorageInitRequest->SetShopId($config['shopId']);

        if (!empty($config['javascriptScriptVersion']))
            $this->dataStorageInitRequest->SetJavascriptScriptVersion($config['javascriptScriptVersion']);

        return $this->dataStorageInitRequest->Send($config['secret']);
    }

}