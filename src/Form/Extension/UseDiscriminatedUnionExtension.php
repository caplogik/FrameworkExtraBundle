<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Extension;

use Caplogik\FrameworkExtraBundle\DiscriminatedUnionUtils;
use Caplogik\FrameworkExtraBundle\Form\EventSubscriber\DiscriminatedUnionSubscriber;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

final class UseDiscriminatedUnionExtension extends AbstractTypeExtension
{
    /** @var DiscriminatedUnionUtils */
    private $discriminatedUnionUtils;

    public function __construct(DiscriminatedUnionUtils $discriminatedUnionUtils)
    {
        $this->discriminatedUnionUtils = $discriminatedUnionUtils;
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new DiscriminatedUnionSubscriber($this->discriminatedUnionUtils));
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        // Disable HTML 5 form validation if the form tree contains a DiscriminatedUnionType
        if ($form->isRoot() && $this->discriminatedUnionUtils->containsDiscriminatedUnionType($form)) {
            $view->vars['attr']['novalidate'] = true;
        }
    }
}
