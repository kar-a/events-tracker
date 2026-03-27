<?php

require $_SERVER['DOCUMENT_ROOT'] . '/api/core.php';

use \Bitrix\Main\Error,
	\App\Ctx;

$result = new ApiResult(Ctx::request());

$method = Ctx::request()->get('method');
$methodsMap = [
	'news',
];

if (!in_array($method, $methodsMap) || !file_exists($_SERVER['DOCUMENT_ROOT'] . '/api/rss/' . $method . '.php')) {
	$result->setStatus(404);
	$result->addError(new Error('Метод ' . $method . ' не найден'));
} else {
	$data = [];
	require_once $method . '.php';
	$result->setData($data);
}

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendXmlResponse();