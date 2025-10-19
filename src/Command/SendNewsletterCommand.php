<?php

namespace App\Command;

use App\Repository\UserRepository;
use App\Repository\VideoGameRepository;
use App\Service\MailService;
use DateTime;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'SendNewsletterCommand', //NOM DE LA COMMANDE A METTRE POUR ENVOYER LE MAIL AVEC php bin/console ****
    description: 'Envoie la newsletter aux abonnés avec les jeux qui sortent dans les 7 prochains jours.',
)]
class SendNewsletterCommand extends Command
{
    private MailService $mailService;
    private UserRepository $userRepository;
    private VideoGameRepository $videoGameRepository;
    public function __construct(MailService $mailService, UserRepository $userRepository, VideoGameRepository $videoGameRepository)
    {
        parent::__construct();
        $this->mailService = $mailService;
        $this->userRepository = $userRepository;
        $this->videoGameRepository = $videoGameRepository;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Récupération des jeux à venir...');

        $today = new DateTime();
        $nextWeek = (clone $today)->modify('+7 days');

        $upcomingGames = $this->videoGameRepository->createQueryBuilder('g')
            ->where('g.release_date BETWEEN :today AND :nextWeek')
            ->setParameter('today', $today->format('Y-m-d'))
            ->setParameter('nextWeek', $nextWeek->format('Y-m-d'))
            ->orderBy('g.release_date', 'ASC')
            ->getQuery()
            ->getResult();

        if (empty($upcomingGames)) {
            $io->warning('Aucun jeu ne sort dans les 7 prochains jours. Aucun email envoyé.');
            return Command::SUCCESS;
        }

        $io->text(count($upcomingGames) . ' jeux trouvés.');

        $subscribers = $this->userRepository->findBy(['subscription_to_newsletter' => true]);

        if (empty($subscribers)) {
            $io->warning('Aucun utilisateur abonné à la newsletter.');
            return Command::SUCCESS;
        }

        $io->section('Envoi des emails...');
        
        $mailtrapAddress = 'adrien@test.com';

        try {
            $this->mailService->sendWeeklyNewsletter($mailtrapAddress, $upcomingGames);
            $io->success('Email envoyé avec succès à Mailtrap !');
        } catch (\Throwable $e) {
            $io->error('Erreur lors de l’envoi du mail : ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
