<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StringMapEntryType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'caplogik_framework_extra_string_map_entry';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('value_type');
        $resolver->setAllowedTypes('value_type', ['string']);
        $resolver->setRequired('value_type');

        $resolver->setDefined('value_options');
        $resolver->setAllowedTypes('value_options', ['array']);
        $resolver->setDefault('value_options', []);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('key', TextType::class);
        $builder->add('value', $options['value_type'], $options['value_options']);
    }
}
