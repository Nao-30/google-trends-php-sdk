<?php

namespace Gtrends\Sdk\Exceptions;

/**
 * Exception thrown for parameter validation errors.
 * 
 * This exception is used when there are validation errors with input parameters,
 * such as invalid data types, values out of range, etc.
 */
class ValidationException extends GtrendsException
{
    /**
     * The parameter name that failed validation.
     *
     * @var string|null
     */
    protected ?string $parameterName = null;

    /**
     * The invalid value that caused the validation error.
     *
     * @var mixed
     */
    protected mixed $invalidValue = null;

    /**
     * Description of the expected value or constraint.
     *
     * @var string|null
     */
    protected ?string $expectedConstraint = null;

    /**
     * Validation errors when multiple parameters fail validation.
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $validationErrors = [];

    /**
     * Create a new validation exception instance.
     *
     * @param string $message The exception message
     * @param int $code The exception code
     * @param \Throwable|null $previous The previous throwable used for exception chaining
     * @param array<string, mixed> $context Additional context information
     * @param string|null $parameterName The parameter name that failed validation
     * @param mixed $invalidValue The invalid value
     * @param string|null $expectedConstraint Description of the expected constraint
     * @param array<string, array<string, mixed>> $validationErrors Multiple validation errors
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        array $context = [],
        ?string $parameterName = null,
        mixed $invalidValue = null,
        ?string $expectedConstraint = null,
        array $validationErrors = []
    ) {
        parent::__construct($message, $code, $previous, $context);
        
        $this->parameterName = $parameterName;
        $this->invalidValue = $invalidValue;
        $this->expectedConstraint = $expectedConstraint;
        $this->validationErrors = $validationErrors;
        
        // Add validation information to context
        if ($parameterName !== null) {
            $this->addContext('parameter_name', $parameterName);
        }
        
        if ($invalidValue !== null) {
            // Use var_export for a string representation of any value
            $this->addContext('invalid_value', var_export($invalidValue, true));
        }
        
        if ($expectedConstraint !== null) {
            $this->addContext('expected_constraint', $expectedConstraint);
        }
        
        if (!empty($validationErrors)) {
            $this->addContext('validation_errors', $validationErrors);
        }
    }

    /**
     * Get the parameter name that failed validation.
     *
     * @return string|null
     */
    public function getParameterName(): ?string
    {
        return $this->parameterName;
    }

    /**
     * Get the invalid value that caused the validation error.
     *
     * @return mixed
     */
    public function getInvalidValue(): mixed
    {
        return $this->invalidValue;
    }

    /**
     * Get the expected constraint description.
     *
     * @return string|null
     */
    public function getExpectedConstraint(): ?string
    {
        return $this->expectedConstraint;
    }

    /**
     * Get all validation errors.
     *
     * @return array<string, array<string, mixed>>
     */
    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    /**
     * Add a validation error for a specific parameter.
     *
     * @param string $parameterName The parameter name
     * @param mixed $invalidValue The invalid value
     * @param string $constraint The constraint that was violated
     * @return self
     */
    public function addValidationError(string $parameterName, mixed $invalidValue, string $constraint): self
    {
        $this->validationErrors[$parameterName] = [
            'value' => var_export($invalidValue, true),
            'constraint' => $constraint,
        ];
        
        return $this;
    }
} 