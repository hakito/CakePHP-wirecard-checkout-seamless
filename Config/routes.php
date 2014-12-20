<?php

/* Add route for handling payment notifications */
Router::connect('/wirecard_checkout_seamless/process/**', array(
	'plugin' => 'wirecard_checkout_seamless',
	'controller' => 'wirecard_checkout_seamless',
	'action' => 'process'
));

/* Add route for handling payment notifications */
Router::connect('/wirecard_checkout_seamless/CorsFallback/**', array(
	'plugin' => 'wirecard_checkout_seamless',
	'controller' => 'wirecard_checkout_seamless',
	'action' => 'CorsFallback'
));