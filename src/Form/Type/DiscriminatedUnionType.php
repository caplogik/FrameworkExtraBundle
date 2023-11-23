<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Type;

use Caplogik\FrameworkExtraBundle\Form\DataMapper\DiscriminatedUnionMapper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiscriminatedUnionType extends AbstractType
{
    const FIELD_DISCRIMINATOR = 'discriminator';
    const FIELD_INNER = 'inner';

    public function getBlockPrefix()
    {
        return 'caplogik_framework_extra_discriminated_union';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('translation_domain', 'CaplogikFrameworkExtraBundle');

        // We don't provide a data_class but the default value should not be a empty array
        $resolver->setDefault('empty_data', null);

        $resolver->setRequired('union');
        $resolver->setAllowedTypes('union', ['array']);

        $resolver->setRequired('discriminator_from');
        $resolver->setAllowedTypes('discriminator_from', ['callable']);

        $resolver->setDefined('discriminator_options');
        $resolver->setAllowedTypes('discriminator_options', ['array']);
        $resolver->setDefault('discriminator_options', []);

        $resolver->setDefined('inner_options');
        $resolver->setAllowedTypes('inner_options', ['array']);
        $resolver->setDefault('inner_options', []);

        // prototype only available for sf 5
        // $resolver->define('union')
        //     ->required()
        //     ->default(function (OptionsResolver $resolver) {
        //         $resolver->setPrototype(true);
        //         $resolver->define('label')->allowedTypes('string')->required();
        //         $resolver->define('type')->allowedTypes('string')->required();
        //         $resolver->define('options')->allowedTypes('array')->default([]);
        //     });
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        [
            'union' => $union,
            'discriminator_from' => $discriminatorFrom,
            'discriminator_options' => $discriminatorOptions,
            'inner_options' => $innerOptions,
        ] = $options;

        $builder->setDataMapper(new DiscriminatedUnionMapper($union, $discriminatorFrom));

        $builder->add(
            self::FIELD_DISCRIMINATOR,
            ChoiceType::class,
            array_merge(
                [
                    'label' => 'discriminated_union.discriminator_label',
                ],
                $discriminatorOptions,
                [
                    'choices' => \array_keys($union),
                    'choice_label' => fn ($choice) => $union[$choice]['label'],
                    'required' => $options['required']
                ]
            )
        );

        $inner = $builder->create(self::FIELD_INNER, FormType::class, $innerOptions);
        $builder->add($inner);

        foreach ($union as $discriminator => $config) {
            $inner->add(
                $discriminator,
                $config['type'],
                array_merge(
                    [
                        'label' => false
                    ],
                    $config['options'] ?? [],
                )
            );
        }
    }
}
