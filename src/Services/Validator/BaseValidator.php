<?php
/**
 * Create by Ricky Huang
 * E-mail: ricky_huang_hkg@ensignfreight.com
 * Description: BaseValidator
 * Date: 2023-03-13 09:33
 * Update: 2023-03-13 09:33
 */

namespace Rickytech\Library\Services\Validator;

use Hyperf\Validation\ValidationException;
use Hyperf\Validation\ValidatorFactory;

abstract class BaseValidator
{
    protected static $dtoClass;

    public static function validate(array $data)
    {
        $validator = self::getValidatorFactory()->make(
            $data,
            self::rules(),
            static::messages(),
            static::customAttributes()
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $dtoClass = static::dtoClass();
        return new $dtoClass($validator->validated());
    }

    protected static function getValidatorFactory(): ValidatorFactory
    {
        return make(ValidatorFactory::class);
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
