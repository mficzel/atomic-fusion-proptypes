<?php
namespace PackageFactory\AtomicFusion\PropTypes\Validators;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Validation\Validator\AbstractValidator;
use Neos\Flow\Validation\Validator\ValidatorInterface;

/**
 * Validator for integers.
 */
class ArrayOfValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    /**
     * @var array
     */
    protected $supportedOptions = [
        'itemValidator' => array(null, 'All items of the array satify this validator', 'Validatop')
    ];

    /**
     * Checks if the given value is accepted.
     *
     * @param mixed $value The value that should be validated
     * @return void
     */
    protected function isValid($value)
    {
        if (is_null($value)) {
            return;
        }

        /**
         * @var ValidatorInterface $itemValidator
         */
        $itemValidator = $this->options['itemValidator'];
        if (is_array($value) || ($value instanceof \Traversable)) {
            foreach ($value as $key => $item) {
                $itemResult = $itemValidator->validate($item);
                if ($itemResult->hasErrors()) {
                    $this->addError('Item %s is not valid', 1515003545, [$key]);
                }
            }
        } else {
            $this->addError('Array-value is expected to be an array or implement \Traversable', 1515070099);
        }
    }
}
