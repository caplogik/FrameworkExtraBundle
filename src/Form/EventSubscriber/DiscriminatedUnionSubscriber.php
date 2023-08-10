<?php declare(strict_types=1);

namespace Caplogik\FrameworkExtraBundle\Form\EventSubscriber;

use Caplogik\FrameworkExtraBundle\DiscriminatedUnionUtils;
use Caplogik\FrameworkExtraBundle\Form\Type\DiscriminatedUnionType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\ClearableErrorsInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\ConstraintViolation;

final class DiscriminatedUnionSubscriber implements EventSubscriberInterface
{
    /** @var DiscriminatedUnionUtils */
    private $discriminatedUnionUtils;

    public function __construct(DiscriminatedUnionUtils $discriminatedUnionUtils)
    {
        $this->discriminatedUnionUtils = $discriminatedUnionUtils;
    }


    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SUBMIT => ['remapRootViolations', -1],
        ];
    }

    // Try to remap all root violations after form validation
    // Without this all violations within DiscriminatedUnionType are not correctly mapped to their fields
    // `error_bubbling` and `error_mapping` form configuration options are not enough for this use case
    // Could be replaced by a dynamic violation mapping API like https://github.com/symfony/symfony/issues/40213
    public function remapRootViolations(FormEvent $event)
    {
        $form = $event->getForm();

        // This event listener need the whole form to works correctly
        if (!$form->isRoot()) {
            return;
        }

        // Remapped errors needs to removed form the root form and this is the only API allowing that
        // This is not part of FormInterface but implemented in symfony by Form so :shrug:
        // Weird but at least it *should* never throw
        if (!$form instanceof ClearableErrorsInterface) {
            throw new \RuntimeException(sprintf(
                'Form needs to implements `%s` to be usable with `%s`',
                ClearableErrorsInterface::class,
                DiscriminatedUnionType::class
            ));
        }

        // We clear all errors at the end, root errors not caused by discriminated unions are kept here to be added back to the form
        $ignoredErrors = [];

        foreach ($form->getErrors() as $error) {
            $cause = $error->getCause();

            // ConstraintViolation are required to have access to a propertyPath
            if (!$cause instanceof ConstraintViolation) {
                $ignoredErrors[] = $error;
                continue;
            }

            $propertyPath = $cause->getPropertyPath();
            $properties = array_slice(explode('.', $propertyPath), 1);
            $iteratedForm = $form;

            // Iterate the form tree using the properties of the violation
            foreach ($properties as $property) {
                // If the form is a DiscriminatedUnion the iterator is advanced to the correct inner form
                if ($this->discriminatedUnionUtils->isDiscriminatedUnionType($iteratedForm)) {
                    $discriminator = $iteratedForm->get(DiscriminatedUnionType::FIELD_DISCRIMINATOR)->getData();

                    $iteratedForm = $iteratedForm
                        ->get(DiscriminatedUnionType::FIELD_INNER)
                        ->get($discriminator);
                }

                // If the form tree cannot be iterated futher we ignore the error
                if (!$iteratedForm->has($property)) {
                    $ignoredErrors[] = $error;
                    break;
                }

                $iteratedForm = $iteratedForm->get($property);
            }
            $iteratedForm->addError($error);
        }

        // Remove all errors
        $form->clearErrors();

        // Add back all ignored errors
        foreach ($ignoredErrors as $ignoredError) {
            $form->addError($ignoredError);
        }
    }
}
