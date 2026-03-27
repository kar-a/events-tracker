<?php

require $_SERVER['DOCUMENT_ROOT'] . '/api/core.php';

use \App\Ctx,
	\App\Suggestions\Suggestion;

$result = new ApiResult(Ctx::request());

if (!$result->isSuccess()) {
	/** @noinspection PhpUnhandledExceptionInspection */
	$result->sendJsonResponse();
}

// получаем нужные данные
$method = strtoupper(Ctx::request()->getRequestMethod());

$data = [];

switch ($method) {
	//Получить список записей
	case 'GET':
	case 'POST':
		if (($query = trim(Ctx::request()->get('q'))) && ($kladr = trim(Ctx::request()->get('kladr')))) {
			$service = new Suggestion();
			$params = [
				'locations' => [
					'kladr_id' => $service->checkKladr($kladr)
				],
				'restrict_value' => true
			];

			/* Показывать в подсказках только улицы (без домов и квартир)
			 * $params['from_bound']['value'] = 'street';
			 * $params['to_bound']['value'] = 'street'; */

			$data = [ /* Подробнее об ограничениях поиска: https://confluence.hflabs.ru/pages/viewpage.action?pageId=426639432 */
				'items' => $service->searchAddress($query, 5, $params),
			];
		}
		break;
	default:
		break;
}

// в массив записываем результат выполнения
$result->setData($data);

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();
