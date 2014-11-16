<?php

App::uses('ComponentCollection', 'Controller');
App::uses('WirecardCheckoutSeamlessComponent',
        'WirecardCheckoutSeamlessComponent.Controller/Component');

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

        $this->mDatastorageInitRequest =
            $this->getMock(
                'at\externet\WirecardCheckoutSeamless\Api\DatastorageInitRequest'
        );

        $Collection = new ComponentCollection();
        $mockedController = $this->getMock('Controller', array('afterWirecardTransferNotification'));
        $this->Controller = $mockedController;
        $this->t = new WirecardCheckoutSeamlessComponent($Collection);
        $this->t->dataStorageInitRequest = $this->mDatastorageInitRequest;

        Cache::clear();
    }

    public function testInitDataStorage()
    {
        $this->mDatastorageInitRequest->expects($this->once())
                ->method('Send')
                ->with(Configure::read('WirecardCheckoutSeamless')['secret']);
        $this->t->InitDataStorage('a', 'b');
    }

}