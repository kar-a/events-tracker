<?php

require $_SERVER['DOCUMENT_ROOT'] . '/api/core.php';

use \App\Ctx,
	\App\Template\Helper as TemplateHelper;

$result = new ApiResult(Ctx::request());

if (!$result->isSuccess()) {
	/** @noinspection PhpUnhandledExceptionInspection */
	$result->sendJsonResponse();
}

$containerId = Ctx::request()->get('containerId');

ob_start();
$APPLICATION->IncludeComponent(
	'bitrix:sale.basket.basket',
	'main',
	[
		'OFFERS_PROPS' => [],
		'PATH_TO_ORDER' => TemplateHelper::PAGE_URL_ORDER,
		'COLUMNS_LIST' => [
			'NAME',
			'PRICE',
			'QUANTITY',
			'DELETE',
			'DISCOUNT',
			'PROPERTY_IMAGES'
		],
		'AJAX_MODE' => 'N',
		'AJAX_OPTION_JUMP' => 'N',
		'AJAX_OPTION_STYLE' => 'N',
		'AJAX_OPTION_HISTORY' => 'N',
		'QUANTITY_FLOAT' => 'N',
		'PRICE_VAT_SHOW_VALUE' => 'N',
		'SET_TITLE' => 'N',
		'AJAX_OPTION_ADDITIONAL' => 'N',
		'ACTION_VARIABLE' => 'action',
	]);
$html = ob_get_clean();

$result->setData([
	'html' => $html
]);

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();