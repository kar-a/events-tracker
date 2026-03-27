<?php

use \Bitrix\Main\Error,
	\Bitrix\Sale\Order,
	\Bitrix\Main\Type\DateTime,
	\App\Ctx,
	\App\EventHandler\Sale\Order as OrderEvents,
	\App\Order\Helper as OrderHelper;

$method = strtoupper(Ctx::request()->getRequestMethod());

$data = [
	'success' => false
];
$result = new ApiResult(Ctx::request());

switch ($method) {
	case 'POST':
		if ($guid = Ctx::request()->get('guid')) {
			$data['guid'] = $guid;
		} else {
			$result->addError(new Error('Не указан guid заказа'));
			break;
		}

		if (!($ar = Order::loadByFilter([
				'filter' => ['=XML_ID' => $guid],
				'select' => ['ID', 'XML_ID', 'STATUS_ID'],
				'limit' => 1
			])) || !($order = $ar[0])) {
			$result->addError(new Error('Заказ не найден'));
			break;
		}

		if ($status1c = Ctx::request()->get('status')) {
			$data['status'] = $status1c;
			if (!$status = OrderHelper::convertStatusFrom1c($status1c, $order)) {
				$result->addError(new Error('Не найдено соответствие статусу ' . $status1c));
				break;
			}
		} else {
			$result->addError(new Error('Не указан статус заказа'));
			break;
		}

		OrderHelper::saveOrderHistory($order->getId(), 'Cтатус из 1c: ' . $status1c);

		$statusFrom = $order->getField('STATUS_ID');
		if ($statusFrom == $status) {
			$result->addError(new Error('Заказ уже в статусе ' . $status1c));
			break;
		}

		if (!OrderHelper::canChangeStatusFrom1c($statusFrom, $status)) {
			$result->addError(new Error('Нельзя изменить статус с ' . OrderHelper::convertStatusTo1c($statusFrom) . ' на ' . $status1c));
			break;
		}

		//Отключаем события, отправляющие заказ обратно в 1с
		OrderEvents::disable1cEvents();

		//Записываем статус, если все ок
		$order->setField('STATUS_ID', $status);

		//Информация об оплате для заказов с оплатой наличными
		if ($status == OrderHelper::STATUS_COMPLETED && !OrderHelper::isOnlinePaySystem($order) && $paymentCollection = $order->getPaymentCollection()) {
			foreach ($paymentCollection as $payment) {
				if ($payment->isPaid()) continue;
				$_fields = [
					'PAY_VOUCHER_DATE' => new DateTime(),
					'PS_SUM' => $order->getPrice(),
				];
				$payment->setFields($_fields);
				$payment->setPaid('Y');
			}
		}

		//Сохраняем, если изменился
		if ($order->isChanged()) {
			$res = $order->save();
			if (!$res->isSuccess()) {
				$result->addError(new Error('Ошибка сохранения заказа: ' . implode('; ', $res->getErrorMessages())));
				break;
			}
		}

		//Включаем события 1с
		OrderEvents::enable1cEvents();

		$data['success'] = true;

		break;
	default:
		break;
}

if (!$result->isSuccess()) {
	$data['success'] = false;
	$data['errors'] = $result->getErrorMessages();
}