<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Type\SchemaBuilder;

use Caplogik\FrameworkExtraBundle\SchemaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType as CoreNumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NumberType extends AbstractType
{
    public function getParent()
    {
        return BaseType::class;
    }

    public function getBlockPrefix()
    {
        return 'caplogik_framework_extra_schema_builder_number';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('type', SchemaType::NUMBER);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('minimum', CoreNumberType::class, [
            'label' => 'schema.builder.number.minimum_label',

        ]);

        $builder->add('maximum', CoreNumberType::class, [
            'label' => 'schema.builder.number.maximum_label',
        ]);
    }
}
