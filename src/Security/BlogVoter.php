<?php
namespace App\Security;

use App\Entity\Blog;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class BlogVoter extends Voter
{
    const VIEW = 'view';
    const EDIT = 'edit';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT])) {
            return false;
        }

        if (!$subject instanceof Blog) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            $vote?->addReason('The user is not logged in.');
            return false;
        }

        /** @var Blog $blog */
        $blog = $subject;

        return match($attribute) {
            self::VIEW => $this->canView($blog, $user),
            self::EDIT => $this->canEdit($blog, $user, $vote),
            default => throw new \LogicException('This code should not be reached!')
        };
    }

    private function canView(Blog $blog, User $user): bool
    {
        if ($this->canEdit($blog, $user)) {
            return true;
        }

        return true;
    }

    private function canEdit(Blog $blog, User $user, ?Vote $vote = null): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        if ($user !== $blog->getUser()) {
            $vote?->addReason('You can only edit your own blog posts.');

            return false;
        }

        return true;
    }
}
