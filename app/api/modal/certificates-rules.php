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
			<div class="modal_certificates_rules page__article">
				<ul class="mt-2">
					<li class="page__paragraph page__paragraph_margin_bottom_small">
						Подарочный сертификат может быть использован для полной или частичной оплаты изделий в
						магазинах Ювелирного дома «ТРИМИАТА».
					</li>
					<li class="page__paragraph page__paragraph_margin_bottom_small">
						Если сумма покупок превышает лимит подарочного сертификата, покупатель вправе доплатить
						разницу. Если сумма покупок меньше номинала сертификата, то разница не остается на
						балансе
						подарочного сертификата.
					</li>
					<li class="page__paragraph page__paragraph_margin_bottom_small">
						Подарочные сертификаты не подлежат обмену и возврату.
					</li>
					<li class="page__paragraph page__paragraph_margin_bottom_small">
						При покупке подарочного сертификата купоны и скидки не действуют.
					</li>
					<li class="page__paragraph page__paragraph_margin_bottom_small">
						Подарочный сертификат действует в течение двух лет.
					</li>
					<li class="page__paragraph page__paragraph_margin_bottom_small">
						Товары, приобретённые с помощью сертификата, возврату не подлежат.
					</li>
					<li class="page__paragraph page__paragraph_margin_bottom_small">
						Сертификат не подлежит обмену на деньги.
					</li>
					<li class="page__paragraph page__paragraph_margin_bottom_small">
						Повреждённые подарочные сертификаты или имеющие признаки подделки к оплате не
						принимаются.
					</li>
					<li class="page__paragraph page__paragraph_margin_bottom_small">
						Ювелирный дом «ТРИМИАТА» не несёт ответственности за утраченный подарочный сертификат.
					</li>
				</ul>
			</div>
			<? $html = ob_get_contents();

			$config = [
				'id' => 'certificates-rules.php',
				'title' => 'Правила использования сертификата',
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