<?php

/**
 * User voter.
 */

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserVoter.
 */
final class UserVoter extends Voter
{
    /**
     * Index permission.
     *
     * @const string
     */
    public const INDEX = 'USER_INDEX';

    /**
     * Show permission.
     *
     * @const string
     */
    public const SHOW = 'USER_SHOW';

    /**
     * Edit email permission.
     *
     * @const string
     */
    public const EDIT_EMAIL = 'USER_EDIT_EMAIL';

    /**
     * Edit password permission.
     *
     * @const string
     */
    public const EDIT_PASSWORD = 'USER_EDIT_PASSWORD';

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
        return in_array($attribute, [
            self::INDEX,
            self::SHOW,
            self::EDIT_EMAIL,
            self::EDIT_PASSWORD,
        ], true) && ($subject instanceof User || null === $subject);
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

        return match ($attribute) {
            self::INDEX,
            self::SHOW,
            self::EDIT_EMAIL,
            self::EDIT_PASSWORD => $this->isAdmin($user),
            default => false,
        };
    }

    /**
     * Check if user has ROLE_ADMIN.
     *
     * @param UserInterface $user User
     *
     * @return bool Result
     */
    private function isAdmin(UserInterface $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
}
