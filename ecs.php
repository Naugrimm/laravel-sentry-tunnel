<?php

use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\Operator\UnaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $containerConfigurator->import(SetList::PSR_12);
    $containerConfigurator->import(SetList::CLEAN_CODE);
    $containerConfigurator->import(SetList::ARRAY);
    $containerConfigurator->import(SetList::STRICT);

    $containerConfigurator->import(SetList::PHP_CS_FIXER);
    $parameters->set(Option::SKIP, [
        OrderedClassElementsFixer::class,
        SingleQuoteFixer::class,
        UnaryOperatorSpacesFixer::class,
    ]);
};
