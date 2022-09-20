# DiscriminatedUnionType

## Usage

```php
<?php declare(strict_types=1);

namespace App\Form\Type;

use Caplogik\FrameworkExtraBundle\Form\Type\DiscriminatedUnionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class NumberOrStringValue extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('value', DiscriminatedUnionType::class, [
            'union' => [
                'number' => [
                    'label' => 'Number',
                    'type' => NumberType::class,
                ],
                'string' => [
                    'label' => 'String',
                    'type' => TextType::class,
                ],
            ],
            'discriminator_from' => fn ($x) => match (true) {
                is_int($x) || is_float($x) => 'number',
                is_string($x) => 'string',
            }
        ]);
    }
}

```

```php
<?php declare(strict_types=1);

namespace App\Form\Type;

use App\Form\Data\{Circle, Frame, Rectangle, Square};
use Caplogik\FrameworkExtraBundle\Form\Type\ClassDiscriminatedUnionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FrameType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Frame::class
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name');

        $builder->add('shape', ClassDiscriminatedUnionType::class, [
            'union' => [
                'circle' => [
                    'label' => 'Circle',
                    'class' => Circle::class,
                    'type' => CircleType::class,
                    'options' => [
                        'help' => 'This is a circle form'
                    ]
                ],
                'square' => [
                    'label' => 'Square',
                    'class' => Square::class,
                    'type' => SquareType::class,
                ],
                'rectangle' => [
                    'label' => 'Rectangle',
                    'class' => Rectangle::class,
                    'type' => RectangleType::class,
                ]
            ]
        ]);
    }
}

```

## Progressive enhancement

This form type render all sub forms, hiding them based on the submitted choice is not handled by the bundle. This can be achieved with custom form themes or extensions like the example provided below with a form extension and [petite-vue](https://github.com/vuejs/petite-vue)


```php
<?php declare(strict_types=1);

namespace App\Form\Extension;

use Caplogik\FrameworkExtraBundle\Form\Type\DiscriminatedUnionType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

final class DiscriminatedUnionPetiteVueExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [DiscriminatedUnionType::class];
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $union = $options['union'];

        $discriminator = $view->children[DiscriminatedUnionType::FIELD_DISCRIMINATOR];
        $inner = $view->children[DiscriminatedUnionType::FIELD_INNER];

        $view->vars['attr']['v-scope'] = '{ discriminator: null }';

        $discriminator->vars['attr'] = array_merge($discriminator->vars['attr'], [
            'v-on:vue:mounted' => 'discriminator = $el.value',
            'v-on:change' => 'discriminator = $event.target.value'
        ]);

        $inner->vars['label'] = false;

        $inner->vars['row_attr'] = array_merge($discriminator->vars['row_attr'], [
            'v-cloak' => null,
        ]);

        foreach ($union as $discriminator => $config) {
            $case = $inner->children[$discriminator];

            $case->vars['row_attr'] = array_merge($case->vars['row_attr'], [
                'v-show' => sprintf('discriminator === %s', json_encode($discriminator)),
            ]);
        }
    }
}
```
