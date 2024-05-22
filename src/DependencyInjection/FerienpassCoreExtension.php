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

namespace Ferienpass\CoreBundle\DependencyInjection;

use Ferienpass\CoreBundle\Entity\Offer;
use Ferienpass\CoreBundle\Entity\Offer\OfferInterface;
use Ferienpass\CoreBundle\Entity\Participant;
use Ferienpass\CoreBundle\Export\Offer\PrintSheet\PdfExports;
use Ferienpass\CoreBundle\Export\Offer\Xml\XmlExports;
use Ferienpass\CoreBundle\Export\ParticipantList\WordExport;
use Ferienpass\CoreBundle\Messenger\MessageLogMiddleware;
use Ferienpass\CoreBundle\Repository\OfferRepositoryInterface;
use Ferienpass\CoreBundle\Repository\ParticipantRepositoryInterface;
use Ferienpass\CoreBundle\Repository\ResetPasswordRequestRepository;
use Ferienpass\CoreBundle\Webhook\PmPaymentRequestParser;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class FerienpassCoreExtension extends Extension implements PrependExtensionInterface
{
    use PersistenceExtensionTrait;

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.php');

        $this->configurePersistence($config['entities'], $container);
        $container->addAliases([
            OfferRepositoryInterface::class => 'ferienpass.repository.offer',
            ParticipantRepositoryInterface::class => 'ferienpass.repository.participant',
        ]);

        // Parameters
        $container->setParameter('ferienpass.logos_dir', $config['logos_dir']);
        $container->setParameter('ferienpass.images_dir', $config['images_dir']);

        $expressionLanguage = new ExpressionLanguage();
        $container->setParameter('ferienpass.receipt_number_prefix', null === $config['receipt_number_prefix'] ? '' : $expressionLanguage->evaluate($config['receipt_number_prefix'], ['date' => new \DateTimeImmutable()]));

        // Injection
        if (isset($config['export'])) {
            $pdfConfigs = $container->getDefinition(PdfExports::class);
            foreach ($config['export']['pdf'] as $configName => $pdfConfig) {
                $pdfConfigs->addMethodCall('addConfig', [$configName, $pdfConfig]);
            }

            $xmlExports = $container->getDefinition(XmlExports::class);
            foreach ($config['export']['xml'] as $configName => $template) {
                $xmlExports->addMethodCall('addTemplate', [$configName, $template]);
            }
        }

        $docxParticipantList = $container->getDefinition(WordExport::class);
        $docxParticipantList->setArgument(2, $config['participant_list']['docx_template'] ?? null);
    }

    public function getAlias(): string
    {
        return 'ferienpass';
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('framework', [
            'mailer' => [
                'envelope' => [
                    'sender' => '%env(ADMIN_EMAIL)%',
                ],
            ],
            'webhook' => [
                'routing' => [
                    'pmPayment' => [
                        'service' => PmPaymentRequestParser::class,
                    ],
                ],
            ],
            'http_client' => [
                'scoped_clients' => [
                    'pmPayment.client' => [
                        'base_uri' => 'https://payment-test.govconnect.de',
                    ],
                ],
            ],
        ]);

        // TODO only register if NOT customized
        $container->prependExtensionConfig('doctrine', [
            'orm' => [
                'entity_managers' => [
                    'default' => [
                        'schema_ignore_classes' => [
                            Offer::class,
                            Participant::class,
                        ],
                    ],
                ],
            ],
        ]);

        $container->prependExtensionConfig('framework', [
            'messenger' => [
                'buses' => [
                    'messenger.bus.default' => [
                        'middleware' => [
                            MessageLogMiddleware::class,
                            'doctrine_transaction',
                            'router_context',
                        ],
                    ],
                ],
            ],
        ]);

        $container->prependExtensionConfig('symfonycasts_reset_password', [
            'request_password_repository' => ResetPasswordRequestRepository::class,
        ]);

        $this->prependWorkflow($container);
    }

    private function prependWorkflow(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('framework', [
            'workflows' => [
                'offer' => [
                    'type' => 'state_machine',
                    'marking_store' => ['type' => 'method', 'property' => 'state'],
                    'supports' => [OfferInterface::class],
                    'initial_marking' => OfferInterface::STATE_DRAFT,
                    'places' => [OfferInterface::STATE_DRAFT, OfferInterface::STATE_COMPLETED, OfferInterface::STATE_REVIEWED, OfferInterface::STATE_PUBLISHED, OfferInterface::STATE_CANCELLED, OfferInterface::STATE_UNPUBLISHED],
                    'transitions' => [
                        OfferInterface::TRANSITION_COMPLETE => ['from' => OfferInterface::STATE_DRAFT, 'to' => OfferInterface::STATE_COMPLETED],
                        OfferInterface::TRANSITION_APPROVE => ['from' => [OfferInterface::STATE_DRAFT, OfferInterface::STATE_COMPLETED], 'to' => OfferInterface::STATE_REVIEWED],
                        OfferInterface::TRANSITION_UNAPPROVE => ['from' => [OfferInterface::STATE_REVIEWED], 'to' => OfferInterface::STATE_COMPLETED],
                        OfferInterface::TRANSITION_PUBLISH => ['from' => [OfferInterface::STATE_DRAFT, OfferInterface::STATE_COMPLETED, OfferInterface::STATE_REVIEWED, OfferInterface::STATE_UNPUBLISHED], 'to' => OfferInterface::STATE_PUBLISHED],
                        OfferInterface::TRANSITION_CANCEL => ['from' => [OfferInterface::STATE_PUBLISHED], 'to' => OfferInterface::STATE_CANCELLED],
                        OfferInterface::TRANSITION_RELAUNCH => ['from' => [OfferInterface::STATE_CANCELLED], 'to' => OfferInterface::STATE_PUBLISHED],
                        OfferInterface::TRANSITION_UNPUBLISH => ['from' => [OfferInterface::STATE_CANCELLED, OfferInterface::STATE_PUBLISHED], 'to' => OfferInterface::STATE_UNPUBLISHED],
                    ],
                ],
            ],
        ]);
    }
}
