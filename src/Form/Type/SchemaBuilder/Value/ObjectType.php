<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Type\SchemaBuilder;

use Caplogik\FrameworkExtraBundle\Form\Type\KeyValuePairsType;
use Caplogik\FrameworkExtraBundle\SchemaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectType extends AbstractType
{
    public function getParent()
    {
        return BaseType::class;
    }

    public function getBlockPrefix()
    {
        return 'caplogik_framework_extra_schema_builder_object';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('type', SchemaType::OBJECT);

        $resolver->setDefined('recursion');
        $resolver->setAllowedTypes('recursion', ['int']);
        $resolver->setRequired('recursion');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('properties', KeyValuePairsType::class, [
            'label' => 'schema.builder.object.properties_label',
            'entry_options' => [
                'label' => false
            ],
            'value_type' => SchemaBuilderType::class,
            'value_options' => [
                'recursion' => $options['recursion'],
            ],
            'allow_add' => true,
            'allow_delete' => true,
        ]);
    }
}
