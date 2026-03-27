<?php

use \App\Ctx,
	\App\Location;

$result = new ApiResult(Ctx::request());

if (!$result->isSuccess()) {
	/** @noinspection PhpUnhandledExceptionInspection */
	$result->sendJsonResponse();
}

$requestParams = unserialize(urldecode(Ctx::request()->get('params')));
$method = strtoupper(Ctx::request()->getRequestMethod());

$data = [];

global $APPLICATION;
switch ($method) {
	case 'POST':

		ob_start();
		$APPLICATION->IncludeComponent(
			'app:location.change.list',
			'main',
			[
				'CACHE_TYPE' => 'A',
				'CACHE_TIME' => APP_CACHE_W,
				'FILTER' => [
					[
						'LOGIC' => 'OR',
						[
							'NAME.NAME' => Ctx::config()->get('location.bigCities'),
							'=TYPE_ID' => [Location::TYPE_CITY]
						],
						[
							'=ID' => Ctx::location()->getId()
						]
					]
				],
				'URI' => $requestParams['uri'] ?: ''
			]
		);
		$html = ob_get_clean();

		$data = [
			'html' => $html,
			'script' => 'App.initLocations()'
		];

		break;
	default:
		break;
}

$result->setData($data);

//В массив записываем результат выполнения
$result->setData($data);

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();