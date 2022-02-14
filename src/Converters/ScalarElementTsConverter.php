<?php


namespace CodexSoft\Transmission\TypescriptConverter\Converters;


use CodexSoft\Transmission\TypescriptConverter\TransmissionToTypescriptConverter;
use CodexSoft\Transmission\Schema\Elements\BoolElement;
use CodexSoft\Transmission\Schema\Elements\NumberElement;
use CodexSoft\Transmission\Schema\Elements\ScalarElement;
use CodexSoft\Transmission\Schema\Elements\StringElement;

/**
 * @property ScalarElement $element
 */
class ScalarElementTsConverter extends AbstractElementTsConverter
{
    public function __construct(
        ScalarElement $element,
        TransmissionToTypescriptConverter $factory
    )
    {
        parent::__construct($element, $factory);
    }

    public function convert(): string
    {
        $elementClass = \get_class($this->element);

        $knownTypes = [
            BoolElement::class => 'boolean',
            ScalarElement::class => 'number|string|boolean',
            StringElement::class => 'string',
            NumberElement::class => 'number',

        ];

        if (\array_key_exists($elementClass, $knownTypes)) {
            return $knownTypes[$elementClass];
        }

        foreach (\class_parents($elementClass) as $classParent) {
            if (\array_key_exists($classParent, $knownTypes)) {
                return $knownTypes[$classParent];
            }
        }

        return 'any';
    }
}
