<?php

use \App\Ctx,
	\App\Str,
	\App\Exchange;

/** @var \Bitrix\Main\HttpRequest $request */
$request = Ctx::request();

$method = strtoupper($request->getRequestMethod());

$data = [];
switch ($method) {
	case 'GET':
		if ($id = Exchange::addToStackExternalRequest(Exchange::GET_CLIENTS_CHANGES_METHOD)) {
			$data = ['success' => true];

			$_startTimeStamp = microtime(true); //Время старта

			ulogging([
				'id' => $id,
				'_startTimeStamp' => $_startTimeStamp
			], '_clientsChanges');

			$sendRes = Exchange::sendStackRow($id);

			ulogging([
				'_sendRes' => $sendRes
			], '_clientsChanges');

			if (!is_array($sendRes) || !$sendRes['success'] || $sendRes['data']['error']) {
				throw new \Exception($sendRes['data']['error']);
			} elseif ($sendRes['success']) {
				if (isset($sendRes['data']['message']) && $message = trim($sendRes['data']['message'])) {
					/* $data['data']['message'] = $message; */
				} else {
					$time_execution = microtime(true) - $_startTimeStamp; //Время выполнения

					$data['data']['message'] = 'Импорт изменений клиентов завершен за ' . $time_execution . Str::ending($time_execution, ' секунду', ' секунды', ' секунд');
				}
			}
		}
		break;
	default:
		break;
}

