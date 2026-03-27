<?php

/**
 * Подключать этот файл в начале всех файлов для API
 */

use \Bitrix\Main\ArgumentException,
	\Bitrix\Main\HttpResponse,
	\Bitrix\Main\Result,
	\Bitrix\Main\Error,
	\Bitrix\Main\HttpRequest,
	\Bitrix\Main\Web\Json,
	\Bitrix\Main\Web\Uri,
	\Bitrix\Main\Data\StaticHtmlCache,
	\App\Ctx,
	\App\Location\Router;

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

/* if(getenv('isbot') == 'true') {
	define('BX_SECURITY_SESSION_VIRTUAL', true); //TODO: не определяются bitrix шаблоны
} */

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

class ApiResult extends Result
{
	/**
	 * @var HttpResponse
	 */
	private $httpResponse;

	private $status = 200;

	/**
	 * @param HttpRequest $request
	 */
	public function __construct(HttpRequest $request)
	{
		parent::__construct();

		StaticHtmlCache::getInstance()->markNonCacheable();

		$this->httpResponse = new HttpResponse();

		$isAjaxRequest = (
			$request->isAjaxRequest() || $request->getHeader('X-Requested-With') == 'XMLHttpRequest'
		);

		$rawPostData = trim($request->getInput());
		if (strlen($rawPostData) > 0) {
			// Если это PUT PATCH DELETE, то данные могут передаваться в теле запроса в виде JSON или строки с параметрами
			$postData = json_decode($rawPostData, true);

			if (!is_array($postData)) {
				parse_str($rawPostData, $postData);
			}

			$request->set(array_merge($request->getQueryList()->toArray(), $postData));
		}

		$checkSessid = true;

		//На странице заказа sessid меняется при каждом запросе
		$noCheckReferers = [
			'/checkout/',
		];

		if ($_referer = new Uri(Ctx::server()->get('HTTP_REFERER'))) {
			$referer = $_referer->getScheme() . '://' . $_referer->getHost() . $_referer->getPath();
			$host = (Ctx::server()->getServerPort() == '443' ? 'https://' : 'http://') . Ctx::request()->getHttpHost();
			foreach ($noCheckReferers as $url) {
				if (rtrim($referer, '/') == rtrim($host . $url, '/')) {
					$checkSessid = false;
					break;
				}
			}
		}

		//Отключаем проверку только для публичных эндпоинтов (чтение)
		if ($checkSessid) {
			$noCheckUrls = [
				'/catalog/',
				'/api/1c/',
				'/api/basket/', //Только чтение корзины
				'/api/location/',
				'/api/modal/',
				'/api/news/',
				//'/api/order/', //Убрано: критичные операции требуют CSRF проверки
				'/api/product/',
				'/api/user/', //Только публичные данные пользователя
				'/api/webhook/',
				'/api/xml/',
				'/api/yml/',
			];

			$_url = Ctx::request()->getRequestUri();
			foreach ($noCheckUrls as $url) {
				if (stripos($_url, $url) === 0 && $_url !== '/api/modal/auth/') {
					$checkSessid = false;
					break;
				}
			}
		}

		if ($checkSessid && (!$isAjaxRequest || !\check_bitrix_sessid() || Router::getInstance()->isBot())) {
			$this->addError(new Error('Сессия истекла, пожалуйста, обновите страницу'));
			$this->setData(['sessid' => \bitrix_sessid()]);
			$this->status = 403;
		}
	}

	/**
	 * @return string
	 * @throws ArgumentException
	 */
	public function toJson()
	{
		if (!$this->isSuccess()) {
			return Json::encode([
				'errors' => $this->getErrorMessages(),
				'data' => $this->getData()
			], JSON_UNESCAPED_UNICODE);
		}

		return Json::encode($this->getData(), JSON_UNESCAPED_UNICODE);
	}

	/**
	 * @throws ArgumentException
	 */
	public function sendJsonResponse()
	{
		$this->httpResponse->addHeader('Content-Type', 'application/json');
		$this->httpResponse->setStatus($this->status);
		$this->httpResponse->flush($this->toJson());

		exit;
	}

	/**
	 * @throws ArgumentException
	 */
	public function sendEmptyResponse()
	{
		$this->httpResponse->addHeader('Content-Type', 'application/json');
		$this->httpResponse->setStatus($this->status);
		$this->httpResponse->flush();

		exit;
	}

	/**
	 * @return string
	 * @throws ArgumentException
	 */
	public function toXml()
	{
		if (!$this->isSuccess()) {
			return Json::encode([
				'errors' => $this->getErrorMessages(),
				'data' => $this->getData()
			], JSON_UNESCAPED_UNICODE);
		}

		$xml = '';
		$data = $this->getData();
		foreach (['header', 'data', 'footer'] as $type) {
			$xml .= $data[$type];
		}

		return $xml ?: Json::encode($this->getData(), JSON_UNESCAPED_UNICODE);
	}

	/**
	 * @throws ArgumentException
	 */
	public function sendXmlResponse()
	{
		$this->httpResponse->addHeader('Content-Type', 'text/xml; charset=utf-8');
		$this->httpResponse->setStatus($this->status);
		$this->httpResponse->flush($this->toXml());

		exit;
	}

	/**
	 * @param int $status
	 */
	public function setStatus($status)
	{
		$this->status = $status;
	}
}