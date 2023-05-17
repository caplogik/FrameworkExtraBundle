<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\Type;

use Caplogik\FrameworkExtraBundle\SchemaType as Type;
use DateTimeImmutable;
use RuntimeException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SchemaType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'caplogik_framework_extra_schema';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('schema');
        $resolver->setAllowedTypes('schema', ['array']);

        // NOTE: When used at root the form needs to be compound form
        // We can remove this limitation by using a wrapper with a datatransformer
        $resolver->setAllowedValues('schema', function (array $schema) {
            return $schema['type'] === Type::OBJECT;
        });

        $resolver->setDefault('label', function (Options $options) {
            return $options['schema']['label'];
        });
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $schema = $options['schema'];

        foreach ($schema['order'] as $name) {
            $property = $schema['properties'][$name];
            [$formType, $formOptions] = $this->getFormTypeOptionsPair($property);

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
        $required = $schema['required'] ?? false;
        $constraints = [];

        if ($required) {
            $constraints[] = new Assert\NotNull();
        }

        if ($schemaType === Type::BOOLEAN) {
            $constraints[] = new Assert\Type(['type' => 'boolean']);

            return [Form\CheckboxType::class, [
                'constraints' => $constraints,
                'required' => $required,
            ]];
        } elseif ($schemaType === Type::NUMBER) {
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
                'constraints' => $constraints,
                'required' => $required,
            ]];
        } elseif ($schemaType === Type::STRING) {
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
                'required' => $required,
            ]];
        } elseif ($schemaType === Type::DATE) {
            $constraints[] = new Assert\Type([
                'type' => DateTimeImmutable::class
            ]);

            $constraints[] = new Assert\Date();

            return [Form\DateType::class, [
                'constraints' => $constraints,
                'required' => $required,
                'input' => 'datetime_immutable'
            ]];
        } elseif ($schemaType === Type::ARRAY) {
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
                'required' => $required,
            ]];
        } elseif ($schemaType === Type::OBJECT) {
            return [SchemaType::class, [
                'schema' => $schema,
                'required' => $required,
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
