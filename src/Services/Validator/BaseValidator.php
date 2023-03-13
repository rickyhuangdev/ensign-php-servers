<?php
/**
 * Create by Ricky Huang
 * E-mail: ricky_huang_hkg@ensignfreight.com
 * Description: BaseValidator
 * Date: 2023-03-13 09:33
 * Update: 2023-03-13 09:33
 */

namespace Rickytech\Library\Services\Validator;

use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;

abstract class BaseValidator
{
    protected static $dtoClass;

    public static function validate(array $data)
    {
        $validator = self::getValidatorFactory()->make(
            $data,
            static::rules(),
            static::messages(),
            static::customAttributes()
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $dtoClass = static::dtoClass();
        return new $dtoClass($validator->validated());
    }

    protected static function getValidatorFactory(): ValidatorFactoryInterface
    {
        return make(ValidatorFactoryInterface::class);
    }

    abstract protected static function rules(): array;

    protected static function messages(): array
    {
        return [];
    }

    protected static function customAttributes(): array
    {
        return [];
    }

    protected static function dtoClass(): string
    {
        return static::$dtoClass;
    }
}
