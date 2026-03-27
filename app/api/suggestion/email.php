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
		if ($query = trim(Ctx::request()->get('q'))) {
			$service = new Suggestion();

			$data = [ /* Апи подсказок по email https://dadata.ru/api/suggest/email/ */
				'items' => $service->searchEmail($query, 3),
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
