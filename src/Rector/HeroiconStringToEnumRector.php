<?php

declare(strict_types=1);

namespace Madbox99\RectorFilament\Rector;

use Filament\Support\Icons\Heroicon;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class HeroiconStringToEnumRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            "Replace 'heroicon-...' string literals with Filament\\Support\\Icons\\Heroicon enum cases.",
            [
                new CodeSample(
                    <<<'CODE'
->icon('heroicon-o-shopping-bag');
CODE,
                    <<<'CODE'
->icon(\Filament\Support\Icons\Heroicon::OutlinedShoppingBag);
CODE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [String_::class];
    }

    /**
     * @param  String_  $node
     */
    public function refactor(Node $node): ?Node
    {
        $value = $node->value;

        if (! str_starts_with($value, 'heroicon-')) {
            return null;
        }

        $enumValue = substr($value, strlen('heroicon-'));
        $case = Heroicon::tryFrom($enumValue);

        if (! $case instanceof Heroicon) {
            return null;
        }

        return new ClassConstFetch(
            new FullyQualified(Heroicon::class),
            new Identifier($case->name),
        );
    }
}
