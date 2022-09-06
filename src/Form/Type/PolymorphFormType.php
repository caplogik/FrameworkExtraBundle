<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Type;

use Caplogik\FrameworkExtraBundle\Form\DataMapper\PolymorphFormMapper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PolymorphFormType extends AbstractType
{
    const FIELD_DISCRIMINATOR = 'discriminator';
    const FIELD_INNER = 'inner';

    public function getBlockPrefix()
    {
        return 'caplogik_extra_polymorph_form';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // We don't provide a data_class but the default value should not be a
        $resolver->setDefault('empty_data', null);

        $resolver->define('mapping')
            ->required()
            ->default(function (OptionsResolver $resolver) {
                $resolver->setPrototype(true);
                $resolver->define('class')->allowedTypes('string')->required();
                $resolver->define('type')->allowedTypes('string')->required();
                $resolver->define('label')->allowedTypes('string')->required();
                $resolver->define('options')->allowedTypes('array')->default([]);
            });
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $mapping = $options['mapping'];

        $builder->setDataMapper(new PolymorphFormMapper($mapping));

        $choices = iterator_to_array((function () use ($mapping) {
            foreach ($mapping as $discriminator => $config) yield $config['label'] => $discriminator;
        })());

        $builder->add(self::FIELD_DISCRIMINATOR, ChoiceType::class, [
            'choices' => $choices,
        ]);

        $inner = $builder->create(self::FIELD_INNER, FormType::class);
        $builder->add($inner);

        foreach ($mapping as $discriminator => $config) {
            $inner->add($discriminator, $config['type'], $config['options']);
        }
    }
}
