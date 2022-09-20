<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Type\SchemaBuilder;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class BaseType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'caplogik_framework_extra_schema_builder_base';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // $builder->add('label', TextType::class);

        // $builder->add('required', CheckboxType::class);
    }
}
