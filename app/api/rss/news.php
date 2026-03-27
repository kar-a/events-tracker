<?php

$APPLICATION->IncludeComponent(
	'bitrix:rss.out',
	'main',
	[
		'IBLOCK_ID' => APP_IBLOCK_NEWS,
		'IBLOCK_TYPE' => APP_IBLOCK_TYPE_CONTENT,
		'CACHE_FILTER' => 'N',
		'CACHE_GROUPS' => 'N',
		'CACHE_TIME' => APP_CACHE_H,
		'CACHE_TYPE' => 'A',
		'NUM_DAYS' => '365',
		'NUM_NEWS' => '20',
		'RSS_TTL' => '60',
		'SORT_BY1' => 'ACTIVE_FROM',
		'SORT_BY2' => 'SORT',
		'SORT_ORDER1' => 'DESC',
		'SORT_ORDER2' => 'ASC',
		'YANDEX' => 'Y'
	]
);

