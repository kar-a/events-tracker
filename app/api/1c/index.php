<?php

require $_SERVER['DOCUMENT_ROOT'] . '/api/core.php';

use \Bitrix\Main\Error,
	\App\Ctx,
	\App\Location\Helper as LocationHelper;

$result = new ApiResult(Ctx::request());

if (!$result->isSuccess()) {
	/** @noinspection PhpUnhandledExceptionInspection */
	$result->sendJsonResponse();
}

if (LocationHelper::getRealIp() != APP_IP_1C && !\isDevEx()) {
	//$result->setStatus(404);
	//$result->sendJsonResponse();
	//die();
}

$method = Ctx::request()->get('method');
$methodsMap = [
	'change-order-status',
	'register-clients-changes',
	'register-products-changes',
	'get-products',
	'get-offers',
];

if (!in_array($method, $methodsMap) || !file_exists($_SERVER['DOCUMENT_ROOT'] . '/api/1c/' . $method . '.php')) {
	$result->setStatus(404);
	$result->addError(new Error('Метод ' . $method . ' не найден'));
} else {
	$data = [];
	require_once $method . '.php';
	$result->setData($data);
}

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();
