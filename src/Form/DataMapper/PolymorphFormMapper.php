<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\DataMapper;

use Caplogik\FrameworkExtraBundle\Form\Type\PolymorphFormType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class PolymorphFormMapper implements DataMapperInterface
{
    /** @var array */
    private $mapping;

    public function __construct(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function mapDataToForms($viewData, $forms): void
    {
        if (null === $viewData) {
            return;
        }

        $forms = iterator_to_array($forms);

        foreach ($this->mapping as $discriminator => $config) {
            if ($config['class'] === get_class($viewData)) {

                $forms[PolymorphFormType::FIELD_DISCRIMINATOR]->setData($discriminator);

                $forms[PolymorphFormType::FIELD_INNER]->setData([
                    $discriminator => $viewData
                ]);

                return;
            }
        }

        $classes = implode('|', iterator_to_array((function () {
            foreach ($this->mapping as $config) yield $config['class'];
        })()));

        throw new UnexpectedTypeException($viewData, $classes);
    }

    public function mapFormsToData($forms, &$viewData): void
    {
        $forms = iterator_to_array($forms);
        $discriminator = $forms[PolymorphFormType::FIELD_DISCRIMINATOR]->getData();
        $viewData = $forms[PolymorphFormType::FIELD_INNER]->getData()[$discriminator];
    }
}
