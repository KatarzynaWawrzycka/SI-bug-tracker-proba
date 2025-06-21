<?php

/**
 * Category voter.
 */

namespace App\Security\Voter;

use App\Entity\Category;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class CategoryVoter.
 */
final class CategoryVoter extends Voter
{
    /**
     * Delete permission.
     *
     * @const string
     */
    public const DELETE = 'CATEGORY_DELETE';

    /**
     * Edit permission.
     *
     * @const string
     */
    public const EDIT = 'CATEGORY_EDIT';

    /**
     * Show permission.
     *
     * @const string
     */
    public const SHOW = 'CATEGORY_SHOW';

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
        return in_array($attribute, [self::DELETE, self::EDIT, self::SHOW], true)
            && $subject instanceof Category;
    }

    /**
     * @param string         $attribute Attribute
     * @param mixed          $subject   Subject
     * @param TokenInterface $token     Token
     *
     * @return bool Result
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
