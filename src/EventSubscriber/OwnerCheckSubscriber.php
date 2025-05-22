<?php

namespace App\EventSubscriber;

use App\Entity\Animal;
use App\Entity\Herd;
use App\Entity\Interface\HasOwner;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class OwnerCheckSubscriber implements EventSubscriberInterface
{
    private const ENTITY_PARAM_MAP = [
        'herd'   => Herd::class,
        'animal' => Animal::class,
    ];

    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onController',
        ];
    }

    public function onController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        $attributes = $request->attributes;

        foreach (self::ENTITY_PARAM_MAP as $paramName => $entityClass) {
            if (!$attributes->has($paramName)) {
                continue;
            }

            $paramValue = $attributes->get($paramName);

            if (!is_numeric($paramValue)) {
                continue;
            }

            $entity = $this->entityManager->getRepository($entityClass)->find($paramValue);

            if (!$entity) {
                continue;
            }

            if ($entity instanceof HasOwner) {
                $this->checkOwnership($entity);
            }

            break;
        }
    }

    private function checkOwnership(HasOwner $entity): void
    {
        /** @var User $user */
        $user = $this->security->getUser();

        if (!$user || $entity->getOwner()?->getId() !== $user->getId()) {
            throw new NotFoundHttpException();
        }
    }
}
