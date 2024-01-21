<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ferienpass\CoreBundle\Entity\User;
use Ferienpass\CoreBundle\Export\Offer\Excel\ExcelExports;
use Ferienpass\CoreBundle\Export\Offer\PrintSheet\PdfExports;
use Ferienpass\CoreBundle\Export\Offer\Xml\XmlExports;
use Ferienpass\CoreBundle\Filter\Type\FilterType;
use Ferienpass\CoreBundle\Messenger\EventLogMiddleware;
use Ferienpass\CoreBundle\Notification\PaymentCreatedNotification;
use Ferienpass\CoreBundle\Security\ContaoBackendUser;
use Ferienpass\CoreBundle\Security\ContaoFrontendUser;
use Ferienpass\CoreBundle\Security\ContaoUserProvider;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

return function(ContainerConfigurator $container): void {
    $services = $container->services()
        ->defaults()
        ->autoconfigure()
        ->autowire()
    ;

    $services
        ->load('Ferienpass\\CoreBundle\\', '../src/')
        ->exclude([
            '../src/{DependencyInjection,Entity}',
            '../src/**/PdfExportConfig.php'
        ])
    ;

    // Tags by directory
//    $services
//        ->load('Ferienpass\\AdminBundle\\Controller\\Dashboard\\', '../src/Controller/Dashboard/')
//        ->tag('ferienpass_admin.dashboard_widget')
//    ;
//
//    $services
//        ->load('Ferienpass\\AdminBundle\\Controller\\Statistics\\', '../src/Controller/Statistics/')
//        ->tag('ferienpass_admin.stats_widget')
//    ;

    // Tags autoconfigure
    $services
        ->instanceof(FilterType::class)
        ->tag('ferienpass.filter.offer_list_type')
    ;
    $services
        ->instanceof(AbstractTypeExtension::class)
        ->tag('form.type_extension')
    ;
    $services
        ->instanceof(VoterInterface::class)
        ->tag('security.voter')
    ;

//    // Aliases for autowiring
//    $services->alias(Helper::class, 'knp_menu.helper');
//
//    $services->get(MenuBuilder::class)
//        ->tag('knp_menu.menu_builder', ['method' => 'primaryNavigation', 'alias' => 'ferienpass_admin_primary'])
//        ->tag('knp_menu.menu_builder', ['method' => 'userNavigation', 'alias' => 'host_user_navigation'])
//        ->tag('knp_menu.menu_builder', ['method' => 'offerActions', 'alias' => 'host_offer_actions'])
//        ->tag('knp_menu.menu_builder', ['method' => 'offerFilters', 'alias' => 'host_offer_filters'])
//        ->tag('knp_menu.menu_builder', ['method' => 'participantListActions', 'alias' => 'host_participant_list_actions'])
//    ;
//
    $services->get(EventLogMiddleware::class)
        ->tag('monolog.logger', ['channel' => 'ferienpass_event'])
    ;
    $services->get(ExcelExports::class)
        ->tag('ferienpass.offer_export_type', ['key' => 'xlsx'])
    ;
    $services->get(PdfExports::class)
        ->tag('ferienpass.offer_export_type', ['key' => 'pdf'])
    ;
    $services->get(XmlExports::class)
        ->tag('ferienpass.offer_export_type', ['key' => 'xml'])
    ;
    $services->get(PaymentCreatedNotification::class)
        ->tag('ferienpass.notification', ['key'=>'payment_created'])
    ;

    $services->set('ferienpass.security.contao_backend_user_provider', ContaoUserProvider::class)
        ->args([
            service('contao.framework'),
            ContaoBackendUser::class,
            service('doctrine'),
            User::class,
            'email'
        ])
    ;
    $services->set('ferienpass.security.contao_frontend_user_provider', ContaoUserProvider::class)
        ->args([
            service('contao.framework'),
            ContaoFrontendUser::class,
            service('doctrine'),
            User::class,
            'email'
        ])
    ;
//
//    $services->set(\Twig\Extension\StringLoaderExtension::class);

};
