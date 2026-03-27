<?php

use \App\Ctx,
	\App\Str,
	\App\Template\Helper as TemplateHelper,
	\App\Order\Helper as OrderHelper;

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
			if ((($price = Ctx::request()->get('total')) || ($price = $requestParams['total'])) && $hours = OrderHelper::getCreditCoolingHoursByPrice($price)) {
				ob_start(); ?>
				<div class="modal_installment">
					<div class="modal_installment__description">
						При оформлении рассрочки действует обязательный период охлаждения
						— <?= $hours . ' ' . Str::ending($hours, 'час', 'часа', 'часов') ?>.<br/><br/>
						Это время нужно для того, чтобы средства поступили от банка.<br/><br/>
						После его завершения заказ будет подтверждён, и вы сможете получить украшения любым удобным
						способом.
						<div class="modal_installment__description__close mt-3 pt-3 pt-md-0 mt-md-5">
							<button class="button w-100" data-bs-dismiss="modal">Хорошо</button>
						</div>
					</div>
				</div>
				<? $html = ob_get_contents();

				$config = [
					'id' => 'order-payment-installment',
					'title' => 'Придется немного подождать',
					'banner' => '',
					'content' => $html
				];
				$data = [
					'success' => true,
					'html' => $html,
					'template' => TemplateHelper::getAsideTemplate($config),
				];
			} else {
				$data = ['success' => false];
			}
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