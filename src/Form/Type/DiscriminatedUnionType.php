<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Type;

use Caplogik\FrameworkExtraBundle\Form\DataMapper\DiscriminatedUnionMapper;
use Caplogik\FrameworkExtraBundle\Form\DataMapper\PolymorphFormMapper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
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
        // We don't provide a data_class but the default value should not be a empty array
        $resolver->setDefault('empty_data', null);

        $resolver->setRequired('union');
        $resolver->setAllowedTypes('union', ['array']);

        $resolver->setRequired('discriminator_from');
        $resolver->setAllowedTypes('discriminator_from', ['callable']);

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
        ] = $options;

        $builder->setDataMapper(new DiscriminatedUnionMapper($union, $discriminatorFrom));

        $choices = iterator_to_array((function () use ($union) {
            foreach ($union as $discriminator => $config) yield $config['label'] => $discriminator;
        })());

        $builder->add(self::FIELD_DISCRIMINATOR, ChoiceType::class, [
            'choices' => $choices,
        ]);

        $inner = $builder->create(self::FIELD_INNER, FormType::class);
        $builder->add($inner);

        foreach ($union as $discriminator => $config) {
            $inner->add($discriminator, $config['type'], $config['options'] ?? []);
        }
    }
}
