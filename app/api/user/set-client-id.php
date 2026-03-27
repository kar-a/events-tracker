<?php

require $_SERVER['DOCUMENT_ROOT'] . '/api/core.php';

use \Bitrix\Main\Error,
	\Bitrix\Main\UserTable,
	\App\Ctx,
	\App\User\Helper as UserHelper;

$result = new ApiResult(Ctx::request());

$data = [];

if (Ctx::request()->isAjaxRequest() && Ctx::request()->isPost() && \check_bitrix_sessid() && ($clientId = trim(Ctx::request()->get('clientId')))) {
	//Записываем ClientID в куки на 30 дней
	Ctx::getInstance()->set('ClientID', $clientId, UserHelper::USER_DATA_COOKIE_TIME);

	if ($userId = intval(Ctx::request()->get('userId'))) {
		if (UserHelper::getUserId() != $userId) { //Менять ClientID можем только себе
			$result->addError(new Error('Пользователь не найден.'));
		} else {
			//Записываем ClientID в пользователя
			global $USER;
			if ($USER->IsAuthorized()) {
				if ($user = UserTable::getRow(['filter' => ['=ID' => $userId], 'select' => ['ID', UserHelper::USER_YM_CLIENT_ID_PROP_CODE]])) {
					if ($clientId && $user[UserHelper::USER_YM_CLIENT_ID_PROP_CODE] != $clientId) {
						$ob = new \CUser;
						if ($ob->Update($user['ID'], [UserHelper::USER_YM_CLIENT_ID_PROP_CODE => $clientId])) {
							$data = [
								'userId' => $userId,
								'clientId' => $clientId,
								'updated' => true,
							];
						} else {
							$result->addError(new Error($ob->LAST_ERROR));
						}
					} else {
						$data = [
							'userId' => $userId,
							'clientId' => $clientId,
							'updated' => false,
						];
					}

					//Флаг, чтобы не проверять на каждом хиту
					Ctx::session()->set('CLIENT_ID_CHECKED', true);
				} else {
					$result->addError(new Error('Пользователь не найден'));
				}
			} else {
				Ctx::session()->set('CLIENT_ID_CHECKED', true);
			}
		}
	}
}

if (!$result->isSuccess()) {
	$data['success'] = false;
	$data['errors'] = $result->getErrorMessages();
}
$result->setData($data);

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();