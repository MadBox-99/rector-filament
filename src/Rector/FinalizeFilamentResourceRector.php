<?php

declare(strict_types=1);

namespace Madbox99\RectorFilament\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class FinalizeFilamentResourceRector extends AbstractRector
{
    /**
     * @var array<int, string>
     */
    private const PARENT_CLASSES = [
        'Filament\\Resources\\Resource',
        'Filament\\Resources\\Pages\\Page',
        'Filament\\Resources\\Pages\\ListRecords',
        'Filament\\Resources\\Pages\\CreateRecord',
        'Filament\\Resources\\Pages\\EditRecord',
        'Filament\\Resources\\Pages\\ViewRecord',
        'Filament\\Resources\\Pages\\ManageRecords',
        'Filament\\Resources\\Pages\\ManageRelatedRecords',
        'Filament\\Resources\\RelationManagers\\RelationManager',
        'Filament\\Pages\\Page',
        'Filament\\Widgets\\Widget',
        'Filament\\Widgets\\StatsOverviewWidget',
        'Filament\\Widgets\\ChartWidget',
        'Filament\\Widgets\\TableWidget',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Mark Filament Resource, Page, RelationManager and Widget classes as `final`.',
            [
                new CodeSample(
                    <<<'CODE'
class UserResource extends Resource
{
}
CODE,
                    <<<'CODE'
final class UserResource extends Resource
{
}
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
        return [Class_::class];
    }

    /**
     * @param  Class_  $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFinal() || $node->isAbstract()) {
            return null;
        }

        if (! $node->extends instanceof Node\Name) {
            return null;
        }

        $parent = $this->getName($node->extends);

        if (! in_array($parent, self::PARENT_CLASSES, true)) {
            return null;
        }

        $node->flags |= Class_::MODIFIER_FINAL;

        return $node;
    }
}
