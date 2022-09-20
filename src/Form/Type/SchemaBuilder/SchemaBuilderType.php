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
        $resolver->setDefined('recursion');
        $resolver->setAllowedTypes('recursion', ['int']);
        $resolver->setDefault('recursion', 4);

        $resolver->setDefault('discriminator_from', function ($data) {
            return $data['type'] ?? null;
        });

        $resolver->setDefault('union', function (Options $options) {
            $recursion = $options['recursion'];

            $union = [
                SchemaType::BOOLEAN => [
                    'label' => 'Boolean',
                    'type' => BooleanType::class,
                ],
                SchemaType::NUMBER => [
                    'label' => 'Number',
                    'type' => NumberType::class,
                ],
                SchemaType::STRING => [
                    'label' => 'String',
                    'type' => StringType::class,
                ],
            ];

            if ($recursion > 0) {
                $childRecursion = $recursion - 1;

                $union = array_merge($union, [
                    SchemaType::ARRAY => [
                        'label' => 'Array',
                        'type' => ArrayType::class,
                        'options' => [
                            'recursion' => $childRecursion,
                        ],
                    ],
                    SchemaType::OBJECT => [
                        'label' => 'Object',
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
