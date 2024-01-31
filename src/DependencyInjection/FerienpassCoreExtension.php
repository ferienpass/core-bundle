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
use Ferienpass\CoreBundle\Export\Offer\PrintSheet\PdfExports;
use Ferienpass\CoreBundle\Export\Offer\Xml\XmlExports;
use Ferienpass\CoreBundle\Export\ParticipantList\WordExport;
use Ferienpass\CoreBundle\Monolog\EventLogHandler;
use Ferienpass\CoreBundle\Repository\ResetPasswordRequestRepository;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

final class FerienpassCoreExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $loader->load('services.php');

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

    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('framework', [
            'mailer' => [
                'envelope' => [
                    'sender' => '%env(ADMIN_EMAIL)%',
                ],
            ],
        ]);

        $container->prependExtensionConfig('symfonycasts_reset_password', [
            'request_password_repository' => ResetPasswordRequestRepository::class,
        ]);

        $container->prependExtensionConfig('monolog', [
            'channels' => ['ferienpass_event'],
            'handlers' => [
                'ferienpass_event' => [
                    'channels' => ['ferienpass_event'],
                    'type' => 'service',
                    'id' => EventLogHandler::class,
                ],
            ],
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
                    'supports' => [Offer::class],
                    'initial_marking' => Offer::STATE_DRAFT,
                    'places' => [Offer::STATE_DRAFT, Offer::STATE_COMPLETED, Offer::STATE_REVIEWED, Offer::STATE_PUBLISHED, Offer::STATE_CANCELLED, Offer::STATE_UNPUBLISHED],
                    'transitions' => [
                        Offer::TRANSITION_COMPLETE => ['from' => Offer::STATE_DRAFT, 'to' => Offer::STATE_COMPLETED],
                        Offer::TRANSITION_APPROVE => ['from' => [Offer::STATE_DRAFT, Offer::STATE_COMPLETED], 'to' => Offer::STATE_REVIEWED],
                        Offer::TRANSITION_PUBLISH => ['from' => [Offer::STATE_DRAFT, Offer::STATE_COMPLETED, Offer::STATE_REVIEWED, Offer::STATE_UNPUBLISHED], 'to' => Offer::STATE_PUBLISHED],
                        Offer::TRANSITION_CANCEL => ['from' => [Offer::STATE_PUBLISHED], 'to' => Offer::STATE_CANCELLED],
                        Offer::TRANSITION_RELAUNCH => ['from' => [Offer::STATE_CANCELLED], 'to' => Offer::STATE_PUBLISHED],
                        Offer::TRANSITION_UNPUBLISH => ['from' => [Offer::STATE_CANCELLED, Offer::STATE_PUBLISHED, Offer::STATE_COMPLETED, Offer::STATE_DRAFT, Offer::STATE_REVIEWED], 'to' => Offer::STATE_UNPUBLISHED],
                    ],
                ],
            ],
        ]);
    }
}
