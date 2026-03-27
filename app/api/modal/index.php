<?php

require $_SERVER['DOCUMENT_ROOT'] . '/api/core.php';

use \Bitrix\Main\Error,
	\App\Ctx;

$result = new ApiResult(Ctx::request());

if (!$result->isSuccess()) {
	/** @noinspection PhpUnhandledExceptionInspection */
	$result->sendJsonResponse();
}

$modalName = Ctx::request()->get('modal-name');

if (!$modalName) {
	$result->setStatus(404);
	$result->addError(new Error('Не указано имя модального окна'));
	$result->sendJsonResponse();
}

$modalFile = __DIR__ . '/' . $modalName . '.php';
if (!file_exists($modalFile)) {
	$result->setStatus(404);
	$result->addError(new Error('Не найдено модальное окно "' . $modalName . '"'));
	$result->sendJsonResponse();
}

$data = include $modalFile;

if (!$data) {
	$result->setStatus(404);
	$result->addError(new Error('Не удалось загрузить модальное окно "' . $modalName . '"'));
} else {
	$result->setData($data);
}

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();