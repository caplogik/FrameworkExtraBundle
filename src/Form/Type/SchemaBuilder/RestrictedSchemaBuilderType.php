<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Type\SchemaBuilder;

use Caplogik\FrameworkExtraBundle\Form\Type\KeyValuePairsType;
use Caplogik\FrameworkExtraBundle\SchemaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A restricted schema builder
 * Only propose an initial object with properties limited to bool, number, string and date.
 * No validation configuration
 */
class RestrictedSchemaBuilderType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'caplogik_framework_extra_schema_builder_restricted_schema_builder';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('translation_domain', 'CaplogikFrameworkExtraBundle');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', HiddenType::class, [
            'data' => SchemaType::OBJECT,
        ]);

        $builder->add('label', HiddenType::class, [
            'empty_data' => false,
        ]);

        $builder->add('properties', KeyValuePairsType::class, [
            'label' => false,
            'value_type' => RestrictedValueType::class,
            'value_options' => ['label' => false],
            'allow_add' => true,
            'allow_delete' => true,
        ]);
    }
}
