<?php
/**
 * All  plugin tests
 */
class AllWirecardCheckoutSeamlessTest extends CakeTestCase {

/**
 * Suite define the tests for this plugin
 *
 * @return void
 */
	public static function suite() {
		$suite = new CakeTestSuite('All test');

		$path = CakePlugin::path('WirecardCheckoutSeamless') . 'Test' . DS . 'Case' . DS;
		$suite->addTestDirectoryRecursive($path);

		return $suite;
	}

}
