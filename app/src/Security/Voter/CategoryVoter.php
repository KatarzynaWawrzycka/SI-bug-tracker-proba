<?php

namespace App\Security\Voter;

use App\Entity\Category;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class CategoryVoter extends Voter
{
    public const DELETE = 'CATEGORY_DELETE';
    public const EDIT = 'CATEGORY_EDIT';
    public const SHOW = 'CATEGORY_SHOW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::DELETE, self::EDIT, self::SHOW], true)
            && $subject instanceof Category;
    }

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
     * @param UserInterface $user
     * @return bool
     */
    private function isAdmin(UserInterface $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
}
