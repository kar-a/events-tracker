<?php

require $_SERVER['DOCUMENT_ROOT'] . '/api/core.php';

use \Bitrix\Main\Error,
	\App\Ctx;

$result = new ApiResult(Ctx::request());

if (!$result->isSuccess()) {
	/** @noinspection PhpUnhandledExceptionInspection */
	$result->sendJsonResponse();
}

$method = strtoupper(Ctx::request()->getRequestMethod());
$params = unserialize(urldecode(Ctx::request()->get('params')));

$data = [];

global $APPLICATION;
switch ($method) {
	case 'POST':

		if (!$params['IBLOCK_ID']) {
			$result->addError(new Error('Не указан инфоблок'));
			break;
		}

		$filter = [
			'IBLOCK_ID' => intval($params['IBLOCK_ID']),
			'FILTER_NAME' => 'arListFilter',
			'FILTER' => $params['FILTER']
		];
		global ${$filter['FILTER_NAME']};
		${$filter['FILTER_NAME']} = $filter['FILTER'];

		ob_start();
		$APPLICATION->IncludeComponent(
			($params['COMPONENT'] ?: 'bitrix:news.list'),
			($params['COMPONENT_TEMPLATE'] ?: 'news'),
			[
				'IBLOCK_TYPE' => ($params['IBLOCK_TYPE'] ?: APP_IBLOCK_TYPE_CONTENT),
				'IBLOCK_ID' => ($params['IBLOCK_ID'] ?: APP_IBLOCK_NEWS),
				'CACHE_TYPE' => 'A',
				'CACHE_TIME' => APP_CACHE_D,
				'CACHE_FILTER' => 'Y',
				'CACHE_GROUPS' => 'N',
				'PROPERTY_CODE' => ($params['PROPERTY_CODE'] ?: []),
				'FIELD_CODE' => ($params['FIELD_CODE'] ?: []),
				'NEWS_COUNT' => ($params['NEWS_COUNT'] ?: 10),
				'SORT_BY1' => 'SORT',
				'SORT_ORDER1' => 'ASC',
				'SORT_BY2' => 'ID',
				'SORT_ORDER2' => 'DESC',
				'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
				'SET_TITLE' => 'N',
				'FILTER_NAME' => $filter['FILTER_NAME'],
				'FILTER' => $filter['FILTER'],
			],
			false
		);
		$html = ob_get_clean();

		$data = [
			'html' => $html,
		];

		break;
	default:
		break;
}

$result->setData($data);

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();