<?php
namespace PackageFactory\AtomicFusion\PropTypes\Validators;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Validation\Validator\AbstractValidator;

/**
 * Validator for integers.
 */
class IntegerValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    /**
     * Checks if the given value is a valid integer.
     *
     * @param mixed $value The value that should be validated
     * @return void
     */
    protected function isValid($value)
    {
        if (is_null($value) || is_int($value)) {
            return;
        }
        $this->addError('A valid float is expected.', 1514998717);
    }
}
