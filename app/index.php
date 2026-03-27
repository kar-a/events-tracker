<? require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

use \App\Template\Helper as TemplateHelper;

$APPLICATION->IncludeComponent(
	'app:banners',
	'slider',
	[
		'IBLOCK_TYPE' => APP_IBLOCK_TYPE_CONTENT,
		'IBLOCK_ID' => APP_IBLOCK_BANNERS,
		'CACHE_TYPE' => 'A',
		'CACHE_TIME' => APP_CACHE_W,
		'COUNT' => 5,
		'SECTION_CODE' => 'MAIN_SLIDER',
		'FILTER' => [],
		'SORT' => ['SORT' => 'ASC']
	]
); ?>
	<div class="container">
		<? //Хиты продаж
		global $arrBestsellersFilter;
		$arrBestsellersFilter = ['!PROPERTY_BEST_SELLER' => false];
		$APPLICATION->IncludeComponent(
			'app:catalog.section',
			'main',
			[
				'IBLOCK_ID' => APP_IBLOCK_CATALOG,
				'IBLOCK_TYPE' => APP_IBLOCK_CATALOG,
				'SECTION_ID' => '',
				'SECTION_CODE' => '',
				'FILTER_NAME' => 'arrBestsellersFilter',
				'INCLUDE_SUBSECTIONS' => 'N',
				'SHOW_ALL_WO_SECTION' => 'Y',
				'PAGE_ELEMENT_COUNT' => '20',
				'ELEMENT_SORT_FIELD' => 'SORT',
				'ELEMENT_SORT_ORDER' => 'ASC',
				'PROPERTY_CODE' => [],
				'BASKET_URL' => TemplateHelper::PAGE_URL_BASKET,
				'ACTION_VARIABLE' => 'action',
				'PRODUCT_ID_VARIABLE' => 'id',
				'PRODUCT_QUANTITY_VARIABLE' => 'quantity',
				'PRODUCT_PROPS_VARIABLE' => 'prop',
				'SECTION_ID_VARIABLE' => 'SECTION_ID',
				'CACHE_TYPE' => 'A',
				'CACHE_TIME' => APP_CACHE_M * 15,
				'CACHE_GROUPS' => 'N',
				'ADD_SECTIONS_CHAIN' => 'N',
				'SET_TITLE' => 'N',
				'SET_STATUS_404' => 'N',
				'CACHE_FILTER' => 'Y',
				'PRICE_CODE' => [APP_PRICE_CODE],
				'PRICE_VAT_INCLUDE' => 'Y',
				'CONVERT_CURRENCY' => 'N',
				'CURRENCY_ID' => APP_CURRENCY,
				'DISPLAY_BOTTOM_PAGER' => 'N',
				'PAGER_TEMPLATE' => '',
				'PAGER_SHOW_ALL' => 'N',
				'AJAX_OPTION_ADDITIONAL' => '',
				'ADD_CHAIN_ITEM' => 'N',
				'COMPATIBLE_MODE' => 'Y',
				'SKIP_DATES_CHECK' => 'Y',
				'SKIP_PERMISSIONS_CHECK' => 'Y',
				'TITLE' => 'Хиты продаж',
				'TITLE_MOBILE' => 'Хиты продаж',
				'TITLE_DESCRIPTION' => 'Выбор покупателей',
				'TITLE_CLASS' => 'section__title',
				'TITLE_DESCRIPTION_CLASS' => 'section__kicker text-center',
				'INCLUDE_PADDING' => 'Y',
				'IS_SLIDER' => 'Y',
				'GALLERY_TYPE' => 'SWAP',
				'SHOW_NAV' => 'Y',
				'LIST_CODE' => 'hit',
				'HIDE_LABELS' => 'Y',
			],
			false
		);

	//Новинки
	global $arrNewArrivalFilter;
	$arrNewArrivalFilter = ['!PROPERTY_NEW_ARRIVAL' => false];
	$APPLICATION->IncludeComponent(
		'app:catalog.section',
		'main',
		[
			'IBLOCK_ID' => APP_IBLOCK_CATALOG,
			'IBLOCK_TYPE' => APP_IBLOCK_CATALOG,
			'SECTION_ID' => '',
			'SECTION_CODE' => '',
			'FILTER_NAME' => 'arrNewArrivalFilter',
			'INCLUDE_SUBSECTIONS' => 'N',
			'SHOW_ALL_WO_SECTION' => 'Y',
			'PAGE_ELEMENT_COUNT' => '20',
			'ELEMENT_SORT_FIELD' => 'SORT',
			'ELEMENT_SORT_ORDER' => 'ASC',
			'PROPERTY_CODE' => [],
			'BASKET_URL' => TemplateHelper::PAGE_URL_BASKET,
			'ACTION_VARIABLE' => 'action',
			'PRODUCT_ID_VARIABLE' => 'id',
			'PRODUCT_QUANTITY_VARIABLE' => 'quantity',
			'PRODUCT_PROPS_VARIABLE' => 'prop',
			'SECTION_ID_VARIABLE' => 'SECTION_ID',
			'CACHE_TYPE' => 'A',
			'CACHE_TIME' => APP_CACHE_M * 15,
			'CACHE_GROUPS' => 'N',
			'ADD_SECTIONS_CHAIN' => 'N',
			'SET_TITLE' => 'N',
			'SET_STATUS_404' => 'N',
			'CACHE_FILTER' => 'Y',
			'PRICE_CODE' => [APP_PRICE_CODE],
			'PRICE_VAT_INCLUDE' => 'Y',
			'CONVERT_CURRENCY' => 'N',
			'CURRENCY_ID' => APP_CURRENCY,
			'DISPLAY_BOTTOM_PAGER' => 'N',
			'PAGER_TEMPLATE' => '',
			'PAGER_SHOW_ALL' => 'N',
			'AJAX_OPTION_ADDITIONAL' => '',
			'ADD_CHAIN_ITEM' => 'N',
			'COMPATIBLE_MODE' => 'Y',
			'SKIP_DATES_CHECK' => 'Y',
			'SKIP_PERMISSIONS_CHECK' => 'Y',
			'TITLE' => 'Новинки каталога',
			'TITLE_MOBILE' => 'Новинки каталога',
			'TITLE_DESCRIPTION' => 'Новые поступления',
			'TITLE_CLASS' => 'section__title',
			'TITLE_DESCRIPTION_CLASS' => 'section__kicker text-center',
			'ALL_LINK' => TemplateHelper::PAGE_URL_CATALOG_BASE,
			'USE_PARALAX' => 'Y',
			'INCLUDE_PADDING' => 'Y',
			'IS_SLIDER' => 'Y',
			'GALLERY_TYPE' => 'SWAP',
			'SLIDER_TYPE' => 'BIG_CENTERED',
			'SHOW_NAV' => 'Y',
			'HIDE_LABELS' => 'Y',
			'PARALLAX_IMAGE' => SITE_TEMPLATE_PATH . '/bundle/img/parallax/ring-1.png',
			'PARALLAX_CLASS' => 'top-sales__ring',
		],
		false
	);

	//Эксклюзив
	global $arrExclusiveFilter;
	$arrExclusiveFilter = ['!PROPERTY_EXCLUSIVE' => false];
	$APPLICATION->IncludeComponent(
		'app:catalog.section',
		'main',
		[
			'IBLOCK_ID' => APP_IBLOCK_CATALOG,
			'IBLOCK_TYPE' => APP_IBLOCK_CATALOG,
			'SECTION_ID' => '',
			'SECTION_CODE' => '',
			'FILTER_NAME' => 'arrExclusiveFilter',
			'INCLUDE_SUBSECTIONS' => 'N',
			'SHOW_ALL_WO_SECTION' => 'Y',
			'PAGE_ELEMENT_COUNT' => '20',
			'ELEMENT_SORT_FIELD' => 'SORT',
			'ELEMENT_SORT_ORDER' => 'ASC',
			'PROPERTY_CODE' => [],
			'BASKET_URL' => TemplateHelper::PAGE_URL_BASKET,
			'ACTION_VARIABLE' => 'action',
			'PRODUCT_ID_VARIABLE' => 'id',
			'PRODUCT_QUANTITY_VARIABLE' => 'quantity',
			'PRODUCT_PROPS_VARIABLE' => 'prop',
			'SECTION_ID_VARIABLE' => 'SECTION_ID',
			'CACHE_TYPE' => 'A',
			'CACHE_TIME' => APP_CACHE_M * 15,
			'CACHE_GROUPS' => 'N',
			'ADD_SECTIONS_CHAIN' => 'N',
			'SET_TITLE' => 'N',
			'SET_STATUS_404' => 'N',
			'CACHE_FILTER' => 'Y',
			'PRICE_CODE' => [APP_PRICE_CODE],
			'PRICE_VAT_INCLUDE' => 'Y',
			'CONVERT_CURRENCY' => 'N',
			'CURRENCY_ID' => APP_CURRENCY,
			'DISPLAY_BOTTOM_PAGER' => 'N',
			'PAGER_TEMPLATE' => '',
			'PAGER_SHOW_ALL' => 'N',
			'AJAX_OPTION_ADDITIONAL' => '',
			'ADD_CHAIN_ITEM' => 'N',
			'COMPATIBLE_MODE' => 'Y',
			'SKIP_DATES_CHECK' => 'Y',
			'SKIP_PERMISSIONS_CHECK' => 'Y',
			'TITLE' => 'Эксклюзив',
			'TITLE_MOBILE' => 'Эксклюзив',
			'TITLE_DESCRIPTION' => 'Уникальные изделия',
			'TITLE_CLASS' => 'section__title',
			'TITLE_DESCRIPTION_CLASS' => 'section__kicker text-center',
			'ALL_LINK' => SITE_DIR . 'catalog/chasy/',
			'USE_PARALAX' => 'N',
			'INCLUDE_PADDING' => 'Y',
			'IS_SLIDER' => 'Y',
			'GALLERY_TYPE' => 'SWAP',
			'SLIDER_TYPE' => 'BIG_CENTERED',
			'SHOW_NAV' => 'Y',
			'HIDE_LABELS' => 'Y',
		],
		false
	); ?>
	<div class="section">
		<div class="services">
			<svg class="services__icon" data-parallax="true">
				<use href="#trimiata.svg"></use>
			</svg>
			<img class="services__ring" data-parallax="true" src="/images/images/parallax/ring-2.png" alt="">
			<div class="services__container">
				<h4 class="section__title">НАШИ УСЛУГИ</h4>
				<? $APPLICATION->IncludeComponent(
					'bitrix:news.list',
					'services',
					[
						'IBLOCK_TYPE' => 'news',
						'IBLOCK_ID' => APP_IBLOCK_SERVICES,
						'CACHE_TYPE' => 'A',
						'CACHE_TIME' => APP_CACHE_D,
						'CACHE_FILTER' => 'Y',
						'CACHE_GROUPS' => 'N',
						'PROPERTY_CODE' => ['LINK', 'ICON'],
						'SET_TITLE' => 'N',
						'SET_BROWSER_TITLE' => 'N',
						'SET_META_KEYWORDS' => 'N',
						'SET_META_DESCRIPTION' => 'N',
						'SET_LAST_MODIFIED' => 'N',
						'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
						'SORT_BY1' => 'SORT',
						'SORT_ORDER1' => 'ASC',
						'SORT_BY2' => 'ID',
						'SORT_ORDER2' => 'DESC',
					],
					false
				); ?>
			</div>
		</div>
	</div>
<? $APPLICATION->IncludeFile(SITE_TEMPLATE_PATH . '/include_areas/banners/news.php', [], ['MODE' => 'html']);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');