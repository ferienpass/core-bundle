<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ferienpass\CmsBundle\Filter\Type\FilterType;
use Ferienpass\CoreBundle\Entity\User;
use Ferienpass\CoreBundle\Export\Offer\Excel\ExcelExports;
use Ferienpass\CoreBundle\Export\Offer\PrintSheet\PdfExports;
use Ferienpass\CoreBundle\Export\Offer\Xml\XmlExports;
use Ferienpass\CoreBundle\Security\ContaoBackendUser;
use Ferienpass\CoreBundle\Security\ContaoUserProvider;
use Ferienpass\CoreBundle\Security\UserChecker;
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

    $services->get(ExcelExports::class)
        ->tag('ferienpass.offer_export_type', ['key' => 'xlsx'])
    ;
    $services->get(PdfExports::class)
        ->tag('ferienpass.offer_export_type', ['key' => 'pdf'])
    ;
    $services->get(XmlExports::class)
        ->tag('ferienpass.offer_export_type', ['key' => 'xml'])
    ;

    $services->alias('ferienpass.security.user_checker', UserChecker::class);

    $services->set('ferienpass.security.contao_backend_user_provider', ContaoUserProvider::class)
        ->args([
            service('contao.framework'),
            ContaoBackendUser::class,
            service('doctrine'),
            User::class,
            'email'
        ])
    ;
//    $services->set('ferienpass.security.contao_frontend_user_provider', ContaoUserProvider::class)
//        ->args([
//            service('contao.framework'),
//            ContaoFrontendUser::class,
//            service('doctrine'),
//            User::class,
//            'email'
//        ])
//    ;
};
