<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Type\SchemaBuilder;

use Caplogik\FrameworkExtraBundle\SchemaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class StringType extends AbstractType
{
    public function getParent()
    {
        return BaseType::class;
    }

    public function getBlockPrefix()
    {
        return 'caplogik_framework_extra_schema_builder_string';
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('type', HiddenType::class, [
            'data' => SchemaType::STRING
        ]);

        $builder->add('minimumLength', IntegerType::class);
        $builder->add('maximumLength', IntegerType::class);
    }
}
