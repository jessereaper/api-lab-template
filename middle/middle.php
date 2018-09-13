<?php
$app = new \Slim\App();

$app->add(function ($request, $response, $next) {
	$response->getBody()->write('BEFORE');
	$response = $next($request, $response);
	$response->getBody()->write('AFTER');

	return $response;
});

$app->get('/hello', function ($request, $response, $args) {
	$response->getBody()->write(' Da World ');

	return $response;
});

$app->run();
