<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class MailService
{
    private MailerInterface $mailer;
    private Environment $twig;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    /**
     * Envoie l'email de la newsletter hebdomadaire à un destinataire.
     *
     * @param string $recipientEmail L'email du destinataire.
     * @param array $games La liste des jeux à inclure dans l'email.
     */
    public function sendWeeklyNewsletter(string $recipientEmail, array $games): void
    {
        $htmlContent = $this->twig->render('email/videoGameEmail.html.twig', [
            'games' => $games,
        ]);

        $email = (new Email())
            ->from('newsletter@jeuvideo.com')
            ->to($recipientEmail)
            ->subject('Les sorties jeux vidéo de la semaine !')
            ->html($htmlContent);

        $this->mailer->send($email);
    }
}
