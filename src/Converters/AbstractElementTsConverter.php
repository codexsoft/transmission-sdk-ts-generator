<?php


namespace CodexSoft\Transmission\TypescriptConverter\Converters;


use CodexSoft\Transmission\TypescriptConverter\TransmissionToTypescriptConverter;
use CodexSoft\Transmission\Schema\Elements\AbstractElement;

abstract class AbstractElementTsConverter
{
    public function __construct(
        protected AbstractElement $element,
        protected TransmissionToTypescriptConverter $factory
    )
    {
    }

    abstract public function convert(): string;
}
