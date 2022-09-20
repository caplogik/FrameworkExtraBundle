<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle;

use Caplogik\FrameworkExtraBundle\Form\Type\DiscriminatedUnionType;
use Generator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

final class DiscriminatedUnionUtils
{
    public function containsDiscriminatedUnionType(FormInterface $form): bool
    {
        return $this->some(
            $this->iterateForm($form),
            function (FormInterface $form) {
                return $this->isDiscriminatedUnionType($form);
            }
        );
    }

    public function isDiscriminatedUnionType(FormInterface $form): bool
    {
        return $this->some(
            $this->iterateType($form->getConfig()->getType()),
            function (ResolvedFormTypeInterface $form) {
                return $form->getInnerType() instanceof DiscriminatedUnionType;
            }
        );
    }

    private function iterateForm(FormInterface $form): Generator
    {
        yield $form;

        foreach ($form as $child) {
            yield from $this->iterateForm($child);
        }
    }

    private function iterateType(ResolvedFormTypeInterface $form): Generator
    {
        yield $form;

        $parent = $form->getParent();

        if ($parent !== null) {
            yield from $this->iterateType($parent);
        }
    }

    private function some(iterable $xs, callable $f): bool
    {
        foreach ($xs as $x) if ($f($x)) return true;
        return false;
    }
}
