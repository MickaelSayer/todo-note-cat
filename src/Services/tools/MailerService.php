<?php

namespace App\Services\tools;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MailerService
{
    private MailerInterface $mailer;
    private string $adress_mail;

    public function __construct(MailerInterface $mailer, string $adress_mail)
    {
        $this->mailer = $mailer;
        $this->adress_mail = $adress_mail;
    }

    /**
     * Sending the email
     *
     * @param array $params['email'] RECIPIENT
     * @param string $params['subject'] Mail subject
     * @param string $params['template'] Template Twig used
     * @param array $params['context'] The data to be sent to the template
     *
     * @return void
     * @throws TransportException
     */
    public function sendTemplateEmail(array $params): void
    {
        try {
            $email = (new TemplatedEmail())
                ->from($this->adress_mail)
                ->to($params['email'])
                ->subject($params['subject'])
                ->htmlTemplate($params['template'])
                ->context($params['context']);

            $this->mailer->send($email);
        } catch (TransportException $e) {
            throw new TransportException(
                "Ah... je n'ai pas réussi à envoyer l'e-mail pour confirmer ton adresse. Réessaie plus tard."
            );
        }
    }
}
