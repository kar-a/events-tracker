<?php

use \Bitrix\Iblock\Iblock;

$data = [];

if ($class = Iblock::wakeUp(APP_IBLOCK_CATALOG)->getEntityDataClass()) {
	$sizes = [];
	$rs = $class::getList([
		'order' => ['XML_ID' => 'asc'],
		'filter' => ['=IBLOCK_ID' => APP_IBLOCK_CATALOG],
		'select' => ['ID', 'XML_ID', 'ACTIVE', 'PROPERTY_SIZE_' => 'SIZE'],
	]);
	while ($el = $rs->fetch()) {
		if ($size = $el['PROPERTY_SIZE_VALUE']) $sizes[$size][] = $el['XML_ID'];

		$data[$el['XML_ID']] = [
			'id' => $el['ID'],
			'code' => $el['XML_ID'],
			'size' => '',
			'active' => ($el['ACTIVE'] == 'Y')
		];
	}

	if ($data && $sizes) {
		$rs = \SizeTable::getList(['filter' => ['=UF_XML_ID' => array_keys($sizes)], 'select' => ['UF_XML_ID', 'UF_EXTERNAL_CODE']]);
		while ($row = $rs->fetch()) {
			if ($sizes[$row['UF_XML_ID']]) {
				foreach ($sizes[$row['UF_XML_ID']] as $elementXmlId) {
					$data[$elementXmlId]['size'] = $row['UF_EXTERNAL_CODE'];
				}
			}
		}
	}
}

$data = array_values($data);
