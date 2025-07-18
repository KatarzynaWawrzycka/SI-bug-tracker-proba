<?php

/**
 * Bug fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Bug;
use App\Entity\Category;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

/**
 * Class BugFixtures.
 */
class BugFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullPropertyFetch
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (!$this->manager instanceof ObjectManager || !$this->faker instanceof Generator) {
            return;
        }

        $this->createMany(100, 'bugs', function (int $i) {
            $bug = new Bug();
            $bug->setTitle($this->faker->sentence);
            $bug->setDescription($this->faker->text);
            $bug->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            $bug->setUpdatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-100 days', '-1 days')
                )
            );
            /** @var Category $category */
            $category = $this->getRandomReference('categories');
            $bug->setCategory($category);

            $randomTags = $this->getRandomReferences('tags', random_int(2, 3));
            foreach ($randomTags as $tag) {
                $bug->addTag($tag);
            }

            /** @var \App\Entity\User $author */
            $author = $this->getRandomReference('user');
            $bug->setAuthor($author);

            /** @var \App\Entity\User $assignedTo */
            $assignedTo = $this->getRandomReference('user');
            $bug->setAssignedTo($assignedTo);

            $isArchived = $this->faker->boolean(30);
            $isClosed = $isArchived || $this->faker->boolean(30);

            $bug->setIsArchived($isArchived);
            $bug->setIsClosed($isClosed);

            return $bug;
        });

        $this->manager->flush();
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: CategoryFixtures::class}
     */
    public function getDependencies(): array
    {
        return [CategoryFixtures::class, TagFixtures::class, UserFixtures::class];
    }
}
