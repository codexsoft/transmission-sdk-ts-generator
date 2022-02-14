<?php


namespace CodexSoft\Transmission\TypescriptConverter\Converters;


use CodexSoft\Transmission\TypescriptConverter\TransmissionToTypescriptConverter;
use CodexSoft\Transmission\Schema\Elements\JsonElement;

/**
 * @property JsonElement $element
 */
class JsonElementTsConverter extends AbstractElementTsConverter
{
    public function __construct(
        JsonElement $element,
        TransmissionToTypescriptConverter $factory
    )
    {
        parent::__construct($element, $factory);
    }

    public function convert(): string
    {
        $result = [];

        foreach ($this->element->getSchema() as $key => $subSchema) {
            $keyName = $key;
            if (!$subSchema->isRequired()) {
                $keyName = $key.'?';
            }

            $result[$keyName] = $this->factory->convert($subSchema);
        }

        if ($this->element->getExtraElementSchema()) {
            $result['[k: string]'] = $this->factory->convert($this->element->getExtraElementSchema());
        }

        return $this->stringifyTypescriptObject($result);
    }

    public function stringifyTypescriptObject(array $data, $indent = '    '): string
    {
        $result = "{\n";
        $lines = [];
        foreach ($data as $key => $value) {
            $lines[] = $indent.$key.': '.$value;
        }
        $result .= \implode(",\n", $lines);
        $result .= "\n}";

        return $result;
    }
}
