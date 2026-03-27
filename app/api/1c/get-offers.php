<?php

use \Bitrix\Main\ORM\Fields\Relations\Reference,
	\Bitrix\Main\ORM\Query\Join,
	\Bitrix\Catalog\PriceTable,
	\Bitrix\Iblock\Iblock;

$data = [];

if ($class = Iblock::wakeUp(APP_IBLOCK_CATALOG_SKU)->getEntityDataClass()) {
	$elements = [];
	$rs = $class::getList([
		'order' => ['XML_ID' => 'asc'],
		'filter' => ['=IBLOCK_ID' => APP_IBLOCK_CATALOG_SKU],
		'runtime' => [new Reference('PRICE', PriceTable::class, Join::on('this.ID', 'ref.PRODUCT_ID')->where('ref.CATALOG_GROUP_ID', APP_PRICE_ID))],
		'select' => ['ID', 'XML_ID', 'PROPERTY_CML2_LINK_' => 'CML2_LINK', 'PRICE'],
	]);
	while ($el = $rs->fetch()) {
		if (!$elementId = $el['PROPERTY_CML2_LINK_VALUE']) continue;
		$elements[$elementId][] = $el['XML_ID'];

		$data[$el['XML_ID']] = [
			'guid' => $el['XML_ID'],
			'id' => $el['ID'],
			'price' => floatval($el['IBLOCK_ELEMENTS_ELEMENT_OFFERS_PRICE_PRICE'])
		];
	}
}

if ($data && $elements && $class = Iblock::wakeUp(APP_IBLOCK_CATALOG)->getEntityDataClass()) {
	$rs = $class::getList([
		'filter' => ['=IBLOCK_ID' => APP_IBLOCK_CATALOG, '=ID' => array_keys($elements)],
		'select' => ['ID', 'XML_ID'],
	]);
	while ($el = $rs->fetch()) {
		if ($elements[$el['ID']]) {
			foreach ($elements[$el['ID']] as $offerXmlId) {
				$data[$offerXmlId]['nim'] = $el['XML_ID'];
			}
		}
	}
}

$data = array_values($data);
