<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: KernelEvents::EXCEPTION)]
class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        if (!str_starts_with($event->getRequest()->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();

        $validationFailed = match (true) {
            $exception instanceof ValidationFailedException => $exception,
            $exception->getPrevious() instanceof ValidationFailedException => $exception->getPrevious(),
            default => null,
        };

        if (null !== $validationFailed) {
            $errors = [];
            foreach ($validationFailed->getViolations() as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }

            $event->setResponse(new JsonResponse(
                ['message' => 'Validation failed.', 'errors' => $errors],
                422,
            ));

            return;
        }

        if ($exception instanceof HttpExceptionInterface) {
            $event->setResponse(new JsonResponse(
                ['message' => $exception->getMessage()],
                $exception->getStatusCode(),
                $exception->getHeaders(),
            ));
        }
    }
}
