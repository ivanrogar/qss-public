<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exception\Client\RequestException;
use App\Security\Token\ApiToken;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class KernelRequestSubscriber implements EventSubscriberInterface
{
    private TokenStorageInterface $tokenStorage;
    private RouterInterface $router;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        RouterInterface $router
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->router = $router;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::EXCEPTION => 'onKernelException'];
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof RequestException && $exception->getCode() === 401) {
            $token = $this->tokenStorage->getToken();

            if ($token instanceof ApiToken) {
                $this->tokenStorage->setToken(null);

                $event->setResponse(
                    new RedirectResponse($this->router->generate('user_login'))
                );
            }
        }
    }
}
