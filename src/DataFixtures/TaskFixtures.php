<?php

namespace App\DataFixtures;

use App\Entity\Task;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class TaskFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // task user
        for ($nbrTask = 1; $nbrTask < 10; $nbrTask++) {
            $task = new Task();
            $task->setTitle('tache numero : ' . $nbrTask)
                ->setContent('Contenu de la tâche numéro : ' . $nbrTask)
                ->setCreatedAt(new DateTimeImmutable());

            if ($nbrTask >= 4 and $nbrTask < 7) {
                $task->setAuthor($this->getReference('user' . '1'));
            }
            if ($nbrTask >= 7 and $nbrTask < 10) {
                $task->setAuthor($this->getReference('user' . '2'));
            }
            if ($nbrTask == 5) {
                $task->setIsDone(1);
            }

            $manager->persist($task);
        }

        // task admin
        for ($nbrTask = 10; $nbrTask < 14; $nbrTask++) {
            $task = new Task();
            $task->setTitle('tache numero : ' . $nbrTask)
                ->setContent('Contenu de la tâche numéro : ' . $nbrTask)
                ->setCreatedAt(new DateTimeImmutable())
                ->setAuthor($this->getReference('admin'));

            $manager->persist($task);
        }


        $manager->flush();
    }

    // returns the list of our fixture dependencies for this fixture
    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}
