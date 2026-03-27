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
$productXmlId = intval(Ctx::request()->get('productXmlId'));

$data = [
	'success' => false
];

switch ($method) {
	case 'GET':
	case 'POST':
		switch (Ctx::request()->get('action')) {

			//Информация о доставе на детальной
			case 'get':
				if (!$productXmlId) $result->addError(new Error('Не указан товар'));
				if (!$result->isSuccess()) break;

				ob_start();
				$APPLICATION->IncludeComponent(
					'app:catalog.element.detail.delivery',
					'main',
					[
						'PRODUCT_XML_ID' => $productXmlId,
						'LOCATION_CODE' => Ctx::location()->getCode(),
					]
				);
				$detailHtml = ob_get_clean();

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