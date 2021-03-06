<?php
namespace PackageFactory\AtomicFusion\PropTypes\Aspects;

use Neos\Error\Messages\Error;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Fusion\Exception as FusionException;
use Neos\Error\Messages\Result;
use Neos\Flow\Validation\Validator\ValidatorInterface;
use Neos\Utility\ObjectAccess;

/**
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class PropTypeValidationAspect
{
    /**
     * @Flow\Around("setting(PackageFactory.AtomicFusion.PropTypes.enable) && method(Neos\Fusion\FusionObjects\ComponentImplementation->evaluate())")
     * @param JoinPointInterface $joinPoint The current join point
     * @return mixed
     */
    public function validatePropTypesForFusionComponents(JoinPointInterface $joinPoint)
    {
        return $this->validateFusionPropTypes($joinPoint);
    }

    /**
     * @Flow\Around("setting(PackageFactory.AtomicFusion.PropTypes.enable) && method(PackageFactory\AtomicFusion\FusionObjects\ComponentImplementation->evaluate())")
     * @param JoinPointInterface $joinPoint The current join point
     * @return mixed
     */
    public function validatePropTypesForPackageFactoryAtomicFusionComponents(JoinPointInterface $joinPoint)
    {
        return $this->validateFusionPropTypes($joinPoint);
    }

    /**
     * @param JoinPointInterface $joinPoint
     * @return mixed
     */
    protected function validateFusionPropTypes(JoinPointInterface $joinPoint)
    {
        $fusionComponentImplementation = $joinPoint->getProxy();
        $validators = $fusionComponentImplementation['__meta/propTypes<Neos.Fusion:RawArray>'];
        $result  = new Result();
        foreach ($validators as $propName => $validator) {
            if ($validator instanceof ValidatorInterface) {
                $result->forProperty($propName)->merge($validator->validate($fusionComponentImplementation[$propName]));
            } else {
                $result->forProperty($propName)->addError(
                    new Error(
                        sprintf(
                            '@propTypes are expected implement the ValidatorInterface %s found instead',
                            (is_object($validator) ? get_class($validator) : gettype($validator))
                        )
                    )
                );
            }
        }

        if ($result->hasErrors()) {
            $message = '';
            $flattenedErrors = $result->getFlattenedErrors();
            foreach ($flattenedErrors as $path => $errors) {
                $message .= sprintf('%s: %s', $path, implode(', ', $errors));
            }

            $prototypeName = ObjectAccess::getProperty($fusionComponentImplementation, 'fusionObjectName', true);
            $path = ObjectAccess::getProperty($fusionComponentImplementation, 'path', true);

            throw new FusionException(sprintf(
                'The @propTypes-validation for %s at path "%s" returned the following errors: %s',
                $prototypeName,
                $path,
                $message
            ));
        }
        return $joinPoint->getAdviceChain()->proceed($joinPoint);
    }
}
