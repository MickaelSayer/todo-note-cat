<?php

namespace App\DataFixtures;

use App\Entity\Note;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TodolistFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $this->addUsers($manager);

        $manager->flush();
    }

    private function addUsers(ObjectManager $manager): void
    {
        $user1 = new User();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user1,
            '123456789'
        );
        $user1->setPassword($hashedPassword)
              ->setEmail('mickael.sayer.dev@gmail.com')
              ->setValid(1);
        $manager->persist($user1);

        $user2 = new User();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user2,
            '987654321'
        );
        $user2->setPassword($hashedPassword)
              ->setEmail('m-iicka86@hotmail.fr');
        $manager->persist($user2);

        $user3 = new User();
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user3,
            '159753'
        );
        $user3->setPassword($hashedPassword)
              ->setEmail('test-sans-note@gmail.com')
              ->setValid(1);
        $manager->persist($user3);

        $this->addNotes($manager, [$user1, $user2]);

        $manager->flush();
    }

    private function addNotes(ObjectManager $manager, array $users): void
    {
        $note1 = new Note();
        $note1->setUser($users[0])
               ->setTitle('List of races');
        $manager->persist($note1);

        $note2 = new Note();
        $note2->setUser($users[0])
               ->setTitle('Development Project');
        $manager->persist($note2);

        $note3 = new Note();
        $note3->setUser($users[0])
               ->setTitle('morning routine');
        $manager->persist($note3);

        $note4 = new Note();
        $note4->setUser($users[1])
               ->setTitle('spring cleaning');
        $manager->persist($note4);

        $note5 = new Note();
        $note5->setUser($users[1])
               ->setTitle('Travel planning');
        $manager->persist($note5);

        $this->addTasks($manager, [$note1, $note2, $note3, $note4, $note5]);
    }

    private function addTasks(ObjectManager $manager, array $Note): void
    {
        $task1 = new Task();
        $task1->setNote($Note[0])
                  ->setDescription('Buy milk')
                  ->setChecked(true);
        $manager->persist($task1);

        $task2 = new Task();
        $task2->setNote($Note[1])
            ->setDescription('Implement X functionality')
            ->setChecked(true);
        $manager->persist($task2);

        $task21 = new Task();
        $task21->setNote($Note[1])
            ->setDescription('Write documentation')
            ->setChecked(true);
        $manager->persist($task21);

        $task22 = new Task();
        $task22->setNote($Note[1])
            ->setDescription('To exercise');
        $manager->persist($task22);

        $task3 = new Task();
        $task3->setNote($Note[2])
            ->setDescription('To exercice')
            ->setChecked(true);
        $manager->persist($task3);

        $task31 = new Task();
        $task31->setNote($Note[2])
            ->setDescription('Take a shower')
            ->setChecked(true);
        $manager->persist($task31);

        $task32 = new Task();
        $task32->setNote($Note[2])
            ->setDescription('Have breakfast');
        $manager->persist($task32);

        $task33 = new Task();
        $task33->setNote($Note[2])
            ->setDescription('To buy vegetables');
        $manager->persist($task33);

        $task4 = new Task();
        $task4->setNote($Note[3])
            ->setDescription('Wash the windows');
        $manager->persist($task4);

        $task41 = new Task();
        $task41->setNote($Note[3])
            ->setDescription('To vacuum');
        $manager->persist($task41);

        $task42 = new Task();
        $task42->setNote($Note[3])
            ->setDescription('Organize the closet');
        $manager->persist($task42);

        $task5 = new Task();
        $task5->setNote($Note[4])
            ->setDescription('Book plane tickets');
        $manager->persist($task5);

        $task51 = new Task();
        $task51->setNote($Note[4])
            ->setDescription('book the hotel');
        $manager->persist($task51);
    }
}
