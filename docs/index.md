# Caplogik

## PetiteVue extension

```php
<?php declare(strict_types=1);

namespace App\Form\Extension;

use Caplogik\FrameworkExtraBundle\Form\Type\PolymorphFormType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

final class PolymorphFormPetiteVueExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [PolymorphFormType::class];
    }

    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        $mapping = $options['mapping'];

        $discriminator = $view->children[PolymorphFormType::FIELD_DISCRIMINATOR];
        $inner = $view->children[PolymorphFormType::FIELD_INNER];

        $view->vars['attr']['v-scope'] = '{ discriminator: null }';

        $discriminator->vars['attr'] = array_merge($discriminator->vars['attr'], [
            'v-on:vue:mounted' => 'discriminator = $el.value',
            'v-on:change' => 'discriminator = $event.target.value'
        ]);

        $inner->vars['row_attr'] = array_merge($discriminator->vars['row_attr'], [
            'v-cloak' => null,
        ]);

        foreach ($mapping as $discriminator => $config) {
            $case = $inner->children[$discriminator];

            $case->vars['row_attr'] = array_merge($case->vars['row_attr'], [
                'v-show' => sprintf('discriminator === %s', json_encode($discriminator)),
            ]);
        }
    }
}
```
