<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Type\SchemaBuilder;

use Caplogik\FrameworkExtraBundle\SchemaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArrayType extends AbstractType
{
    public function getParent()
    {
        return BaseType::class;
    }

    public function getBlockPrefix()
    {
        return 'caplogik_framework_extra_schema_builder_array';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('recursion');
        $resolver->setAllowedTypes('recursion', ['int']);
        $resolver->setRequired('recursion');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', HiddenType::class, [
            'data' => SchemaType::ARRAY
        ]);

        $builder->add('minimumCount', IntegerType::class);
        $builder->add('maximumCount', IntegerType::class);

        $builder->add('items', SchemaBuilderType::class, [
            'recursion' => $options['recursion'],
        ]);
    }
}
