<?php

use \App\Ctx,
	\App\Exchange;

$method = strtoupper(Ctx::request()->getRequestMethod());

$data = [];
switch ($method) {
	case 'GET':
		Exchange::addToStackExternalRequest(Exchange::GET_IMPORT_CHANGES_METHOD);
		$data = ['success' => true];
		break;
	default:
		break;
}