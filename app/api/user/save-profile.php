<?php

require $_SERVER['DOCUMENT_ROOT'] . '/api/core.php';

use \Bitrix\Main\Error,
	\Bitrix\Main\UserTable,
	\App\Ctx,
	\App\User\Helper as UserHelper;

$request = Ctx::request();
$result = new ApiResult($request);

$data = [];

if (!$request->isAjaxRequest() || !$request->isPost() || !\check_bitrix_sessid()) {
	$result->addError(new Error('Некорректный запрос'));
}

global $USER, $USER_FIELD_MANAGER;

if ($result->isSuccess()) {
	$userId = (int)$USER->GetID();
	if (!$userId) {
		$result->addError(new Error('Требуется авторизация'));
	} else {
		// Явная выборка только необходимых полей
		$user = UserTable::getRow([
			'filter' => ['=ID' => $userId],
			'select' => [
				'ID', 'PERSONAL_PHOTO',
				'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL',
				'PERSONAL_BIRTHDAY', 'PERSONAL_PHONE', 'PERSONAL_STREET', 'PERSONAL_NOTES', 'PERSONAL_CITY',
				UserHelper::SUBSCRIBE_EMAIL_PROP_CODE,
				UserHelper::SMS_AGREEMENT_PROP_CODE
			]
		]);
		if (!$user) {
			$result->addError(new Error('Пользователь не найден'));
		} else {
			$allowed = [
				'NAME', 'LAST_NAME', 'SECOND_NAME', 'EMAIL',
				'PERSONAL_BIRTHDAY', 'PERSONAL_PHONE', 'PERSONAL_STREET', 'PERSONAL_NOTES', 'PERSONAL_CITY'
			];

			$arFields = [];
			// Базовые поля: добавляем только если значение меняется
			foreach ($allowed as $field) {
				$newVal = $request->getPost($field);
				if ($newVal !== null) {
					$newVal = is_string($newVal) ? trim($newVal) : $newVal;
					$oldVal = isset($user[$field]) ? (is_string($user[$field]) ? trim($user[$field]) : $user[$field]) : null;
					if ($newVal !== $oldVal) $arFields[$field] = $newVal;
				}
			}

			// Чекбоксы UF: рассчитываем желаемое значение и сравниваем со старым
			$ufCheckboxes = [UserHelper::SUBSCRIBE_EMAIL_PROP_CODE, UserHelper::SMS_AGREEMENT_PROP_CODE];
			foreach ($ufCheckboxes as $uf) {
				$desired = ($request->getPost($uf) !== null) ? '1' : '0';
				$old = isset($user[$uf]) ? (string)$user[$uf] : '0';
				if ($desired !== $old) $arFields[$uf] = $desired;
			}

			// Фото: загруженное или удаление — только если требуется
			$photoDel = $request->get('PERSONAL_PHOTO_del');
			$photoFile = $request->getFile('PERSONAL_PHOTO');
			if ($photoDel === 'Y') {
				$arFields['PERSONAL_PHOTO'] = ['del' => 'Y'];
			} elseif (is_array($photoFile) && !empty($photoFile['name'])) {
				$photoFile['old_file'] = $user['PERSONAL_PHOTO'];
				$arFields['PERSONAL_PHOTO'] = $photoFile;
			}

			// UF-поля из менеджера — соберём отдельно и добавим только отличающиеся
			$ufFields = [];
			$USER_FIELD_MANAGER->EditFormAddFields('USER', $ufFields);
			foreach ($ufFields as $key => $value) {
				// Пропускаем уже обработанные чекбоксы, чтобы не перезаписать логику
				if (in_array($key, $ufCheckboxes, true)) continue;
				$old = isset($user[$key]) ? $user[$key] : null;
				if ($value !== $old) $arFields[$key] = $value;
			}

			if (!count($arFields)) {
				$data = ['ok' => true, 'message' => 'Изменений не обнаружено'];
			} else {
				$obUser = new \CUser();
				if ($obUser->Update($userId, $arFields)) {
					$data = ['ok' => true, 'message' => 'Данные профиля сохранены'];
				} else {
					$result->addError(new Error($obUser->LAST_ERROR ?: 'Ошибка сохранения'));
				}
			}
		}
	}
}

if (!$result->isSuccess()) {
	$data['ok'] = false;
	$data['errors'] = $result->getErrorMessages();
}
$result->setData($data);

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();


