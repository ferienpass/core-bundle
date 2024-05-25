<?php

declare(strict_types=1);

/*
 * This file is part of the Ferienpass package.
 *
 * (c) Richard Henkenjohann <richard@ferienpass.online>
 *
 * For more information visit the project website <https://ferienpass.online>
 * or the documentation under <https://docs.ferienpass.online>.
 */

namespace Ferienpass\CoreBundle\Payments\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Ferienpass\CoreBundle\Entity\Payment;
use Ferienpass\CoreBundle\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PmPaymentProvider implements PaymentProviderInterface
{
    public function __construct(private readonly HttpClientInterface $pmPaymentClient, private readonly EventDispatcherInterface $dispatcher, private readonly EntityManagerInterface $entityManager, private readonly UrlGeneratorInterface $urlGenerator, #[Autowire(env: 'PMPAYMENT_AGS')] private readonly string $ags, #[Autowire(env: 'PMPAYMENT_PROCEDURE')] private readonly string $procedure, #[Autowire(env: 'PMPAYMENT_SALT')] private readonly string $salt, private readonly RequestStack $requestStack)
    {
    }

    public function initializePayment(array $attendances, User $user): string
    {
        $payment = Payment::fromAttendances($attendances, $this->dispatcher, user: $user);

        $values = [
            'ags' => $this->ags,
            'amount' => $payment->getTotalAmount(),
            'procedure' => $this->procedure,
            'desc' => preg_replace('/[^a-zA-Z0-9\' ?.,\-()+\/]/', '', "Ferienpass {$payment->getId()}"),
            'accountingRecord' => sprintf('%s|%s|%s', $this->procedure, $payment->getId(), preg_replace('/[\r\n]+/', '|', $payment->getBillingAddress())),
            'notifyURL' => 'https://handsome-grass-96.webhook.cool', // $this->urlGenerator->generate('_webhook_controller', ['type' => 'pmPayment'], UrlGeneratorInterface::ABSOLUTE_URL),
            'redirectURL' => $this->urlGenerator->generate('applications', ['step' => 'checkPayment'], UrlGeneratorInterface::ABSOLUTE_URL),
        ];

        $response = $this->pmPaymentClient->request('POST', '/payment/secure', [
            'body' => $values + ['hash' => $this->calculateHash($values)],
        ]);

        $content = $response->toArray();
        if (null === ($redirect = $content['url'] ?? null)) {
            throw new ClientException($response);
        }

        $payment->setPmPaymentTransactionId($content['txid']);

        $this->entityManager->persist($payment);
        $this->entityManager->flush();

        $this->requestStack->getSession()->set('fp.processPayment', $payment->getId());

        return $redirect;
    }

    public function isRedirectSuccessful(Request $request): bool
    {
        $urlParams = [
            'ags' => $request->query->get('ags', ''),
            'txid' => $request->query->get('txid', ''),
            'amount' => $request->query->get('amount', 0),
            'desc' => $request->query->get('desc', ''),
            'status' => $request->query->get('status', ''),
            'payment_method' => $request->query->get('payment_method', ''),
            'created_at' => $request->query->get('created_at', ''),
        ];

        if (!$this->hashIsValid($request->query->get('hash', ''), $urlParams)) {
            return false;
        }

        if ('0' === $urlParams['status']) {
            return false;
        }

        if ('' === $urlParams['payment_method'] && '-1' === $urlParams['status']) {
            return false;
        }

        // Successful here means to wait for approval, not that the payment will be marked as paid
        return true;
    }

    private function calculateHash(array $values): string
    {
        return hash_hmac('sha256', implode('|', $values), $this->salt);
    }

    private function hashIsValid(string $hash, array $values): bool
    {
        return hash_equals($this->calculateHash($values), $hash);
    }
}
