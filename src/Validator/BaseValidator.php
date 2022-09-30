<?php

namespace Rickytech\Library\Validator;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Rickytech\Library\Exceptions\FormRequestException;


class BaseValidator
{
    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    protected $validator;

    protected function formRequestError()
    {
        throw new FormRequestException($this->validator->errors()->first(), 422);
    }
}
