<?php


namespace CodexSoft\Transmission\TypescriptConverter;


use CodexSoft\Transmission\Schema\Elements\AbstractElement;

class SchemaReference
{

    /**
     * SchemaReference constructor.
     *
     * @param string $schemaClass
     * @param string $referenceName
     * @param AbstractElement $element
     * @param string|null $generatedTypescript
     */
    public function __construct(
        private string $schemaClass,
        private string $referenceName,
        private AbstractElement $element,
        private ?string $generatedTypescript,
    )
    {
    }

    /**
     * @return string
     */
    public function getSchemaClass(): string
    {
        return $this->schemaClass;
    }

    /**
     * @return string
     */
    public function getReferenceName(): string
    {
        return $this->referenceName;
    }

    /**
     * @return AbstractElement
     */
    public function getElement(): AbstractElement
    {
        return $this->element;
    }

    /**
     * @return string|null
     */
    public function getGeneratedTypescript(): ?string
    {
        return $this->generatedTypescript;
    }
}
