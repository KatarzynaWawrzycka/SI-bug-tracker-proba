<?php

namespace App\DataFixtures;

use App\Entity\Comment;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class CommentFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    public function loadData(): void
    {
        $this->createMany(150, 'comments', function (int $i) {
            $comment = new Comment();

            $comment->setContent($this->faker->text(120));
            $comment->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-10 days')
                )
            );
            $comment->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-10 days', 'now')
                )
            );

            $comment->setAuthor($this->getRandomReference('user'));
            $comment->setBug($this->getRandomReference('bugs'));

            return $comment;
        });

        $this->manager->flush();
    }

    public function getDependencies(): array
    {
        return [BugFixtures::class, UserFixtures::class];
    }
}
