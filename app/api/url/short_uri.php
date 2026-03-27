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

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use \Bitrix\Main\HttpResponse,
	\App\Ctx;

if (($code = Ctx::request()->get('code')) && $ar = \CBXShortUri::GetUri($code)) {
	\CBXShortUri::SetLastUsed($ar['ID']);
	\LocalRedirect($ar['URI'], true, \CBXShortUri::GetHttpStatusCodeText($ar['STATUS']));
} else {
	$httpResponse = new HttpResponse();
	$httpResponse->setStatus(404);
	$httpResponse->flush();
}