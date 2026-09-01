<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        if ($this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return new RedirectResponse($this->urlGenerator->generate('app_blog_index'));
        } else {
            return new RedirectResponse($this->urlGenerator->generate('app_login_redirect'));
        }

        $request->getSession()->getFlashBag()->add('warning', 'Недостаточно прав');

        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
}
