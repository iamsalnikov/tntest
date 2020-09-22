<?php

namespace web\controllers;

use exceptions\DailyCourseRepositoryException;
use exceptions\InvalidDailyCourseRequestException;
use repositories\DailyCourseRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class CurrencyCourseController
 *
 * @package web\controllers
 */
class CurrencyCourseController
{
    private DailyCourseRepositoryInterface $dailyCourseRepo;

    /**
     * CurrencyCourseController constructor.
     *
     * @param DailyCourseRepositoryInterface $dailyCourseRepo
     */
    public function __construct(DailyCourseRepositoryInterface $dailyCourseRepo)
    {
        $this->dailyCourseRepo = $dailyCourseRepo;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function actionDailyCourse(Request $request): JsonResponse
    {
        $currencyID = $request->attributes->get("currencyID");
        $date = (string) $request->get("date");
        if (!$date) {
            throw new BadRequestHttpException("Нужно предать дату в параметре date в формате d.m.Y");
        }

        $parsedDate = \DateTime::createFromFormat("d.m.Y", $date);
        if (!$parsedDate instanceof \DateTime) {
            throw new BadRequestHttpException("Нужно предать дату в параметре date в формате d.m.Y");
        }

        $baseCurrencyID = $request->get("base");

        try {
            $data = $this->dailyCourseRepo->getDailyCourse($parsedDate, $currencyID, $baseCurrencyID);
        } catch (InvalidDailyCourseRequestException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        } catch (DailyCourseRepositoryException $e) {
            throw new HttpException(500, $e->getMessage(), $e);
        }

        return new JsonResponse([
            "value" => $data->getValue(),
            "difference" => $data->getPreviousDayDifference(),
        ]);
    }
}
