<?php

use \Bitrix\Main\Error,
	\App\Ctx,
	\App\User\Helper as UserHelper;

$result = new ApiResult(Ctx::request());

$requestParams = unserialize(urldecode(Ctx::request()->get('params')));
$userId = $requestParams['userId'];

$data = [];

if (Ctx::request()->isAjaxRequest() && Ctx::request()->isPost() && \check_bitrix_sessid() && $userId) {

	if (UserHelper::getUserId() != $userId) { //Менять ClientID можем только себе
		$result->addError(new Error('Пользователь не найден.'));
	} else {

		ob_start();
		if ($image = UserHelper::getLoyaltyQrImage()) { ?>
			<div class="modal modal_loyalty_qr fade" tabindex="-1">
				<div class="modal-dialog modal-dialog-centered">
					<div class="modal-content" data-role="modal-content">
						<img src="data:image/png;base64,<?= base64_encode($image) ?>" alt="QR"/>
					</div>
				</div>
			</div>
		<? }
		$html = ob_get_clean();

		$data = [
			'html' => $html,
		];
	}
}

if (!$result->isSuccess()) {
	$data['success'] = false;
	$data['errors'] = $result->getErrorMessages();
}
$result->setData($data);

/** @noinspection PhpUnhandledExceptionInspection */
$result->sendJsonResponse();