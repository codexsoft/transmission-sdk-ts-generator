<?php


namespace CodexSoft\Transmission\TypescriptConverter\Converters;

use CodexSoft\Transmission\Schema\Elements\BoolElement;

/**
 * @property BoolElement $element
 */
class BoolElementTsConverter extends ScalarElementTsConverter
{
    public function convert(): string
    {
        $choices = $this->element->getChoicesSourceArray();
        if ($choices) {
            return \implode(
                '|',
                \array_map(static fn($choice) => $choice ? 'true' : 'false', $choices)
            );
        }

        return 'boolean';
    }
}
