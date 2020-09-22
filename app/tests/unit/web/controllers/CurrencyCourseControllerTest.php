<?php

namespace tests\unit\web\controllers;

use entities\DailyCourseResponse;
use exceptions\DailyCourseRepositoryException;
use exceptions\InvalidDailyCourseRequestException;
use repositories\DailyCourseRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use web\controllers\CurrencyCourseController;
use PHPUnit\Framework\TestCase;

class CurrencyCourseControllerTest extends TestCase
{
    public function testActionDailyCourseWithEmptyDate()
    {
        $repo = $this->createMock(DailyCourseRepositoryInterface::class);
        $request = $this->createMock(Request::class);
        $request->attributes = $this->createMock(ParameterBag::class);
        $request->method("get")->willReturnOnConsecutiveCalls("", "");

        $this->expectExceptionObject(
            new BadRequestHttpException("Нужно предать дату в параметре date в формате d.m.Y")
        );

        $controller = new CurrencyCourseController($repo);
        $controller->actionDailyCourse($request);
    }

    public function testActionDailyCourseWithBadDate()
    {
        $repo = $this->createMock(DailyCourseRepositoryInterface::class);
        $request = $this->createMock(Request::class);
        $request->attributes = $this->createMock(ParameterBag::class);
        $request->method("get")->willReturnOnConsecutiveCalls("12-10-2020", "");

        $this->expectExceptionObject(
            new BadRequestHttpException("Нужно предать дату в параметре date в формате d.m.Y")
        );

        $controller = new CurrencyCourseController($repo);
        $controller->actionDailyCourse($request);
    }

    public function testActionDailyCourseWithInvalidDailyCourseException()
    {
        $repo = $this->createMock(DailyCourseRepositoryInterface::class);
        $request = $this->createMock(Request::class);
        $request->attributes = $this->createMock(ParameterBag::class);
        $request->attributes->method("get")->willReturn("currencyID");
        $request->method("get")->willReturnOnConsecutiveCalls("12.10.2020", "");

        $repo->method("getDailyCourse")->willThrowException(
            new InvalidDailyCourseRequestException("Все плохо")
        );

        $this->expectExceptionObject(
            new BadRequestHttpException("Все плохо")
        );

        $controller = new CurrencyCourseController($repo);
        $controller->actionDailyCourse($request);
    }

    public function testActionDailyCourseWithDailyCourseRepositoryException()
    {
        $repo = $this->createMock(DailyCourseRepositoryInterface::class);
        $request = $this->createMock(Request::class);
        $request->attributes = $this->createMock(ParameterBag::class);
        $request->attributes->method("get")->willReturn("currencyID");
        $request->method("get")->willReturnOnConsecutiveCalls("12.10.2020", "");

        $repo->method("getDailyCourse")->willThrowException(
            new DailyCourseRepositoryException("Все плохо")
        );

        $this->expectExceptionObject(
            new HttpException(500,"Все плохо")
        );

        $controller = new CurrencyCourseController($repo);
        $controller->actionDailyCourse($request);
    }

    public function testActionDailyCourseWithGoodResult()
    {
        $repo = $this->createMock(DailyCourseRepositoryInterface::class);
        $request = $this->createMock(Request::class);
        $request->attributes = $this->createMock(ParameterBag::class);
        $request->attributes->method("get")->willReturn("currencyID");
        $request->method("get")->willReturnOnConsecutiveCalls("12.10.2020", "");

        $repo->method("getDailyCourse")->willReturn(
            $this->createConfiguredMock(DailyCourseResponse::class, [
                "getValue" => 10.1,
                "getPreviousDayDifference" => 404.5
            ])
        );

        $expResponse = new JsonResponse([
            "value" => 10.1,
            "difference" => 404.5,
        ]);

        $controller = new CurrencyCourseController($repo);
        $response = $controller->actionDailyCourse($request);

        $this->assertEquals($expResponse, $response);
    }
}
