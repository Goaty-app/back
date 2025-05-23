<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

// use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if ($exception instanceof HttpException) {
            $data = [
                'status'  => $exception->getStatusCode(),
                'message' => $exception->getMessage(),
            ];
        } else {
            $data = [
                'status'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => 'Internal server error',
            ];
        }

        $event->setResponse(new JsonResponse($data));
    }

    public static function getSubscribedEvents(): array
    {
        // todo: uncomment this code for this to work
        return [
            // KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
