<?php

namespace App\Tests\services\security;

use App\Services\tools\MailerService;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mailer\Exception\TransportException;

class MailerServiceTest extends WebTestCase
{
    private MailerInterface|MockObject $mailerMock;

    private MailerService $mailerService;
    private array $params = [];

    public function setUp(): void
    {
        $this->mailerMock = $this->createMock(MailerInterface::class);

        $this->mailerService = new MailerService($this->mailerMock, 'test@test.test');
        $this->params = [
            'email' => 'mickael.sayer.dev@gmail.com',
            'subject' => "Test email",
            'template' => 'teste/test.html.twig',
            'context' => [
                'title' => 'Test title',
                'descs' => ["Test desc"]
            ]
        ];
    }

    /**
     * test the sending of an email with errors
     */
    public function testSendTemplateEmailKO(): void
    {
        $this->mailerMock
            ->expects($this->once())
            ->method('send')
            ->willThrowException(
                new TransportException(
                    "Ah... je n'ai pas réussi à envoyer l'e-mail pour confirmer ton adresse. Réessaie plus tard"
                )
            );

        $this->expectException(TransportException::class);
        $this->expectExceptionMessage(
            "Ah... je n'ai pas réussi à envoyer l'e-mail pour confirmer ton adresse. Réessaie plus tard"
        );

        $this->mailerService->sendTemplateEmail($this->params);
    }
}
