<?php

namespace App\EventSubscriber;

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
    private const ENTITY_NAMESPACE = 'App\\Entity\\';
    private const SKIP_PREFIX = '_';

    // // Other Solution, much simpler
    // private const ALLOWED_PARAMS = [
    //     'herd',
    // ];

    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $em,
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
        $routeParams = $event->getRequest()->attributes->all();

        foreach ($routeParams as $paramName => $paramValue) {
            if ($this->shouldSkipParam($paramName, $paramValue)) {
                continue;
            }

            $entity = $this->tryGetEntity($paramName, $paramValue);

            if ($entity instanceof HasOwner) {
                $this->checkOwnership($entity);
            }
        }
    }

    private function shouldSkipParam(string $paramName, $paramValue): bool
    {
        return str_starts_with($paramName, self::SKIP_PREFIX) || !is_numeric($paramValue);
    }

    private function tryGetEntity(string $paramName, $paramValue)
    {
        $entityClass = $this->getEntityClass($paramName);

        if (!class_exists($entityClass)) {
            return null;
        }

        return $this->em->getRepository($entityClass)->find($paramValue);
    }

    private function getEntityClass(string $paramName): string
    {
        return self::ENTITY_NAMESPACE.ucfirst($paramName);
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
