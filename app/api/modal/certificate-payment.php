<?php

use \App\Ctx,
	\App\Template\Helper as TemplateHelper;

$result = new ApiResult(Ctx::request());

if (!$result->isSuccess()) {
	/** @noinspection PhpUnhandledExceptionInspection */
	$result->sendJsonResponse();
}

$requestParams = unserialize(urldecode(Ctx::request()->get('params')));
$method = strtoupper(Ctx::request()->getRequestMethod());

$data = [];

switch ($method) {
	case 'POST':
	case 'GET':
		try {
			global $APPLICATION;
			
			//Получаем HTML из компонента с шаблоном main
			ob_start();
			$APPLICATION->IncludeComponent(
				'app:catalog.certificate.payment',
				'main',
			);
			$html = ob_get_clean();

			$config = [
				'id' => 'certificate-payment',
				'title' => 'Выберите способ оплаты',
				'banner' => '',
				'content' => $html
			];

			$data = [
				'success' => true,
				'html' => $html,
				'template' => TemplateHelper::getAsideTemplate($config),
			];

		} catch (\Exception $e) {
			$data = [
				'success' => false,
				'error' => $e->getMessage()
			];
		}
		break;
	default:
		break;
}

$result->setData($data);

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();

