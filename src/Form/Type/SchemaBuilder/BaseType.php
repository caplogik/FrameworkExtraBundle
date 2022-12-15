<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Type\SchemaBuilder;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BaseType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'caplogik_framework_extra_schema_builder_base';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('translation_domain', 'CaplogikFrameworkExtraBundle');

        $resolver->setRequired('type');
        $resolver->setAllowedTypes('type', ['string']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', HiddenType::class, [
            'data' => $options['type']
        ]);

        $builder->add('label', TextType::class, [
            'label' => 'schema.builder.label_label',
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
            ]
        ]);

        // TODO: required doesn't make sense for objects and arrays
        $builder->add('required', CheckboxType::class, [
            'label' => 'schema.builder.required_label',
            'required' => false
        ]);
    }
}
