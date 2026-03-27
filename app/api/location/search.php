<?php

require $_SERVER['DOCUMENT_ROOT'] . '/api/core.php';

use \App\Ctx,
	\App\Location;

$result = new ApiResult(Ctx::request());

if (!$result->isSuccess()) {
	/** @noinspection PhpUnhandledExceptionInspection */
	$result->sendJsonResponse();
}

ob_start();
$locations = $APPLICATION->IncludeComponent(
	'app:location.search',
	'',
	[
		'QUERY' => Ctx::request()->get('q'),
		'LANGUAGE_ID' => LANGUAGE_ID,
		'FILTER' => [
			'TYPE_ID' => [Location::TYPE_CITY, Location::TYPE_VILLAGE]
		],
		'SELECT' => [

		]
	]
);
$html = ob_get_clean();

$data = [
	'items' => array_values($locations),
];

$result->setData($data);

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();