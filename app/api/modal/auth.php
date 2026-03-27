<?php

use \Bitrix\Main\Error,
	\App\Ctx;

/** @var \CMain $APPLICATION */

$result = new ApiResult(Ctx::request());
if (!$result->isSuccess()) {
	/** @noinspection PhpUnhandledExceptionInspection */
	$result->sendJsonResponse();
}

$method = strtoupper(Ctx::request()->getRequestMethod());

$data = [];

global $APPLICATION, $USER;

switch ($method) {
	case 'POST':
		//Проверяем, авторизован ли пользователь
		if ($USER->IsAuthorized()) {
			$result->addError(new Error('Пользователь уже авторизован'));
			break;
		}

		//Включаем компонент для генерации ViewTarget
		ob_start();
		$APPLICATION->IncludeComponent(
			'app:system.auth.full',
			'popup',
			[
				'USE_LINK' => 'N',
				'USE_SMART_CAPTCHA' => 'Y',
			],
			false
		);
		ob_end_clean();

		//Извлекаем модальное окно из ViewTarget
		$html = $APPLICATION->GetViewContent('system.auth.popup');
		if (!$html) {
			$result->addError(new Error('Не удалось загрузить модальное окно авторизации'));
			break;
		}
		$data = [
			'success' => true,
			'html' => $html,
			'htmlFooter' => $APPLICATION->GetViewContent('system.auth.footer'), //Извлекаем иконку авторизации для мобильного footer
			'script' => 'App.initAuth()'
		];
		break;
	default:
		break;
}

$result->setData($data);
/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();

