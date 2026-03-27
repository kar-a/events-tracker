<?php

require $_SERVER['DOCUMENT_ROOT'] . '/api/core.php';

use \Bitrix\Main\Context,
	\Bitrix\Main\Error,
	\Bitrix\Sale\Order,
	\Bitrix\Sale\PaySystem,
	\App\Ctx,
	\App\Exchange,
	\App\Order\Helper as OrderHelper;

$reqest = Context::getCurrent()->getRequest();

$result = new ApiResult($reqest);

if (!$result->isSuccess()) {
	/** @noinspection PhpUnhandledExceptionInspection */
	$result->sendJsonResponse();
}

//Получаем нужные данные
$method = strtoupper($reqest->getRequestMethod());

//В массив записываем результат выполнения
$data = [];

switch ($method) {
	case 'POST':
		$action = Ctx::request()->get('action');

		//Проверка CSRF для критичных операций изменения данных
		if (in_array($action, ['changePaySystem', 'cancelOrder']) && !\check_bitrix_sessid()) {
			$result->addError(new Error('Ошибка проверки безопасности. Пожалуйста, обновите страницу.'));
			$result->setData(['sessid' => \bitrix_sessid()]);
			$result->sendJsonResponse();
			exit;
		}

		//Ищем заказ
		if (in_array($action, ['changePaySystem', 'cancelOrder', 'loadPaymentRetryComponent'])) {
			if ($id = (int)Ctx::request()->get('id')) {
				$data['orderId'] = $id;
			} else {
				$result->addError(new Error('Заказ не найден'));
				break;
			}

			if (!($order = Order::load($id))) {
				$result->addError(new Error('Заказ не найден'));
				break;
			}
		}

		global $USER;
		$userId = $USER->IsAuthorized() ? (int)$USER->GetID() : \CSaleUser::GetAnonymousUserID();
		
		//Проверка владельца заказа для критичных операций
		if ($order && in_array($action, ['changePaySystem', 'cancelOrder'])) {
			if (!$userId || $order->getField('USER_ID') !== $userId) {
				$result->addError(new Error('Ошибка доступа'));
				$result->sendJsonResponse();
				exit;
			}
		}

		if ($order) $statusId = $order->getField('STATUS_ID');
		switch (Ctx::request()->get('action')) {
			case 'changePaySystem':
				if ($order->getField('CANCELED') == 'Y' || $statusId == OrderHelper::STATUS_CANCELED) {
					$result->addError(new Error('Заказ отменен'));
					break;
				}

				if ($statusId == OrderHelper::STATUS_COMPLETED) {
					$result->addError(new Error('Заказ выполнен'));
					break;
				}

				if ($order->getField('PAYED') == 'Y') {
					$result->addError(new Error('Заказ уже оплачен'));
					break;
				}

				if ($paymentCollection = $order->getPaymentCollection()) {
					if (!$payment = $paymentCollection->current()) {
						$result->addError(new Error('Платеж не найден'));
						break;
					}
				}

				if (!$paySystemId = Ctx::request()->get('paySystemId')) {
					$result->addError(new Error('Платежная система не найдена'));
					break;
				}

				$data['undoPaySystemId'] = $order->getField('PAY_SYSTEM_ID');

				if (!$paySystemService = PaySystem\Manager::getObjectById($paySystemId)) {
					$result->addError(new Error('Платежная система не найдена'));
					break;
				}

				$availablePaySystems = PaySystem\Manager::getListWithRestrictions($payment);
				if (!$newPaySystem = $availablePaySystems[$paySystemId]) {
					$result->addError(new Error('Платежная система не найдена'));
					break;
				}

				//Изменяем платежную систему
				$fields = [
					'PAY_SYSTEM_ID' => $paySystemId,
					'PAY_SYSTEM_NAME' => $newPaySystem['NAME']
				];
				$payment->setFields($fields);

				//Рассчитываем скидки
				$discount = $order->getDiscount();
				$discount->setOrderRefresh(true);
				$discount->setApplyResult([]);

				$res = $discount->calculate();
				if ($res->isSuccess()) {
					if (($discountData = $res->getData()) && !empty($discountData) && is_array($discountData)) {
						$order->applyDiscount($discountData);
					}
				}

				//Обновляем корзину
				$basket = $order->getBasket();
				$basket->refreshData(['PRICE', 'COUPONS']);

				//Обновляем заказ
				$order->refreshData();
				$order->doFinalAction();

				//Сохраняем заказ
				$res = $order->save();
				if (!$res->isSuccess()) {
					$data['success'] = false;
					$result->addError(new Error(implode("\n", $res->getErrorMessages())));
					break;
				} else {
					//При изменении на наличные процесс редактирования прекращается
					if ($paySystemId == OrderHelper::PAY_SYSTEM_CASH) {
						if ($order->getField('STATUS_ID') == OrderHelper::STATUS_WAIT_PAYMENT) {
							$order->setField('STATUS_ID', OrderHelper::STATUS_PROCESSING);
							$order->save();
						}

						$data['script'] = 'location.reload();';
					}

					$data['success'] = true;
					$data['paySystemId'] = $order->getField('PAY_SYSTEM_ID');

					//Добавляем в очередь на изменение системы оплаты
					try {
						if (!Ctx::isDevDomain() && $rowId = Exchange::addToStackChangeOrderPayment($id)) {
							$sendRes = Exchange::sendStackRow($rowId);

							$data['rowId'] = $rowId;
							$data['sendRes'] = $sendRes;

							if (!is_array($sendRes) || !$sendRes['success'] || $sendRes['data']['error']) {
								$data['success'] = false;
								throw new \Exception($sendRes['data']['error']);
							}
						}
					} catch (Throwable $e) {
						$data['success'] = false;

						ulogging([
							'$order->getId()' => $order->getId(),
							'$e->getMessage()' => $e->getMessage(),
						], 'paySystemChangeErrors');

						$data['$order->getId()'] = $order->getId();
						$data['$e->getMessage()'] = $e->getMessage();
					}
				}

				//Получаем html полоски смены оплаты
				if ($result->isSuccess()) {
					ob_start();
					$APPLICATION->IncludeComponent(
						'app:sale.payment.retry',
						'main',
						[
							'ORDER_ID' => $_SESSION['SALE_ORDER_ID'] ?? null,
							'USER_ID' => $userId,
							'CHANGE_PAY_SYSTEM' => 'Y',
						],
						false
					);
					$data['html'] = ob_get_clean();
				}

				break;

			case 'cancelOrder':
				//Дополнительная проверка сессии
				if (!isset($_SESSION['SALE_ORDER_ID']) || !is_array($_SESSION['SALE_ORDER_ID']) || !in_array($id, $_SESSION['SALE_ORDER_ID'])) {
					$result->addError(new Error('Ошибка доступа'));
					break;
				}

				if ($order->getField('CANCELED') == 'Y' || $statusId == OrderHelper::STATUS_CANCELED) {
					$result->addError(new Error('Заказ отменен'));
					break;
				}

				if ($statusId == OrderHelper::STATUS_COMPLETED) {
					$result->addError(new Error('Заказ выполнен'));
					break;
				}

				if ($order->getField('PAYED') == 'Y') {
					$result->addError(new Error('Заказ уже оплачен'));
					break;
				}

				//Устанавливаем флаг отмены, после этого срабатывает событие добавления в очередь на отмену в 1С
				$order->setField('CANCELED', 'Y');
				//Устанавливаем статус
				$order->setField('STATUS_ID', OrderHelper::STATUS_CANCELED);

				//Сохраняем заказ
				$res = $order->save();
				if (!$res->isSuccess()) {
					$data['success'] = false;
					$result->addError(new Error(implode("\n", $res->getErrorMessages())));
					break;
				} else {
					$data['success'] = true;
				}

				break;

			case 'loadPaymentRetryComponent':

				ob_start();
				$APPLICATION->IncludeComponent(
					'app:sale.payment.retry',
					'main',
					[
						'ORDER_ID' => $_SESSION['SALE_ORDER_ID'] ?? null,
						'USER_ID' => $userId,
					],
					false
				);

				$data = [
					'success' => true,
					'html' => ob_get_clean()
				];

				break;
			case 'loadCurrentOrdersComponent':

				ob_start();
				$APPLICATION->IncludeComponent(
					'app:sale.orders.current.link',
					'main',
				);
				$header = ob_get_clean();

				ob_start();
				$APPLICATION->IncludeComponent(
					'app:sale.orders.current.link',
					'count',
				);
				$account = ob_get_clean();

				$data = [
					'success' => true,
					'headerHtml' => $header,
					'accountMenuHtml' => $account,
				];

				break;
			default:
				break;
		}
		break;
	default:
		break;
}

//Результат выполнения
$result->setData($data);

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();
