<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Type\SchemaBuilder;

use Caplogik\FrameworkExtraBundle\SchemaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RestrictedValueType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'caplogik_framework_extra_schema_builder_restricted_value';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('translation_domain', 'CaplogikFrameworkExtraBundle');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('label', TextType::class, [
            'label' => 'schema.builder.label_label',
            'constraints' => [
                new Assert\NotBlank(),
                new Assert\Type('string'),
            ]
        ]);

        $choices = [
            'schema.builder.boolean.label' => SchemaType::BOOLEAN,
            'schema.builder.number.label' => SchemaType::NUMBER,
            'schema.builder.string.label' => SchemaType::STRING,
            'schema.builder.date.label' => SchemaType::DATE,
        ];

        $builder->add('type', ChoiceType::class, [
            'choices' => $choices,
            'placeholder' => '',
            'constraints' => [
                new Assert\NotNull(),
                new Assert\Choice(array_values($choices)),
            ]
        ]);
    }
}
