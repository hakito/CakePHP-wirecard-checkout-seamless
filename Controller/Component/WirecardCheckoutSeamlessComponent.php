<?php

App::uses('Component', 'Controller');

use at\externet\WirecardCheckoutSeamless\Api;

class WirecardCheckoutSeamlessComponent extends Component
{

    /** @var \Controller */
    public $Controller;

    /** @var Api\DataStorageInitRequest */
    public $dataStorageInitRequest;

    /** @var Api\FrontendInitRequest */
    public $frontendInitRequest;

    /** @var Api\ConfirmationResponse */
    public $confirmationResponse;

    public function __construct($collection)
    {
        parent::__construct($collection);
        $this->dataStorageInitRequest = new Api\DataStorageInitRequest();
        $this->frontendInitRequest = new Api\FrontendInitRequest();
        $this->confirmationResponse = new Api\ConfirmationResponse();
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
     * @throws WirecardRequestException when the response contains errors.
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

        return $this->TrySend($this->dataStorageInitRequest, $config['secret']);
    }

    /**
     * Redirects client to the next step. Depending on the selected payment type the client redirection will vary.
     *
     * This method expects at least the following params in the params array:
     * <ul>
     * <li>language - Language for displayed texts on payment page.</li>
     * <li>paymentType - Selected payment method of your consumer.</li>
     * <li>amount - Amount of payment.</li>
     * <li>currency - Currency code of amount.</li>
     * <li>orderDescription - Unique description of the consumer's order in a human readable form.</li>
     * <li>successUrl - URL of your online shop when checkout process was successful.</li>
     * <li>cancelUrl - URL of your online shop when checkout process has been cancelled.</li>
     * <li>failureUrl - URL of your online shop when an error occured within checkout process.</li>
     * <li>serviceUrl - URL of your service page containing contact information.</li>
     * </ul>
     *
     * You may specify optional parameters as documented here:
     * https://integration.wirecard.at/doku.php/request_parameters#optional_parameters
     *
     * Other required parameters will be generated automatically or read from the config. The priority for the
     * parmeter is:
     * <ol>
     * <li>Function argument (The argument you pass to this function)</li>
     * <li>Config (Parameters that are defined in the config)</li>
     * <li>Internally generated (Arguments that are internally generated)</li>
     * </ol>
     *
     * @param string $id Identifier that will be returned in your confirmation callback
     * @param array $params Array of required and optional parameters
     * @throws WirecardRequestException when the response contains errors.
     */
    public function PaymentRedirect($id, $params)
    {
        $config = Configure::read('WirecardCheckoutSeamless');

        $encodedId = urlencode($id);
        $defaultParams = array(
            'confirmUrl' => Router::url('/wirecard_checkout_seamless/process/' . $encodedId, true),
            'consumerIpAddress' => $this->Controller->request->clientIp(),
            'consumerUserAgent' => $this->Controller->request->header('User-Agent')
        );        
        $combinedParams = array_merge($defaultParams, $config, $params);
        foreach ($combinedParams as $key => $val)
        {
            $pascalCase = strtoupper(substr($key, 0, 1)) . substr($key, 1);
            $method = 'Set' . $pascalCase;
            
            if (method_exists($this->frontendInitRequest, $method))
            {
                $this->frontendInitRequest->$method($val);
            }
        }

        /* @var $response \at\externet\WirecardCheckoutSeamless\Api\FrontendInitResponse */
        $response = $this->TrySend($this->frontendInitRequest, $config['secret']);
        
        $this->Controller->redirect($response->GetRedirectUrl());
    }

    /**
     * Handler for confirmation URL
     * @param string $id Identifier for callback
     * @param array $post $_POST array expected
     * @internal This is called by the controller of the plugin. You should not call this method manually.
     */
    public function HandleConfirmationUrl($id, $post)
    {
        $config = Configure::read('WirecardCheckoutSeamless');
        $this->confirmationResponse->InitFromArray($post, $config['secret']);

        $callback = isset($config['ConfirmationCallback']) ?
                $config['ConfirmationCallback'] : 'afterWirecardCheckoutSeamlessNotification';

        return call_user_func_array(array($this->Controller, $callback), array($id, $this->confirmationResponse));
    }

    /**
     * Wrapper for wirecard request, that throws an exception in case of an error
     * @param Api\Request $request
     * @param string $secret
     * @return Api\Response response
     * @throws WirecardRequestException when GetErrors() > 0
     */
    private function TrySend($request, $secret)
    {
        $response = $request->Send($secret);
        if ($response->GetErrors() > 0)
        {
            $errors = $response->GetErrorArray();
            $message = 'Error during ' . get_class($request);
            $message .= isset($errors[1]) ? sprintf(' (%s).', $errors[1]->GetMessage()) : '.';
            $exception = new WirecardRequestException($message);
            $exception->response = $response;
            throw $exception;
        }
        return $response;
    }
}

class WirecardRequestException extends Exception {
    /** @var Api\Response */
    public $response;

}