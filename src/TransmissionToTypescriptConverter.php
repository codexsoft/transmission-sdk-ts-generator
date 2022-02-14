<?php


namespace CodexSoft\Transmission\TypescriptConverter;


use CodexSoft\Transmission\Schema\Contracts\JsonSchemaInterface;
use CodexSoft\Transmission\Schema\Elements\BoolElement;
use CodexSoft\Transmission\Schema\Elements\NumberElement;
use CodexSoft\Transmission\Schema\Elements\StringElement;
use CodexSoft\Transmission\TypescriptConverter\Converters\AbstractElementTsConverter;
use CodexSoft\Transmission\TypescriptConverter\Converters\BoolElementTsConverter;
use CodexSoft\Transmission\TypescriptConverter\Converters\CollectionElementTsConverter;
use CodexSoft\Transmission\TypescriptConverter\Converters\JsonElementTsConverter;
use CodexSoft\Transmission\TypescriptConverter\Converters\NumberElementTsConverter;
use CodexSoft\Transmission\TypescriptConverter\Converters\ScalarElementTsConverter;
use CodexSoft\Transmission\Schema\Elements\AbstractElement;
use CodexSoft\Transmission\Schema\Elements\CollectionElement;
use CodexSoft\Transmission\Schema\Elements\JsonElement;
use CodexSoft\Transmission\Schema\Elements\ReferencableElementInterface;
use CodexSoft\Transmission\Schema\Elements\ScalarElement;
use CodexSoft\Transmission\TypescriptConverter\Converters\StringElementTsConverter;

class TransmissionToTypescriptConverter
{
    /** @var SchemaReference[] */
    protected array $references = [];
    protected array $referencesInWork = [];
    protected bool $useRefs = true;
    protected ?\Closure $createRefClosure = null;
    /** @var array<string, int> */
    protected array $generatedRefNames = [];
    protected array $knownConverters = [
        CollectionElement::class => CollectionElementTsConverter::class,
        JsonElement::class => JsonElementTsConverter::class,
        StringElement::class => StringElementTsConverter::class,
        NumberElement::class => NumberElementTsConverter::class,
        BoolElement::class => BoolElementTsConverter::class,
        ScalarElement::class => ScalarElementTsConverter::class,
    ];

    public function addKnownConverter(string $elementClass, string $converterClass): void
    {
        $this->knownConverters[$elementClass] = $converterClass;
    }

    /**
     * @param string|JsonSchemaInterface $class
     *
     * @return string
     * @throws \CodexSoft\Transmission\Schema\Exceptions\InvalidJsonSchemaException
     * @throws \ReflectionException
     */
    public function createRef(string|JsonSchemaInterface $class): string
    {
        /*
         * Skip processing of ready references
         */
        if (\array_key_exists($class, $this->references)) {
            return $this->references[$class]->getReferenceName();
        }

        if ($this->createRefClosure) {
            $refName = ($this->createRefClosure)($class);
        } else {
            /* default behaviour */
            $reflection = new \ReflectionClass($class);
            $refName = 'I'.$reflection->getShortName();
            //$refName = 'I'.\str_replace("\\", '_', $class);
        }

        /*
         * In case when same name was already generated, adding numeric postfix
         */
        if (\array_key_exists($refName, $this->generatedRefNames)) {
            $count = $this->generatedRefNames[$refName];
            $count++;
            $refName .= '__'.$count;
            $this->generatedRefNames[$refName] = $count;
        } else {
            $this->generatedRefNames[$refName] = 1;
        }

        /*
         * Because referenced schema can contain child references, they should be recursively collected.
         * To avoid infinite loops in case of cyclic references, we remember classes that is being
         * processed and skip processing if reference mentioned once again.
         */

        if (!\array_key_exists($class, $this->referencesInWork)) {
            $this->referencesInWork[$class] = $class;

            $refSchema = new JsonElement($class::createSchema());
            $code = $this->convert($refSchema);

            $reference = new SchemaReference($class, $refName, $refSchema, $code);
            $this->references[$class] = $reference;

            unset($this->referencesInWork[$class]);
        }

        return $refName;
    }

    protected function findConverterClass(string $elementClass): string
    {
        $knownConverters = [
            CollectionElement::class => CollectionElementTsConverter::class,
            JsonElement::class => JsonElementTsConverter::class,
            StringElement::class => StringElementTsConverter::class,
            NumberElement::class => NumberElementTsConverter::class,
            BoolElement::class => BoolElementTsConverter::class,
            ScalarElement::class => ScalarElementTsConverter::class,
        ];

        if (\array_key_exists($elementClass, $knownConverters)) {
            return $knownConverters[$elementClass];
        }

        foreach (\class_parents($elementClass) as $classParent) {
            if (\array_key_exists($classParent, $knownConverters)) {
                return $knownConverters[$classParent];
            }
        }

        throw new \InvalidArgumentException("Element of class $elementClass is not supported for typescript convertation");
    }

    public function convert(AbstractElement $element, ?bool $useRefs = null): string
    {
        if ($useRefs !== null) {
            $this->useRefs = $useRefs;
        }

        if ($this->useRefs && $element instanceof ReferencableElementInterface && $element->isReference()) {
            $result = $this->createRef($element->getReferencedClass());
            if ($element instanceof CollectionElement) {
                $result = "Array<$result>";
            }
        } else {
            $converterClass = $this->findConverterClass(\get_class($element));
            /** @var AbstractElementTsConverter $converter */
            $converter = new $converterClass($element, $this);
            $result = $converter->convert();
        }

        if ($element->isNullable()) {
            $result .= '|null';
        }

        /**
         * Adding some description, example and default value info for element
         */

        $label = $element->getLabel() ? ' '.$element->getLabel() : '';

        $skipExamples = [42, 'Some text sample'];
        $example = '';
        if ($element instanceof ScalarElement) {
            $candidateExample = $element->getExample();
            if (!$skipExamples || !\in_array($candidateExample, [42, 'Some text sample'], true)) {
                $example = ' (e.g., '.\var_export($candidateExample, true).')';
            }
        }

        $default = $element instanceof ScalarElement && $element->hasDefaultValue()
            ? ' ('.\var_export($element->getDefaultValue(), true).' by default)'
            : '';

        if ($label || $example || $default) {
            $result .= ' /*'.$label.$default.$example.' */';
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isUseRefs(): bool
    {
        return $this->useRefs;
    }

    /**
     * @return SchemaReference[]
     */
    public function getReferences(): array
    {
        return $this->references;
    }

    /**
     * @param bool $useRefs
     *
     * @return TransmissionToTypescriptConverter
     */
    public function setUseRefs(bool $useRefs): TransmissionToTypescriptConverter
    {
        $this->useRefs = $useRefs;
        return $this;
    }

    /**
     * @param \Closure|null $createRefClosure
     *
     * @return TransmissionToTypescriptConverter
     */
    public function setCreateRefClosure(?\Closure $createRefClosure): static
    {
        $this->createRefClosure = $createRefClosure;
        return $this;
    }
}
