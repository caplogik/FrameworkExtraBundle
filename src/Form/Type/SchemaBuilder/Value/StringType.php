<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Type\SchemaBuilder;

use Caplogik\FrameworkExtraBundle\SchemaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StringType extends AbstractType
{
    public function getParent()
    {
        return BaseType::class;
    }

    public function getBlockPrefix()
    {
        return 'caplogik_framework_extra_schema_builder_string';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('type', SchemaType::STRING);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('minimumLength', IntegerType::class, [
            'label' => 'schema.builder.string.minimum_length_label',
        ]);

        $builder->add('maximumLength', IntegerType::class, [
            'label' => 'schema.builder.string.maximum_length_label',
        ]);
    }
}
