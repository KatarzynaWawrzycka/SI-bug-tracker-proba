<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserVoter extends Voter
{
    public const INDEX = 'USER_INDEX';
    public const SHOW = 'USER_SHOW';
    public const EDIT_EMAIL = 'USER_EDIT_EMAIL';
    public const EDIT_PASSWORD = 'USER_EDIT_PASSWORD';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::INDEX,
            self::SHOW,
            self::EDIT_EMAIL,
            self::EDIT_PASSWORD,
        ], true) && ($subject instanceof User || null === $subject);
    }

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

    private function isAdmin(UserInterface $user): bool
    {
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
}
