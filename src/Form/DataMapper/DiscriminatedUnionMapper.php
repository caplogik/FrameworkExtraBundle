<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\DataMapper;

use Caplogik\FrameworkExtraBundle\Form\Type\DiscriminatedUnionType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception\InvalidArgumentException;

class DiscriminatedUnionMapper implements DataMapperInterface
{
    /** @var array */
    private $union;

    /** @var callable */
    private $discriminatorFrom;

    public function __construct(array $union, callable $discriminatorFrom)
    {
        $this->union = $union;
        $this->discriminatorFrom = $discriminatorFrom;
    }

    public function mapDataToForms($viewData, $forms): void
    {
        if (null === $viewData) {
            return;
        }

        $discriminator = ($this->discriminatorFrom)($viewData);
        $discriminators = array_keys($this->union);

        if (!in_array($discriminator, $discriminators, true)) {
            throw new InvalidArgumentException(sprintf(
                'Expected discrimator of value %s, "%s" given.',
                implode(
                    '|',
                    array_map(function ($x) { return '"' . $x . '"'; }, $discriminators)
                ),
                $discriminator
            ));
        }

        $forms = iterator_to_array($forms);
        $forms[DiscriminatedUnionType::FIELD_DISCRIMINATOR]->setData($discriminator);
        $forms[DiscriminatedUnionType::FIELD_INNER]->setData([$discriminator => $viewData]);
    }

    public function mapFormsToData($forms, &$viewData): void
    {
        $forms = iterator_to_array($forms);
        $discriminator = $forms[DiscriminatedUnionType::FIELD_DISCRIMINATOR]->getData();

        if ($discriminator === null) {
            $viewData = null;
        } else {
            $viewData = $forms[DiscriminatedUnionType::FIELD_INNER][$discriminator]->getData();
        }
    }
}
