<?php

/**
 *  Class BugVoter.
 */

namespace App\Security\Voter;

use App\Entity\Bug;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 *  Class BugVoter.
 */
final class BugVoter extends Voter
{
    /**
     * Delete permission.
     *
     * @const string
     */
    public const DELETE = 'BUG_DELETE';

    /**
     * Edit permission.
     *
     * @const string
     */
    public const EDIT = 'BUG_EDIT';

    public const COMMENT = 'BUG_COMMENT';

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool Result
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::DELETE, self::EDIT, self::COMMENT])
            && $subject instanceof Bug;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute Permission name
     * @param mixed          $subject   Object
     * @param TokenInterface $token     Security token
     *
     * @return bool Vote result
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }
        if (!$subject instanceof Bug) {
            return false;
        }

        return match ($attribute) {
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::COMMENT => $this->canComment($user),
            default => false,
        };
    }

    /**
     * Checks if user can delete bug.
     *
     * @param Bug           $bug  Bug entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canDelete(Bug $bug, UserInterface $user): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        return $bug->getAuthor() === $user;
    }

    /**
     * Checks if user can edit bug.
     *
     * @param Bug           $bug  Bug entity
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function canEdit(Bug $bug, UserInterface $user): bool
    {
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }

        return $bug->getAuthor() === $user;
    }

    private function canComment(?UserInterface $user): bool
    {
        return $user instanceof UserInterface;
    }
}
