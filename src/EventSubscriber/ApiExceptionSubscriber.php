<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();

        if ($exception instanceof \InvalidArgumentException) {
            $decoded = json_decode($exception->getMessage(), true);
            $event->setResponse(new JsonResponse([
                'message' => 'Validation failed.',
                'errors' => is_array($decoded) ? $decoded : ['form' => [$exception->getMessage()]],
            ], 422));

            return;
        }

        if ($exception instanceof \JsonException) {
            $event->setResponse(new JsonResponse([
                'message' => 'Invalid JSON payload.',
            ], 400));

            return;
        }

        if ($exception instanceof HttpExceptionInterface) {
            $event->setResponse(new JsonResponse([
                'message' => $exception->getMessage() ?: JsonResponse::$statusTexts[$exception->getStatusCode()],
            ], $exception->getStatusCode()));

            return;
        }

        $event->setResponse(new JsonResponse([
            'message' => 'Internal server error.',
        ], 500));
    }
}
