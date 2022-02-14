<?php


namespace CodexSoft\Transmission\TypescriptConverter\Converters;


use CodexSoft\Transmission\TypescriptConverter\TransmissionToTypescriptConverter;
use CodexSoft\Transmission\Schema\Elements\CollectionElement;

/**
 * @property CollectionElement $element
 */
class CollectionElementTsConverter extends AbstractElementTsConverter
{
    public function __construct(
        CollectionElement $element,
        TransmissionToTypescriptConverter $factory
    )
    {
        parent::__construct($element, $factory);
    }

    public function convert(): string
    {
        return 'Array<'.$this->factory->convert($this->element->getElementSchema()).'>';
    }
}
