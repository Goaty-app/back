<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if ($exception instanceof UnprocessableEntityHttpException) {
            $data = [
                'status'     => $exception->getStatusCode(),
                'message'    => $this->translator->trans('exception.unprocessable.entity'),
                'violations' => $this->populateviolations($exception),
            ];
        } elseif ($exception instanceof NotFoundHttpException) {
            $data = [
                'status'  => $exception->getStatusCode(),
                'message' => $this->translator->trans('exception.not_found'),
            ];
        } elseif ($exception instanceof HttpException) {
            $data = [
                'status'  => $exception->getStatusCode(),
                'message' => $exception->getMessage(),
            ];
        } else {
            $data = [
                'status'  => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $this->translator->trans('exception.internal.server.error'),
            ];
        }

        $event->setResponse(new JsonResponse($data, $data['status']));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    private function populateViolations(UnprocessableEntityHttpException $exception): array
    {
        $previous = $exception->getPrevious();

        if (!$previous instanceof ValidationFailedException) {
            return [];
        }

        return array_map(
            static fn ($violation) => [
                'field'   => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ],
            [...$previous->getViolations()],
        );
    }
}
