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

namespace Ferienpass\CoreBundle\Migration;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Migration\MigrationInterface;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\CoreBundle\Slug\Slug;
use Doctrine\DBAL\Connection;

class PageControllerMigration implements MigrationInterface
{
    private Connection $connection;
    private Slug $slug;
    private ContaoFramework $framework;

    private static array $pageTypes = [
        'account_deleted' => 'Account gelöscht',
        'activate_account' => 'Account aktivieren',
        'change_password' => 'Passwort ändern',
        'forgot_password_confirm' => 'Passwort zurückgesetzt',
        'lost_password' => 'Passwort vergessen',
        'host_details' => 'Veranstalter',
        'manage_notifications' => 'Benachrichtigungen',
        'offer_details' => 'Angebot',
        'personal_data' => 'Persönliche Daten',
        'registration_confirm' => 'Registrierung erfolgreich',
        'registration_welcome' => 'Erste Schritte',
    ];

    public function __construct(Connection $connection, Slug $slug, ContaoFramework $framework)
    {
        $this->connection = $connection;
        $this->slug = $slug;
        $this->framework = $framework;
    }

    public function getName(): string
    {
        return 'Introduce page controllers';
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();

        if (!$schemaManager->tablesExist(['tl_page'])) {
            return false;
        }

        // Return true if column has id values
        return $this->connection->query(
                "SELECT * FROM tl_page WHERE type IN ('".implode("','", array_keys(self::$pageTypes))."')"
            )->rowCount() < \count(array_keys(self::$pageTypes));
    }

    public function run(): MigrationResult
    {
        $this->framework->initialize();

        $rootPage = $this->connection
            ->query("SELECT id FROM tl_page WHERE type='root' AND dns NOT LIKE 'veranstalter%'")
            ->fetchColumn();

        $sorting = $this->connection
            ->executeQuery('SELECT MAX(sorting) FROM tl_page WHERE pid=:pid', ['pid' => $rootPage])
            ->fetchColumn();

        $types = $this->connection
            ->executeQuery("SELECT `type` FROM tl_page WHERE type IN ('".implode("','", array_keys(self::$pageTypes))."')")
            ->fetchAllAssociative();
        $types = array_column($types, 'type');

        foreach (array_diff_key(array_keys(self::$pageTypes), $types) as $pageType) {
            $sort = $sorting + 128;
            $title = self::$pageTypes[$pageType];
            $alias = $this->slug->generate($title, $rootPage);

            $this->connection->query(
                "INSERT INTO tl_page (title, `type`, `alias`, pid, published, hide, noSearch, sorting) VALUES ('$title', '$pageType', '$alias', '$rootPage', '1', '1', '1', $sort)"
            )->rowCount();
        }

        return new MigrationResult(true, 'Created pages');
    }
}
