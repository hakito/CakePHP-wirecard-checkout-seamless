<?php

App::uses('ComponentCollection', 'Controller');
App::uses('WirecardCheckoutSeamlessComponent', 'WirecardCheckoutSeamless.Controller/Component');

use at\externet\WirecardCheckoutSeamless\Api;

class WirecardCheckoutSeamlessComponentTest extends CakeTestCase
{

    /** @var \WirecardCheckoutSeamlessComponent component */
    public $t = null;
    public $Controller = null;

    /** @var Api\DataStorageInitRequest */
    public $mDatastorageInitRequest;

    /** @var Api\DataStorageInitResponse */
    public $mDatastorageInitResponse;
    
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $Collection = new ComponentCollection();
        $mockedController = $this->getMock('Controller', array('afterWirecardTransferNotification', 'redirect'));
        $this->Controller = $mockedController;
        $mockRequest = $this->getMock('CakeRequest');
        $this->Controller->request = $mockRequest;
        $this->t = new WirecardCheckoutSeamlessComponent($Collection);

        $this->mDatastorageInitRequest = $this->getMock(
            'at\externet\WirecardCheckoutSeamless\Api\DataStorageInitRequest'
        );
        $this->mDatastorageInitResponse = $this->getMock(
            'at\externet\WirecardCheckoutSeamless\Api\DataStorageInitResponse'
        );

        $this->t->dataStorageInitRequest = $this->mDatastorageInitRequest;
        $this->t->startup($this->Controller);
        Cache::clear();
    }

    public function testStartup()
    {
        // FROM setUp
        // $this->t->startup($this->Controller);
        $this->assertEquals($this->Controller, $this->t->Controller);
    }

    public function testInitDataStorageCallsSetters()
    {
        $this->mDatastorageInitRequest->expects($this->once())
            ->method('Send')
            ->will($this->returnValue($this->mDatastorageInitResponse));
        $expected = array(
            'customerId' => 'a',
            'shopId' => 'b',
            'javascriptScriptVersion' => 'c',
            'orderIdent' => 'd',
            'returnUrl' => 'http://localhost/eps_bank_transfer/CorsFallback.php',
            'language' => 'f'
        );

        Configure::write('WirecardCheckoutSeamless', array_merge($expected, array('secret' => 's')));

        foreach ($expected as $key => $val)
        {
            $pascalCase = strtoupper(substr($key, 0, 1)) . substr($key, 1);
            $method = 'Set' . $pascalCase;
            $this->mDatastorageInitRequest->expects($this->once())
                    ->method($method)
                    ->with($val);
        }

        $this->t->InitDataStorage($expected['orderIdent'], $expected['language']);
    }

    public function testInitDataStorageCallsSend()
    {
        $config = Configure::read('WirecardCheckoutSeamless');
        $this->mDatastorageInitRequest->expects($this->once())
                ->method('Send')
                ->with($config['secret'])
                ->will($this->returnValue($this->mDatastorageInitResponse));
        $this->t->InitDataStorage('a', 'b');
    }

    public function testInitDataStorageThrowsExceptionOnError()
    {
        $this->mDatastorageInitResponse->expects($this->once())
             ->method('GetErrors')
             ->will($this->returnValue(1));

        $this->mDatastorageInitRequest->expects($this->once())
                ->method('Send')
                ->will($this->returnValue($this->mDatastorageInitResponse));

        $this->setExpectedException('WirecardRequestException');
        $this->t->InitDataStorage('a', 'b');
    }

    public function testPaymentRedirectReturnsResponse()
    {
        $config = Configure::read('WirecardCheckoutSeamless');
        $mFrontendInitRequest = $this->getMock(
            'at\externet\WirecardCheckoutSeamless\Api\FrontendInitRequest'
        );
        $mFrontendInitResponse = $this->getMock(
            'at\externet\WirecardCheckoutSeamless\Api\FrontendInitResponse'
        );
        $this->t->frontendInitRequest = $mFrontendInitRequest;

        $mFrontendInitRequest->expects($this->once())
                ->method('Send')
                ->with($config['secret'])
                ->will($this->returnValue($mFrontendInitResponse));

        $this->t->PaymentRedirect(array());
    }

    public function testPaymentRedirectExceptionOnError()
    {
        $config = Configure::read('WirecardCheckoutSeamless');
        $mFrontendInitRequest = $this->getMock(
            'at\externet\WirecardCheckoutSeamless\Api\FrontendInitRequest'
        );
        $mFrontendInitResponse = $this->getMock(
            'at\externet\WirecardCheckoutSeamless\Api\FrontendInitResponse'
        );
        $mFrontendInitResponse->expects($this->once())
             ->method('GetErrors')
             ->will($this->returnValue(1));

        $this->t->frontendInitRequest = $mFrontendInitRequest;

        $mFrontendInitRequest->expects($this->once())
                ->method('Send')
                ->with($config['secret'])
                ->will($this->returnValue($mFrontendInitResponse));

        $this->setExpectedException('WirecardRequestException');
        $this->t->PaymentRedirect(array());
    }

    public function testPaymentRedirectSetsDefaultParams()
    {
        $mFrontendInitRequest = $this->getMock(
            'at\externet\WirecardCheckoutSeamless\Api\FrontendInitRequest'
        );
        $mFrontendInitResponse = $this->getMock(
            'at\externet\WirecardCheckoutSeamless\Api\FrontendInitResponse'
        );
        $this->t->frontendInitRequest = $mFrontendInitRequest;
        $mFrontendInitRequest->expects($this->once())
                ->method('Send')
                ->will($this->returnValue($mFrontendInitResponse));

        $this->Controller->request->expects($this->once())
                ->method('clientIp')
                ->will($this->returnValue('1.2.3.4'));

        $this->Controller->request->staticExpects($this->once())
                ->method('header')
                ->with('User-Agent')
                ->will($this->returnValue('AgentSmith'));

        // Expected default params
        $mFrontendInitRequest->expects($this->once())
                ->method('SetConfirmUrl')
                ->with($this->stringContains('http'));
        $mFrontendInitRequest->expects($this->once())
                ->method('SetConsumerIpAddress')
                ->with('1.2.3.4');
        $mFrontendInitRequest->expects($this->once())
                ->method('SetConsumerUserAgent')
                ->with('AgentSmith');

        $this->t->PaymentRedirect(array());
    }

    public function testPaymentRedirectRedirect()
    {
        $mFrontendInitRequest = $this->getMock(
            'at\externet\WirecardCheckoutSeamless\Api\FrontendInitRequest'
        );
        $mFrontendInitResponse = $this->getMock(
            'at\externet\WirecardCheckoutSeamless\Api\FrontendInitResponse'
        );
        $mFrontendInitResponse->expects($this->once())
                ->method('GetRedirectUrl')
                ->will($this->returnValue('http://example.com'));

        $this->t->frontendInitRequest = $mFrontendInitRequest;
        $mFrontendInitRequest->expects($this->once())
                ->method('Send')
                ->will($this->returnValue($mFrontendInitResponse));

        $this->Controller->expects($this->once())
                ->method('redirect')
                ->with('http://example.com');

        $this->t->PaymentRedirect(array());
    }
}
