<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ClassDiscriminatedUnionType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'caplogik_framework_extra_class_discriminated_union';
    }

    public function getParent()
    {
        return DiscriminatedUnionType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // $resolver->setRequired('union');
        // $resolver->setAllowedTypes('union', ['array']);

        $resolver->setDefault('discriminator_from', function (Options $options) {
            $union = $options['union'];

            return function ($data) use ($union) {
                $class = get_class($data);

                foreach ($union as $discriminator => $config) {
                    if ($class === $config['class']) {
                        return $discriminator;
                    }
                }
            };
        });
    }
}
