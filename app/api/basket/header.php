<?php

require $_SERVER['DOCUMENT_ROOT'] . '/api/core.php';

use \App\Ctx;

$result = new ApiResult(Ctx::request());

if (!$result->isSuccess()) {
	/** @noinspection PhpUnhandledExceptionInspection */
	$result->sendJsonResponse();
}

ob_start();
$smallBasketData = $APPLICATION->IncludeComponent(
	'app:sale.basket.small',
	'main',
);
$header = ob_get_clean();

ob_start();
$APPLICATION->IncludeComponent(
	'app:sale.basket.small',
	'count',
);
$account = ob_get_clean();

$data = [
	'headerHtml' => $header,
	'accountMenuHtml' => $account,
	'storage' => [
		'basketCount' => $smallBasketData['ITEMS_COUNT']
	]
];

$result->setData($data);

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();