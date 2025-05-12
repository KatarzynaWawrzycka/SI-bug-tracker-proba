<?php

/**
 * Tag voter.
 */

namespace App\Security\Voter;

use App\Entity\Tag;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class TagVoter.
 */
final class TagVoter extends Voter
{
    public const DELETE = 'TAG_DELETE';
    public const EDIT = 'TAG_EDIT';
    public const SHOW = 'TAG_SHOW';

    /**
     * @param string $attribute
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::DELETE, self::EDIT, self::SHOW], true)
            && $subject instanceof Tag;
    }

    /**
     * @param string         $attribute
     * @param mixed          $subject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::EDIT, self::DELETE, self::SHOW => $this->isAdmin($user),
            default => false,
        };
    }

    /**
     * Check if user has ROLE_ADMIN.
     * @param UserInterface $user
     *
     * @return bool
     */
    private function isAdmin(UserInterface $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
}
