<?php

namespace Atournayre\Form\Decorator;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping\Column;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\Length;

class MaxLengthFormDecorator
{
    private const DATABASE_TEXT_MAXLENGTH = 65535;

    private function __construct() {}

    public static function decorate(FormInterface $form): void
    {
        $formDataClass = $form->getConfig()->getDataClass();

        foreach ($form->all() as $formElement) {
            $formElementConfig = $formElement->getConfig();
            $options = $formElementConfig->getOptions();

            // If the form field already has a maxlength, we don't go any further
            // We consider that the developer did it on purpose
            if ($options['attr']['maxlength'] ?? null) {
                continue;
            }

            // If the form element is associated with a data_class
            // Then it is a sub-form
            // So we also decorate this sub-form
            if (!is_null($formElement->getConfig()->getDataClass())) {
                self::decorate($formElement);
                continue;
            }

            // If the property reflection fails, it is either that the property is unmapped, or that it is a button
            // So we don't go any further
            try {
                $reflectionProperty = new \ReflectionProperty($formDataClass, $formElement->getName());
            } catch (\ReflectionException $e) {
                continue;
            }

            // If the property has no attributes
            // So we don't go any further
            if ($reflectionProperty->getAttributes() === []) {
                continue;
            }

            // Initialization of an array to retrieve the maxlengths
            $maxlengths = [];

            // Add the maxlength associated with the Length constraint if it exists
            $reflectionAttributesLength = $reflectionProperty->getAttributes(Length::class);
            if ($reflectionAttributesLength !== []) {
                /** @var \ReflectionAttribute $attribute */
                $attribute = current($reflectionAttributesLength);
                $maxlengths[] = $attribute->getArguments()['max'] ?? null;
            }

            // Add the maxlength associated with the Doctrine column if it exists
            $reflectionAttributesColumn = $reflectionProperty->getAttributes(Column::class);
            if ($reflectionAttributesColumn !== []) {
                /** @var \ReflectionAttribute $attribute */
                $attribute = current($reflectionAttributesColumn);
                $arguments = $attribute->getArguments();
                $length = ($arguments['type'] ?? null) === Types::TEXT
                    ? self::DATABASE_TEXT_MAXLENGTH
                    : $arguments['length'] ?? null;
                $maxlengths[] = $length;
            }

            // Keep only the non-null maxlengths
            $maxlengths = array_filter($maxlengths);

            // If no maxlength found
            // We will not go any further
            if ($maxlengths === []) {
                continue;
            }

            // We keep the smallest maxlength among those defined or determined
            $maxlength = min($maxlengths);
            $options['attr']['maxlength'] = $maxlength;

            // We override the form
            $form->add($formElement->getName(), get_class($formElementConfig->getType()->getInnerType()), $options);
        }
    }
}
