<?php

require $_SERVER['DOCUMENT_ROOT'] . '/api/core.php';

use \Bitrix\Main\Context,
	\Bitrix\Main\Error,
	\Bitrix\Sale,
	\Bitrix\Sale\Basket,
	\Bitrix\Sale\BasketItem,
	\Bitrix\Sale\Fuser,
	\App\Ctx,
	\App\Basket\Action,
	\App\Catalog\Helper as CatalogHelper;

$request = Context::getCurrent()->getRequest();

$result = new ApiResult($request);

if (!$result->isSuccess()) {
	/** @noinspection PhpUnhandledExceptionInspection */
	$result->sendJsonResponse();
}

//Получаем нужные данные
$method = strtoupper($request->getRequestMethod());

$productId = $request->get('productId');
$elementId = $request->get('elementId');
$basketItemId = $request->get('itemId');
$quantity = $request->get('quantity');
$coupon = $request->get('coupon');

$siteId = $request->get('siteId') ?: Context::getCurrent()->getSite();
$fUserId = $request->get('userId') ?: Fuser::getId(true);
$basket = Basket::loadItemsForFUser($fUserId, $siteId);

//В массив записываем результат выполнения
$data = [];

switch ($method) {

	//Получить содержимое корзины
	case 'GET':
		$data['basket'] = array_map(function (BasketItem $basketItem) {
			return $basketItem->getFieldValues();
		}, $basket->getBasketItems());
		break;

	//Добавить/удалить товар из корзины, изменить количество
	case 'POST':
		$catalogAction = new Action();
		$product = $catalogAction->checkProductInBasket($productId);

		switch (Ctx::request()->get('action')) {

			//Добавление в корзину
			case 'add':
				if (!$productId) {
					$result->addError(new Error('Не указан товар'));
					break;
				}

				if ($product && $product['DELAY'] == 'Y') {
					$fields = [
						'DELAY' => 'N',
						'SUBSCRIBE' => 'N',
						'QUANTITY' => $quantity
					];
					$actionResult = $catalogAction->doAction(
						Action::ACTION_UPDATE,
						$productId,
						$fields
					);
				} else {
					$catalogAction = new Action();
					$actionResult = $catalogAction->doAction(
						Action::ACTION_ADD_TO_BASKET,
						$productId,
						$quantity
					);
				}

				if (!$actionResult->isSuccess()) {
					$result->addErrors($actionResult->getErrors());
					$result->setStatus(500);
					break;
				}

				ob_start();
				$APPLICATION->IncludeComponent(
					'app:sale.basket.small',
					'main',
				);
				$basket = ob_get_clean();

				ob_start();
				$APPLICATION->IncludeComponent(
					'app:sale.basket.small',
					'count',
				);
				$account = ob_get_clean();

				$data = [
					'success' => $actionResult->isSuccess(),
					'headerHtml' => mb_convert_encoding($basket, 'UTF-8', 'UTF-8'),
					'accountMenuHtml' => mb_convert_encoding($account, 'UTF-8', 'UTF-8')
				];

				break;

			//Обновление количества товара в корзине
			case 'update':
				if (!$productId) {
					$result->addError(new Error('Не указан товар'));
					break;
				}

				try {
					if (!$quantity) {
						$result->addError(new Error('Не указано количество'));
						break;
					}

					$catalogAction = new Action();
					$actionResult = $catalogAction->doAction(
						Action::ACTION_UPDATE,
						$productId,
						['QUANTITY' => $quantity]
					);

					if (!$actionResult->isSuccess()) {
						$result->addErrors($actionResult->getErrors());
						$result->setStatus(500);
						break;
					}

					$data = [
						'productId' => $productId,
						'quantity' => $quantity,
						'success' => $actionResult->isSuccess()
					];
				} catch (\Exception $e) {
					$result->addError(new Error($e->getMessage()));
				}
				break;

			//Удаление товара из корзины
			case 'delete':

				if (!$productId) {
					$result->addError(new Error('Не указан товар'));
					break;
				}

				$catalogAction = new Action();
				$actionResult = $catalogAction->doAction(
					Action::ACTION_DELETE,
					$productId
				);

				if (!$actionResult->isSuccess()) {
					$result->addErrors($actionResult->getErrors());
					$result->setStatus(500);
				}

				ob_start();
				$APPLICATION->IncludeComponent(
					'app:sale.basket.small',
					'main',
				);
				$basket = ob_get_clean();

				ob_start();
				$APPLICATION->IncludeComponent(
					'app:sale.basket.small',
					'count',
				);
				$account = ob_get_clean();

				$_basket = CatalogHelper::getBasketProducts();
				$data = [
					'productId' => $productId,
					'success' => $actionResult->isSuccess(),
					'headerHtml' => mb_convert_encoding($basket, 'UTF-8', 'UTF-8'),
					'accountMenuHtml' => mb_convert_encoding($account, 'UTF-8', 'UTF-8'),
					'basketElements' => array_keys($_basket['ADDED_ELEMENTS'])
				];

				break;
			case 'addCoupon':
				if (!$coupon) {
					$result->addError(new Error('Купон не найден'));
					break;
				}

				$couponInfo = Sale\DiscountCouponsManager::getData($coupon);
				if (!$couponInfo || $couponInfo['ACTIVE'] !== 'Y') {
					$result->addError(new Error('Купон не найден'));
				}

				if (!Sale\DiscountCouponsManager::add($coupon)) {
					$result->addError(new Error('Не удалось применить купон'));
					break;
				} else {
					$data = [
						'coupon' => $coupon,
						'success' => true,
					];
				}

				break;
			case 'removeCoupon':
				if (!$coupon) {
					$result->addError(new Error('Купон не найден'));
					break;
				}

				$couponInfo = Sale\DiscountCouponsManager::getData($coupon);
				if (!$couponInfo || $couponInfo['ACTIVE'] !== 'Y') {
					$result->addError(new Error('Купон не найден'));
				}

				if (!Sale\DiscountCouponsManager::delete($coupon)) {
					$result->addError(new Error('Не удалось убрать купон'));
					break;
				} else {
					$data = [
						'coupon' => $coupon,
						'success' => true,
					];
				}

				break;
			default:
				$result->addError(new Error('Не указано действие'));
				break;
		}
		break;

	default:
		break;
}

if ($result->isSuccess() && $method !== 'POST') {
	$r = $basket->save();
	if (!$r->isSuccess()) {
		$result->addErrors($r->getErrors());
		$result->setStatus(500);
	}
}

//Результат выполнения
$result->setData($data);

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();
