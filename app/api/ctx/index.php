<?php

require $_SERVER['DOCUMENT_ROOT'] . '/api/core.php';

use \App\Ctx,
	\App\Location;

$result = new ApiResult(Ctx::request());

if (!$result->isSuccess()) {
	/** @noinspection PhpUnhandledExceptionInspection */
	$result->sendJsonResponse();
}

$data = ['success' => false];

if (($params = Ctx::request()->get('params')) && is_array($params) && $params) {
	foreach ($params as $param => $value) {
		if (is_string($param) && ($param = trim($param)) && in_array(gettype($value), ['boolean', 'integer', 'double', 'string', 'NULL'])) {
			if (Ctx::getInstance()->set($param, strval($value))) {
				if ($param == 'loc.id') {
					Ctx::getInstance()->set('loc.detection_method', Location::DETECTION_METHOD_USER_SET);

					$data = ['success' => true];
				}
			}
		}
	}
}

//Результат выполнения
$result->setData($data);

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();