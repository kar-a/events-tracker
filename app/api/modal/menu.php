<?php
//Этот файл подключается через /api/modal/index.php

use \Bitrix\Main\Error,
	\App\Ctx;

/** @var \CMain $APPLICATION */
/** @var \Bitrix\Main\Result $result */

$method = strtoupper(Ctx::request()->getRequestMethod());

$data = [];

global $APPLICATION;

switch ($method) {
	case 'GET':
	case 'POST':
		try {
			//Включаем компонент для генерации меню
			ob_start();
			$APPLICATION->IncludeComponent(
				'app:catalog.section.list',
				'header',
				[],
				false
			);
			$html = ob_get_clean();

			if (!$html || trim($html) === '') {
				$result->addError(new Error('Не удалось загрузить главное меню'));
				return $data;
			}

			$data = [
				'success' => true,
				'html' => $html,
				'script' => 'App.initHeaderMenu();App.initSectionsMenu();'
			];
		} catch (Exception $e) {
			$result->addError(new Error('Ошибка при загрузке главного меню: ' . $e->getMessage()));
		}
		break;
	default:
		break;
}

return $data;

