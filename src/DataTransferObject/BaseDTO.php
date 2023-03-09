<?php
/**
 * Create by Ricky Huang
 * E-mail: ricky_huang_hkg@ensignfreight.com
 * Description: BaseDto
 * Date: 2023-03-09 16:08
 * Update: 2023-03-09 16:08
 */

namespace Rickytech\Library\DataTransferObject;

use Hyperf\Utils\Arr;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;

class BaseDTO
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @var array
     */
    protected $customAttributes = [];

    /**
     * @var ValidatorFactoryInterface
     */
    protected $validatorFactory;

    public function __construct(array $data, ValidatorFactoryInterface $validatorFactory)
    {
        $this->data = $data;
        $this->validatorFactory = $validatorFactory;
    }

    /**
     * Validate the data and return the DTO object.
     *
     * @throws ValidationException
     */
    public static function make(array $data): static
    {
        $instance = new static($data, make(ValidatorFactoryInterface::class));
        $instance->validate();
        foreach ($data as $key => $value) {
            $instance->$key = $value;
        }
        $instance->transform();
        return $instance;
    }

    /**
     * Validate the data.
     *
     * @throws ValidationException
     */
    protected function validate(): void
    {
        $validator = $this->validatorFactory->make(
            $this->data,
            $this->rules,
            $this->messages,
            $this->customAttributes
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Transform the data.
     */
    protected function transform(): void
    {
        $this->data = Arr::only($this->data, array_keys($this->rules));
    }

    /**
     * Convert the DTO object to an array.
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
