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

global $APPLICATION;
switch ($method) {
	case 'POST':
	case 'GET':

		try {
			ob_start(); ?>
			<div class="modal_installment">
				<div class="modal_installment__description">
					Каждое украшение создаётся вручную и может незначительно отличаться по весу — это влияет на стоимость. Все выбранные параметры, такие как длина, диаметр и цвет, полностью соответствуют заявленным характеристикам.
				</div>
			</div>
			<? $html = ob_get_contents();

			$config = [
				'id' => 'weight',
				'title' => 'Вес изделия',
				'banner' => '',
				'content' => $html
			];
			$data = [
				'success' => true,
				'html' => $html,
				'template' => TemplateHelper::getAsideTemplate($config),
			];
		} catch (\Exception $e) {

		}

		break;
	default:
		break;
}

$result->setData($data);

//В массив записываем результат выполнения
$result->setData($data);

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();