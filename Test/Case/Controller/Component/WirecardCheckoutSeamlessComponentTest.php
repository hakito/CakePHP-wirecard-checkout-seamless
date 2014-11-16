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
        $mockedController = $this->getMock('Controller', array('afterWirecardTransferNotification'));
        $this->Controller = $mockedController;
        $this->t = new WirecardCheckoutSeamlessComponent($Collection);

        $this->mDatastorageInitRequest = $this->getMock(
            'at\externet\WirecardCheckoutSeamless\Api\DatastorageInitRequest'
        );
        $this->mDatastorageInitResponse = $this->getMock(
            'at\externet\WirecardCheckoutSeamless\Api\DatastorageInitResponse'
        );

        $this->t->dataStorageInitRequest = $this->mDatastorageInitRequest;

        Cache::clear();
    }

    public function testStartup()
    {
        $this->t->startup($this->Controller);
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

}
