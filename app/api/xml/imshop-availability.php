<?php

use \App\Store\Helper as StoreHelper,
	\App\EventHandler\Sale\OnCondSaleActionsControlBuildList\Source as SaleSourceControl;

$data = [
	'header' => '<?xml version="1.0" encoding="utf-8" ?><inventory>',
	'data' => '',
	'footer' => '</inventory>'
];

$stores = StoreHelper::getStores();
if ($stores) {
	$mainStore = [];

	//Список складов
	$data['data'] .= '<outlets>';
	foreach ($stores as $store) {
		if (StoreHelper::isMainStore($store)) {
			$mainStore = $store;
		}
		$data['data'] .= '<outlet id="' . $store['ID'] . '">';
		$data['data'] .= '<name>' . $store['TITLE'] . '</name>';
		$data['data'] .= '<city>' . StoreHelper::getStoreCity($store) . '</city>';
		$data['data'] .= '<address>' . $store['ADDRESS'] . '</address>';
		$data['data'] .= '<lat>' . $store['GPS_N'] . '</lat>';
		$data['data'] .= '<lon>' . $store['GPS_S'] . '</lon>';
		$data['data'] .= '<online>' . (StoreHelper::isMainStore($store) ? 'true' : 'false') . '</online>';
		$data['data'] .= '<public>' . (StoreHelper::isMainStore($store) ? 'true' : 'false') . '</public>';
		$data['data'] .= '</outlet>';
	}
	$data['data'] .= '</outlets>';

	//Товары
	$data['data'] .= '<availability>';
	$select = ['ID', 'IBLOCK_ID', 'XML_ID', 'ACTIVE'];
	$rs = \CIBlockElement::GetList([], ['=IBLOCK_ID' => APP_IBLOCK_CATALOG_SKU], false, false, $select);
	while ($el = $rs->fetch()) {
		$data['data'] .= '<product ';
		$data['data'] .= 'id="' . $el['XML_ID'] . '" ';
		$data['data'] .= 'barcode="' . $el['XML_ID'] . '" ';
		$data['data'] .= 'outlet="' . $mainStore['ID'] . '" ';
		$data['data'] .= 'quantity="' . ($el['ACTIVE'] == 'Y' ? '1' : '0') . '" ';
		if ($el['ACTIVE'] == 'Y') {
			SaleSourceControl::enableMobileAppMode();
			$price = \CCatalogProduct::GetOptimalPrice($el['ID'], 1, [2]);
			SaleSourceControl::disableMobileAppMode();
			$_price = $price['RESULT_PRICE'];
			if ($_price['DISCOUNT_PRICE'] && $_price['BASE_PRICE'] > $_price['DISCOUNT_PRICE']) {
				$data['data'] .= 'price="' . $_price['DISCOUNT_PRICE'] . '" ';
				$data['data'] .= 'oldprice="' . $_price['BASE_PRICE'] . '" ';
			} else {
				$data['data'] .= 'price="' . $_price['BASE_PRICE'] . '" ';
			}
		} else {
			$data['data'] .= 'price="0" ';
		}
		$data['data'] .= '/>';
	}
	$data['data'] .= '</availability>';
}