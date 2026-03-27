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
					Рассрочка до <b>300 000 ₽</b> — оформляется быстро и удобно в корзине, без переплат.<br/><br/>
					Внимание! Подтверждение рассрочки на покупку теперь занимает время:<br/>
					<ul class="modal_installment__variant_content">
						<li>— при сумме покупки <b>от&nbsp;50&nbsp;000&nbsp;₽ до&nbsp;200&nbsp;000&nbsp;₽ —
								4&nbsp;часа</b></li>
						<li>— при сумме покупки <b>от&nbsp;200&nbsp;000&nbsp;₽ — 48&nbsp;часов</b></li>
					</ul>
					<br/>
					Доступны варианты от следующих банков:
				</div>
				<div class="modal_installment__variants">
					<div class="modal_installment__variant">
						<div class="modal_installment__variant_header">
							<div class="modal_installment__variant_title">
								<div class="modal_installment__variant_icon">
									<img
										src="<?= SITE_TEMPLATE_PATH ?>/bundle/img/modal/modal_installment_variant_otp.png"
										alt=""/> />
								</div>
								<div class="modal_installment__variant_name">ОТП банк:</div>
							</div>
							<div class="modal_installment__variant_benefit">
								<svg class="modal_installment__variant_benefit_icon" xmlns="http://www.w3.org/2000/svg"
								     width="24" height="24" viewBox="0 0 24 24" fill="none">
									<path fill-rule="evenodd" clip-rule="evenodd"
									      d="M18.231 7.46967C18.5239 7.76256 18.5239 8.23744 18.231 8.53033L10.5007 16.2606L5.27045 11.0303C4.97756 10.7374 4.97756 10.2626 5.27045 9.96967C5.56334 9.67678 6.03822 9.67678 6.33111 9.96967L10.5007 14.1393L17.1704 7.46967C17.4632 7.17678 17.9381 7.17678 18.231 7.46967Z"
									      fill="currentColor"/>
								</svg>
								<div class="modal_installment__variant_benefit_descipription">
									Высокий процент одобрения
								</div>
							</div>
						</div>
						<ul class="modal_installment__variant_content">
							<li>— срок: от 3 до 12 месяцев</li>
							<li>— возраст: от 21 до 69 лет</li>
						</ul>
					</div>
					<div class="modal_installment__variant">
						<div class="modal_installment__variant_header">
							<div class="modal_installment__variant_title">
								<div class="modal_installment__variant_icon">
									<img
										src="<?= SITE_TEMPLATE_PATH ?>/bundle/img/modal/modal_installment_variant_sber.png"
										alt=""/>
								</div>
								<div class="modal_installment__variant_name">Сбербанк:</div>
							</div>
						</div>
						<ul class="modal_installment__variant_content">
							<li>— срок 3 / 4 / 6 /9 месяцев</li>
							<li>— возраст от 21 до 70 лет</li>
						</ul>
					</div>
					<div class="modal_installment__variant">
						<div class="modal_installment__variant_header">
							<div class="modal_installment__variant_title">
								<div class="modal_installment__variant_icon">
									<img
										src="<?= SITE_TEMPLATE_PATH ?>/bundle/img/modal/modal_installment_variant_tinkoff.png"
										alt=""/>
								</div>
								<div class="modal_installment__variant_name">Т-Банк:</div>
							</div>
						</div>
						<ul class="modal_installment__variant_content">
							<li>— срок 10 месяцев</li>
							<li>— возраст от 18 лет</li>
						</ul>
					</div>
					<div class="modal_installment__variant">
						Банк можно выбрать при оформлении заказа в корзине.
					</div>
				</div>
			</div>
			<? $html = ob_get_contents();

			$config = [
				'id' => 'installment',
				'title' => 'Условия рассрочки',
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