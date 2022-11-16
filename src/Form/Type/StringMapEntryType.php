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
        $resolver->setDefault('translation_domain', 'CaplogikFrameworkExtraBundle');

        $resolver->setDefined('key_options');
        $resolver->setAllowedTypes('key_options', ['array']);
        $resolver->setRequired('key_options');

        $resolver->setDefined('value_type');
        $resolver->setAllowedTypes('value_type', ['string']);
        $resolver->setRequired('value_type');

        $resolver->setDefined('value_options');
        $resolver->setAllowedTypes('value_options', ['array']);
        $resolver->setRequired('value_options');
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('key', TextType::class, array_merge(
            [
                'label' => 'string_map.entry.key_label'
            ],
            $options['key_options']
        ));

        $builder->add('value', $options['value_type'], array_merge(
            [
                'label' => 'string_map.entry.value_label'
            ],
            $options['value_options']
        ));
    }
}
