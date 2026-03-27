<?php

require $_SERVER['DOCUMENT_ROOT'] . '/api/core.php';

use \Bitrix\Main\Error,
	\App\Ctx,
	\App\Exchange,
	\App\Catalog\Helper as CatalogHelper;

$result = new ApiResult(Ctx::request());

if (!$result->isSuccess()) {
	/** @noinspection PhpUnhandledExceptionInspection */
	$result->sendJsonResponse();
}

$method = strtoupper(Ctx::request()->getRequestMethod());
$productId = intval(Ctx::request()->get('productId'));

$data = [
	'success' => false
];

$items = CatalogHelper::getWishlistItems();
switch ($method) {
	case 'POST':
		switch (Ctx::request()->get('action')) {
			//Добавление в избранное
			case 'add':
				if (!$productId) $result->addError(new Error('Не указан товар'));
				if (!$result->isSuccess()) break;

				if (!in_array($productId, $items)) {
					$items[] = $productId;
					$items = array_unique($items);

					CatalogHelper::setWishlistItems($items);

					//Синхронизация с ImShop (не блокируем ответ)
					try {
						global $USER;
						if ($USER->IsAuthorized() && $userId = (int)$USER->GetID()) {
							Exchange::addToStackExternalRequest(Exchange::SEND_WISHLIST_METHOD, Exchange::getServiceTypeByCode(Exchange::SERVICE_IMSHOP), [
								'wishlist' => $items,
								'userId' => $userId
							], $userId);
						}
					} catch (\Throwable $e) {
					}
				}

				//Html блока в шапке
				ob_start();
				$APPLICATION->IncludeComponent(
					'app:catalog.wishlist.link',
					'main',
				);
				$headerHtml = ob_get_clean();

				//Html бейджа в меню
				ob_start();
				$APPLICATION->IncludeComponent(
					'app:catalog.wishlist.link',
					'count',
				);
				$accountMenuHtml = ob_get_clean();

				$data = [
					'success' => true,
					'headerHtml' => mb_convert_encoding($headerHtml, 'UTF-8', 'UTF-8'),
					'accountMenuHtml' => mb_convert_encoding($accountMenuHtml, 'UTF-8', 'UTF-8'),
				];

				break;

			//Удаление товара из избранного
			case 'delete':
				if ($productId) {
					if (!$productId) $result->addError(new Error('Не указан товар'));
					if (!$result->isSuccess()) break;

					if (in_array($productId, $items) && ($key = array_search($productId, $items)) !== null) {
						unset($items[$key]);
						CatalogHelper::setWishlistItems($items);

						//Синхронизация с ImShop (не блокируем ответ)
						try {
							global $USER;
							if ($USER->IsAuthorized() && $userId = (int)$USER->GetID()) {
								Exchange::addToStackExternalRequest(Exchange::SEND_WISHLIST_METHOD, Exchange::getServiceTypeByCode(Exchange::SERVICE_IMSHOP), [
									'wishlist' => $items,
									'userId' => $userId
								], $userId);
							}
						} catch (\Throwable $e) {
						}
					}

					//Html блока в шапке
					ob_start();
					$APPLICATION->IncludeComponent(
						'app:catalog.wishlist.link',
						'main',
					);
					$headerHtml = ob_get_clean();

					//Html бейджа в меню
					ob_start();
					$APPLICATION->IncludeComponent(
						'app:catalog.wishlist.link',
						'count',
					);
					$accountMenuHtml = ob_get_clean();

					$data = [
						'success' => true,
						'headerHtml' => mb_convert_encoding($headerHtml, 'UTF-8', 'UTF-8'),
						'accountMenuHtml' => mb_convert_encoding($accountMenuHtml, 'UTF-8', 'UTF-8'),
					];
				} else {
					$result->addError(new Error('Не указан товар'));
				}
				break;

			//Получение списка избранного
			case 'getWishlist':
				//Html блока в шапке
				ob_start();
				$APPLICATION->IncludeComponent(
					'app:catalog.wishlist.link',
					'main',
				);
				$headerHtml = ob_get_clean();

				//Html бейджа в меню
				ob_start();
				$APPLICATION->IncludeComponent(
					'app:catalog.wishlist.link',
					'count',
				);
				$accountMenuHtml = ob_get_clean();

				//Список товаров вишлиста
				$items = array_unique(CatalogHelper::getWishlistItems());

				$data = [
					'success' => true,
					'headerHtml' => mb_convert_encoding($headerHtml, 'UTF-8', 'UTF-8'),
					'accountMenuHtml' => mb_convert_encoding($accountMenuHtml, 'UTF-8', 'UTF-8'),
					'items' => $items,
				];
				break;
			default:
				break;
		}

		break;
	default:
		break;
}

if (!$result->isSuccess()) {
	$data['success'] = false;
	$data['errors'] = $result->getErrorMessages();
}
$result->setData($data);

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();