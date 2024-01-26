<?php

namespace App\EventSubscriber;

use App\Exception\DatabaseException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ExceptionSubscriber
 *
 * This class handles exceptions thrown during the application's lifecycle.
 * It listens to the KernelEvents::EXCEPTION event and customizes the response based on the exception type.
 */

class ExceptionSubscriber implements EventSubscriberInterface
{
    /**
     * Handles the kernel.exception event.
     *
     * @param ExceptionEvent $event The exception event.
     */

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof HttpException)
        {
            // Check if the exception is an HttpException
            $data = [
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage(),
            ];
            $event->setResponse(new JsonResponse($data));
        } elseif ($exception instanceof DatabaseException)
        {
            // Check if the exception is a custom DatabaseException
            $data = [
                'status' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ];
            $event->setResponse(new JsonResponse($data));
        } else {
            // For other exceptions, set a generic response
            $data = [
                'status' => 500,
                'message' => $exception->getMessage(),
            ];
            $event->setResponse(new JsonResponse($data));
        }
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onKernelException',
        ];
    }
}
