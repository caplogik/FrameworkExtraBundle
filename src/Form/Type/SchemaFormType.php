<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Type;

use Caplogik\FrameworkExtraBundle\SchemaType;
use RuntimeException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SchemaFormType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'caplogik_framework_extra_schema_form';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('schema');
        $resolver->setAllowedTypes('schema', ['array']);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $schema = $options['schema'];

        if ($schema['type'] !== SchemaType::OBJECT) {
            throw new RuntimeException('Root schema type should be object');
            // TODO: symfony need compound root form but we can use a simple datatransformer
        }

        foreach ($schema['properties'] as $name => $property) {
            [$formType, $formOptions] = $this->getFormTuple($property);

            $builder->add($name, $formType, array_merge(
                [
                    'label' => $property['label']
                ],
                $formOptions
            ));
        }
    }

    private function getFormTuple(array $schema)
    {
        $schemaType = $schema['type'];

        $constraints = [
            new Assert\NotNull()
        ];

        if ($schemaType === SchemaType::BOOLEAN) {
            $constraints[] = new Assert\Type(['type' => 'boolean']);

            return [Form\CheckboxType::class, [
                'constraints' => $constraints
            ]];
        } elseif ($schemaType === SchemaType::NUMBER) {
            $constraints[] = new Assert\Type(['type' => 'numeric']);

            $rangeConstraint = $this->getRangedConstraint(
                Assert\Range::class,
                $schema['minimum'] ?? null,
                $schema['maximum'] ?? null,
            );

            if ($rangeConstraint) {
                $constraints[] = $rangeConstraint;
            }

            return [Form\NumberType::class, [
                'constraints' => $constraints
            ]];
        } elseif ($schemaType === SchemaType::STRING) {
            $constraints[] = new Assert\Type([
                'type' => 'string'
            ]);

            $lengthConstraint = $this->getRangedConstraint(
                Assert\Length::class,
                $schema['minimumLength'] ?? null,
                $schema['maximumLength'] ?? null,
            );

            if ($lengthConstraint) {
                $constraints[] = $lengthConstraint;
            }

            return [Form\TextType::class, [
                'constraints' => $constraints,
            ]];
        } elseif ($schemaType === SchemaType::ARRAY) {
            [$formType, $formOptions] = $this->getFormTuple($schema['items']);

            $constraints[] = new Assert\Type([
                'type' => 'array'
            ]);

            $countConstraint = $this->getRangedConstraint(
                Assert\Count::class,
                $schema['minimumItems'] ?? null,
                $schema['maximumItems'] ?? null,
            );

            if ($countConstraint) {
                $constraints[] = $countConstraint;
            }


            return [Form\CollectionType::class, [
                'entry_type' => $formType,
                'entry_options' => array_merge(['label' => false], $formOptions),
                'allow_add' => true,
                'allow_delete' => true,
                'constraints' => $constraints,
                'error_bubbling' => false,
            ]];
        } elseif ($schemaType === SchemaType::OBJECT) {
            return [SchemaFormType::class, [
                'schema' => $schema,
                'constraints' => $constraints,
            ]];
        } else {
            throw new RuntimeException('Unsupported schema type');
        }
    }

    private function getRangedConstraint(string $class, $min, $max)
    {
        if ($min === null && $max === null) {
            return null;
        }

        $options = [];

        if ($min !== null) {
            $options['min'] = $min;
        }

        if ($max !== null) {
            $options['max'] = $max;
        }

        return new $class($options);
    }
}
