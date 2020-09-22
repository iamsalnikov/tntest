<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use web\controllers\CurrencyController;
use web\controllers\CurrencyCourseController;

require_once __DIR__.'/../../vendor/autoload.php';

(new Dotenv())->usePutenv(true)->load(__DIR__ . "/../../.env");

$cb = require __DIR__.'/../../config/di/container.php';

$app = new DI\Bridge\Silex\Application($cb);

$app->get('/currencies', [CurrencyController::class, "actionCurrenciesList"]);
$app->get('/currencies/{currencyID}/courses', [CurrencyCourseController::class, "actionDailyCourse"]);

$app->error(function (\Exception $e) {
    if ($e instanceof HttpExceptionInterface) {
        return new JsonResponse([
            "message" => $e->getMessage(),
        ], $e->getStatusCode());
    }

    return new JsonResponse([
        "message" => $e->getMessage()
    ], 500);
});

$app->run();
