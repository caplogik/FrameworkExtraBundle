<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Type\SchemaBuilder;

use Caplogik\FrameworkExtraBundle\Form\Type\DiscriminatedUnionType;
use Caplogik\FrameworkExtraBundle\SchemaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SchemaBuilderType extends AbstractType
{
    public function getParent()
    {
        return DiscriminatedUnionType::class;
    }

    public function getBlockPrefix()
    {
        return 'caplogik_framework_extra_schema_builder_schema_builder';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('translation_domain', 'CaplogikFrameworkExtraBundle');

        $resolver->setDefined('recursion');
        $resolver->setAllowedTypes('recursion', ['int']);
        $resolver->setDefault('recursion', 3);

        $resolver->setDefault('discriminator_from', function ($data) {
            return $data['type'] ?? null;
        });

        $resolver->setDefault('union', function (Options $options) {
            $recursion = $options['recursion'];

            $union = [
                SchemaType::BOOLEAN => [
                    'label' => 'schema.builder.boolean.label',
                    'type' => BooleanType::class,
                ],
                SchemaType::NUMBER => [
                    'label' => 'schema.builder.number.label',
                    'type' => NumberType::class,
                ],
                SchemaType::STRING => [
                    'label' => 'schema.builder.string.label',
                    'type' => StringType::class,
                ],
            ];

            if ($recursion > 0) {
                $childRecursion = $recursion - 1;

                $union = array_merge($union, [
                    SchemaType::ARRAY => [
                        'label' => 'schema.builder.array.label',
                        'type' => ArrayType::class,
                        'options' => [
                            'recursion' => $childRecursion,
                        ],
                    ],
                    SchemaType::OBJECT => [
                        'label' => 'schema.builder.object.label',
                        'type' => ObjectType::class,
                        'options' => [
                            'recursion' => $childRecursion,
                        ],
                    ],
                ]);
            }

            return $union;
        });
    }
}
