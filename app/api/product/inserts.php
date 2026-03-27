<?php

require $_SERVER['DOCUMENT_ROOT'] . '/api/core.php';

use \Bitrix\Main\Error,
	\App\Ctx,
	\App\Template\Helper as TemplateHelper;

$result = new ApiResult(Ctx::request());

if (!$result->isSuccess()) {
	/** @noinspection PhpUnhandledExceptionInspection */
	$result->sendJsonResponse();
}

$method = strtoupper(Ctx::request()->getRequestMethod());
$productXmlId = intval(Ctx::request()->get('productXmlId'));

$data = [
	'success' => false
];

switch ($method) {
	case 'GET':
	case 'POST':
		switch (Ctx::request()->get('action')) {

			//Информация о вставках на детальной
			case 'get':
				if (!$productXmlId) $result->addError(new Error('Не указан товар'));
				if (!$result->isSuccess()) break;

				//Получаем оффер по XML_ID с свойством INSERT
				$offers = \CIBlockElement::GetList(
					[],
					['IBLOCK_ID' => APP_IBLOCK_CATALOG_SKU, 'XML_ID' => $productXmlId, 'ACTIVE' => 'Y'],
					false,
					['nTopCount' => 1],
					['ID', 'IBLOCK_ID', 'PROPERTY_INSERT']
				);
				if (!$offer = $offers->GetNext(false, false)) {
					$result->addError(new Error('Товар не найден'));
					break;
				}

				$insert = $offer['PROPERTY_INSERT_VALUE'] ?? null;

				if (!$insert) {
					//Если вставок нет, возвращаем пустой HTML
					$detailHtml = '';
				} else {
					//Генерируем HTML вставок
					ob_start();
					echo TemplateHelper::getInsertsHtml($insert);
					$detailHtml = ob_get_clean();
				}

				$data = [
					'success' => true,
					'detailHtml' => mb_convert_encoding($detailHtml, 'UTF-8', 'UTF-8'),
				];

				break;
			default:
				break;
		}

		break;
	default:
		break;
}

if (!$result->isSuccess()) {
	$data['success'] = false;
	$data['errors'] = $result->getErrorMessages();
}
$result->setData($data);

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();
