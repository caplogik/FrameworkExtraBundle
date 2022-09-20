<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StringMapType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'caplogik_framework_extra_string_map';
    }

    public function getParent(): string
    {
        return CollectionType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined('value_type');
        $resolver->setAllowedTypes('value_type', ['string']);
        $resolver->setRequired('value_type');

        $resolver->setDefined('value_options');
        $resolver->setAllowedTypes('value_options', ['array']);
        $resolver->setDefault('value_options', []);

        $resolver->setDefaults([
            'entry_type' => StringMapEntryType::class,
            'entry_options' => function (Options $options) {
                return [
                    'value_type' => $options['value_type'],
                    'value_options' => $options['value_options'],
                ];
            },
        ]);

        $resolver->setNormalizer('data', function (Options $options, $value) {
            dump($value);

            return $value;
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $xs = $event->getData();

            if ($xs === null) {
                return;
            }

            $ys = [];

            foreach ($xs as $key => $value) {
                $ys[] = [
                    'key' => $key,
                    'value' => $value
                ];
            }

            $event->setData($ys);
        }, 1);


        $builder->addModelTransformer(new CallbackTransformer(
            function ($xs) {
                return $xs;
            },
            function ($xs) {
                $ys = [];

                foreach ($xs as ['key' => $key, 'value' => $value]) {
                    if (array_key_exists($key, $ys)) {
                        throw new TransformationFailedException('Duplicate key detected');
                    }

                    $ys[$key] = $value;
                }

                return $ys;
            }
        ));
    }
}
