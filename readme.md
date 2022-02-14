# Typescript converter for CodexSoft Transmission

This library provides a way to convert Transmission elements into typescript interfaces (recursively). It supports references.

## Installation

```shell script
composer require codexsoft/transmission-ts-converter
``` 

## Extending

By default, only basic element classes can be converted: `CollectionElement`, `JsonElement`, `StringElement`, `NumberElement`, `BoolElement`, `ScalarElement`. You can add your own converters (each needs to extend `CodexSoft\Transmission\Typescript\Converters\AbstractElementTsConverter`)

```php
$toTs = (new TransmissionToTypescriptConverter());
$toTs->addKnownConverter(MyElement::class => MyElementTsConverter::class);
```

## Usage

Using this utility you can generate SDK for typescript. For given directory with controllers that implement `CodexSoft\Transmission\OpenApi3\OpenApi3OperationInterface` generator of whole API can be easily implemented.

```php
// ...preparing Symfony Finder or whatever
$endpointReflections = [];
foreach ($finder->getIterator() as $fileInfo) {
    $fqnClassName = (string) 'App'.(new \Stringy\Stringy($fileInfo->getRealPath()))
        ->removeRight('.php')
        ->replace('/', "\\");
        
    $reflectionClass = new \ReflectionClass($fqnClassName);
    if ($reflectionClass->isAbstract()) {
        continue;
    }

    if (!$reflectionClass->implementsInterface(OpenApi3OperationInterface::class)) {
        continue;
    }
    
    $endpointReflections[] = $reflectionClass;
    
    $toTs = (new TransmissionToTypescriptConverter());
    
    /**
     * Set ref interface name generator
     */
    $toTs->setCreateRefClosure(function(string $class) {
        $reflection = new \ReflectionClass($class);
        return (string) (new \Stringy\Stringy('I'.$reflection->getShortName()))->removeRight('Transformer');
    });
}
    
```

## Testing

```shell script
php ./vendor/bin/phpunit
```
