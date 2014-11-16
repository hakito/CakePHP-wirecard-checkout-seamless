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
        
        $this->mDatastorageInitRequest =
            $this->getMock(
                'at\externet\WirecardCheckoutSeamless\Api\DatastorageInitRequest'
        );
        $this->t->dataStorageInitRequest = $this->mDatastorageInitRequest;

        Cache::clear();
    }

    public function testStartup()
    {
        $this->t->startup($this->Controller);
        $this->assertEquals($this->Controller, $this->t->Controller);
    }

    public function testInitDataStorage()
    {
        $config = Configure::read('WirecardCheckoutSeamless');
        $this->mDatastorageInitRequest->expects($this->once())
                ->method('Send')
                ->with($config['secret']);
        $this->t->InitDataStorage('a', 'b');
    }

}
