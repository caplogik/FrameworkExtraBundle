<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Extension;

use Caplogik\FrameworkExtraBundle\Form\EventSubscriber\PolymorphFormSubscriber;
use Caplogik\FrameworkExtraBundle\Form\Type\PolymorphFormType;
use Generator;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

final class PolymorphFormExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new PolymorphFormSubscriber());
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // Disable HTML 5 form validation if the form tree contains a PolymorphFormType
        if ($form->isRoot() && $this->containsPolymorphFormType($form)) {
            $view->vars['attr']['novalidate'] = true;
        }
    }

    private function containsPolymorphFormType(FormInterface $form): bool
    {
        return $this->some(
            $this->iterate($form),
            fn ($form) => $form->getConfig()->getType()->getInnerType() instanceof PolymorphFormType
        );
    }

    private function iterate(FormInterface $form): Generator
    {
        yield $form;

        foreach ($form as $child) {
            yield from $this->iterate($child);
        }
    }

    private function some(iterable $xs, callable $f): bool
    {
        foreach ($xs as $x) if ($f($x)) return true;
        return false;
    }
}
