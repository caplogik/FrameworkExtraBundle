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

    // This listener try to remap all root violations after form validation
    // Without this all violations within PolymorphFormType are not correctly mapped
    // Can be replaced by a dynamic violation mapping API like https://github.com/symfony/symfony/issues/40213
    public function remapRootViolations(FormEvent $event)
    {
        $form = $event->getForm();

        if ($form->isRoot()) {
            if (!$form instanceof ClearableErrorsInterface) {
                throw new \RuntimeException('Root form needs to be error clearable to remap violations');
            }

            $ignoredErrors = [];

            foreach ($form->getErrors() as $error) {
                $cause = $error->getCause();

                if (!$cause instanceof ConstraintViolation) {
                    $ignoredErrors[] = $error;
                    continue;
                }

                $propertyPath = $cause->getPropertyPath();
                $properties = array_slice(explode('.', $propertyPath), 1);
                $iteratedForm = $form;

                if ($this->discriminatedUnionUtils->isDiscriminatedUnionType($iteratedForm)) {
                    $discriminator = $iteratedForm->get(DiscriminatedUnionType::FIELD_DISCRIMINATOR)->getData();

                    $iteratedForm = $iteratedForm
                        ->get(DiscriminatedUnionType::FIELD_INNER)
                        ->get($discriminator);
                }


                foreach ($properties as $property) {
                    if (!$iteratedForm->has($property)) {
                        $ignoredErrors[] = $error;
                        break;
                    }

                    $iteratedForm = $iteratedForm->get($property);

                    // If the form is a PolymorphFormType iteratedForm is updated to the valid sub form based on submitted data
                    // if ($iteratedForm->getConfig()->getType()->getInnerType() instanceof PolymorphFormType) {
                    //     $discriminator = $iteratedForm->get(PolymorphFormType::FIELD_DISCRIMINATOR)->getData();

                    //     $iteratedForm = $iteratedForm
                    //         ->get(PolymorphFormType::FIELD_INNER)
                    //         ->get($discriminator);
                    // }
                }

                $iteratedForm->addError($error);
            }

            // Remove all root errors, we don't have a more granular api available
            $form->clearErrors();

            // map again all ignored errors to root form
            foreach ($ignoredErrors as $ignoredError) {
                $form->addError($ignoredError);
            }
        }
    }
}
