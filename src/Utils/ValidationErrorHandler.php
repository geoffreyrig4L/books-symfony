<?php

namespace App\Utils;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ValidationErrorHandler
{
    public static function handle(ConstraintViolationListInterface $errors): void
    {
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            throw new HttpException(Response::HTTP_BAD_REQUEST, implode(' | ', $errorMessages));
        }
    }
}