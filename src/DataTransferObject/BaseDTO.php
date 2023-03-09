<?php
/**
 * Create by Ricky Huang
 * E-mail: ricky_huang_hkg@ensignfreight.com
 * Description: BaseDto
 * Date: 2023-03-09 16:08
 * Update: 2023-03-09 16:08
 */

namespace Rickytech\Library\DataTransferObject;

use Hyperf\Context\Context;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\ValidationException;

class BaseDTO
{
    /**
     * @var ValidatorFactoryInterface
     */
    protected static $validatorFactory;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected static $rules = [];

    /**
     * @var array
     */
    protected static $messages = [];

    /**
     * @var array
     */
    protected static $attributes = [];

    /**
     * @var array
     */
    protected static $defaults = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Set the validator factory instance.
     */
    public static function setValidatorFactory(ValidatorFactoryInterface $validatorFactory): void
    {
        static::$validatorFactory = $validatorFactory;
    }

    /**
     * Get the validator factory instance.
     */
    public static function getValidatorFactory(): ValidatorFactoryInterface
    {
        return make(ValidatorFactoryInterface::class);
    }

    /**
     * Get the validation rules for the DTO.
     */
    public static function rules(): array
    {
        return static::$rules;
    }

    /**
     * Get the validation messages for the DTO.
     */
    public static function messages(): array
    {
        return static::$messages;
    }

    /**
     * Get the validation attributes for the DTO.
     */
    public static function attributes(): array
    {
        return static::$attributes;
    }

    /**
     * Get the default values for the DTO.
     */
    public static function defaults(): array
    {
        return static::$defaults;
    }

    /**
     * Validate the DTO data.
     *
     * @throws ValidationException
     */
    public function validate(): void
    {
        $validator = static::getValidatorFactory()->make(
            $this->data,
            static::rules(),
            static::messages(),
            static::attributes()
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Create a new DTO instance from the given data.
     */
    public static function create(array $data = []): static
    {
        $defaults = static::defaults();

        $data = array_merge($defaults, $data);
        $instance = new static($data);
        $instance->validate();
        foreach ($data as $key => $value) {
            $instance->$key = $value;
        }
        return $instance;
    }

    /**
     * Get the data for the DTO.
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Get a value from the DTO data.
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Set a value in the DTO data.
     */
    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Determine if the DTO data has a value for the given key.
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Get the current DTO instance from the context.
     */
    public static function current(): ?self
    {
        return Context::get(static::class);
    }

    /**
     * Set the current DTO instance in the context.
     */
    public function setCurrent(): void
    {
        Context::set(static::class, $this);
    }
}
