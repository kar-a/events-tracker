<?php

use \Bitrix\Main\Error,
	\App\Ctx,
	\App\Template\Helper as TemplateHelper;

/** @var \CMain $APPLICATION */

$result = new ApiResult(Ctx::request());
if (!$result->isSuccess()) {
	/** @noinspection PhpUnhandledExceptionInspection */
	$result->sendJsonResponse();
}

$requestParams = unserialize(urldecode(Ctx::request()->get('params')));
$elementId = (int)($requestParams['elementId'] ?? 0);
$template = (string)($requestParams['template'] ?? 'main');
$method = strtoupper(Ctx::request()->getRequestMethod());

$data = [];

global $APPLICATION;
switch ($method) {
	case 'POST':
		if ($elementId <= 0) {
			$result->addError(new Error('Не указан товар'));
			break;
		}

		ob_start();
		$APPLICATION->IncludeComponent(
			'app:catalog.element',
			$template,
			[
				'IBLOCK_TYPE' => APP_IBLOCK_TYPE_CATALOG,
				'IBLOCK_ID' => APP_IBLOCK_CATALOG,
				'ELEMENT_ID' => $elementId,
				'PROPERTY_CODE' => [],
				'OFFERS_FIELD_CODE' => ['XML_ID'],
				'ACTION_VARIABLE' => 'action',
				'PRODUCT_ID_VARIABLE' => 'id',
				'SECTION_ID_VARIABLE' => 'SECTION_ID',
				'PRODUCT_QUANTITY_VARIABLE' => 'quantity',
				'PRODUCT_PROPS_VARIABLE' => 'prop',
				'CACHE_TYPE' => 'N',
				'CACHE_TIME' => 0,
				'CACHE_GROUPS' => 'N',
				'SET_TITLE' => 'N',
				'SET_META_DESCRIPTION' => 'N',
				'SET_META_KEYWORDS' => 'N',
				'SET_STATUS_404' => 'N',
				'PRICE_CODE' => [APP_PRICE_CODE],
				'PRICE_VAT_INCLUDE' => 'Y',
				'PRICE_VAT_SHOW_VALUE' => 'N',
				'PRODUCT_PROPERTIES' => [],
				'CONVERT_CURRENCY' => 'N',
				'CURRENCY_ID' => APP_CURRENCY,
				'USE_ELEMENT_COUNTER' => 'N',
				'ADD_ELEMENT_CHAIN' => 'N',
				'ADD_SECTIONS_CHAIN' => 'N',
				'SEF_URL_TEMPLATES' => [],
				'SEF_FOLDER' => TemplateHelper::PAGE_URL_CATALOG_BASE,
				'ADD_PROPERTIES_TO_BASKET' => 'N',
				'SKIP_DATES_CHECK' => 'Y',
				'SKIP_PERMISSIONS_CHECK' => 'Y',
				'COMPATIBLE_MODE' => 'Y',
				'COMPACT_MODE' => 'Y',
			],
			false
		);
		$html = ob_get_clean();

		//Строим offcanvas‑шаблон под aside
		$config = [
			'id' => 'quick_buy',
			'title' => '',
			'banner' => '',
			'content' => $html
		];

		$data = [
			'success' => true,
			'template' => TemplateHelper::getAsideTemplate($config),
		];
		break;
	default:
		break;
}

$result->setData($data);
/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();


