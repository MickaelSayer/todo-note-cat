<?php

namespace App\Command;

use DateTime;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteInvalidUserCommand extends Command
{
    private EntityManagerInterface $entityManager;

    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $entityManager, UserRepository $userRepository)
    {
        parent::__construct();

        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
    }

    protected function configure(): void
    {
        $this
            ->setName('delete:invalid-user')
            ->setDescription('Delete users who have created an account but have not been validated after 24 hours');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dateThreshold = new DateTime('-1 hours');
        $usersToDelete = $this->userRepository->findInvalidUserAfter24Hours($dateThreshold);

        foreach ($usersToDelete as $user) {
            $this->entityManager->remove($user);
            $io->success(sprintf('Utilisateur supprimé : %s', $user->getEmail()));
        }

        $this->entityManager->flush();

        $io->success('Suppression des utilisateurs non validés terminée.');

        return Command::SUCCESS;
    }
}
