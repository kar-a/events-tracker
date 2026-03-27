<?php

require $_SERVER['DOCUMENT_ROOT'] . '/api/core.php';

use \Bitrix\Main\Error,
	\App\Ctx,
	\App\Logger\Rotator as LogRotator;

$result = new ApiResult(Ctx::request());

if (!$result->isSuccess()) {
	/** @noinspection PhpUnhandledExceptionInspection */
	$result->sendJsonResponse();
}

$method = Ctx::request()->get('method');
$methodsMap = [
	'authorize',
	'calculate-basket',
	'create-order',
	'filter',
	'get-delivery-methods',
	'get-form',
	'get-orders-history',
	'get-pay-systems',
	'get-personal-price',
	'get-products-availability',
	'get-shops-list',
	'get-user-info',
	'loyalty',
	'order-set-status',
	'orders-history',
	'process-payment',
	'wishlist',
	'search',
	'submit-form',
	'update-user',
];

if (!in_array($method, $methodsMap) || !file_exists($_SERVER['DOCUMENT_ROOT'] . '/api/webhook/' . $method . '.php')) {
	$result->setStatus(404);
	$result->addError(new Error('Метод ' . $method . ' не найден'));
} else {
	$data = [];
	require_once $method . '.php';
	$result->setData($data);
}

$logDir = 'imshop/';
ulogging([
	'method' => $method,
	'$_REQUEST' => $_REQUEST,
	'Ctx::request()->getRequestMethod()' => Ctx::request()->getRequestMethod(),
	'Ctx::request()->getInput()' => json_decode(Ctx::request()->getInput(), true),
	'data' => $result->getData(),
	'isSuccess' => $result->isSuccess()
], $logDir.'_webhook_' . date('d.m.Y'));
$logDir = implode( '', [$_SERVER['DOCUMENT_ROOT'] , '/upload/logs/' , $logDir]);
$maxTime = 60 * 60 * 24 * 30; //Месяц
(new LogRotator($logDir, $maxTime))->run();

//Для метода получения персональной цены, при отсутствии данных отдаем ответ 204
if ($method == 'get-personal-price' && !$result->getData()) {
	$result->setStatus(204);
	/** @noinspection PhpUnhandledExceptionInspection */
	$result->sendEmptyResponse();
}

//Для других случаев обычный ответ
/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();