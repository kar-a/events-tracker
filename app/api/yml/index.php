<?php
//Отключаем лишние действия
define('BX_SENDPULL_COUNTER_QUEUE_DISABLE', true);
define('BX_SECURITY_SESSION_READONLY', true);
define('DisableEventsCheck', true);
define('NO_AGENT_CHECK', true);
define('NO_AGENT_STATISTIC', true);
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('SKIP_LOCATION_REDIRECT', true);
define('STOP_STATISTICS', true);

// Запуск из консоли
if (empty($_SERVER['DOCUMENT_ROOT'])) {
	$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/../..');
	$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'];
}

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use \Bitrix\Main\HttpResponse,
	\App\Ctx,
	\App\Export\Base;

$httpResponse = new HttpResponse();
$httpResponse->addHeader('Content-Type', 'text/xml; charset=utf-8');

if (!$profile = Base::yml()->getProfile(Ctx::request()->get('profile'))) {
	$httpResponse->setStatus(404);
	$httpResponse->flush();
} else {
	if ($xml = Base::yml()->getXml($profile)) {
		$httpResponse->flush($xml);
	} else {
		$httpResponse->setStatus(500);
		$httpResponse->flush();
	}
}

