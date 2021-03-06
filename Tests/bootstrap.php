<?php
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SwagImportExport\Tests\Helper\DataProvider\NewsletterDataProvider;
use SwagImportExport\Tests\Helper\DataProvider\ProfileDataProvider;
use Tests\Helper\BackendControllerTestHelper;

include_once __DIR__ . '/../../../../../../../tests/Functional/bootstrap.php';

class ImportExportTestKernel extends TestKernel
{
    const IMPORT_FILES_DIR = __DIR__ . '/Helper/ImportFiles/';

    public function getConfig()
    {
        return __DIR__ . '/../../../../../../../config_testing.php';
    }

    public static function start()
    {
        $kernel = new \Shopware\Kernel('testing', true);
        $kernel->boot();

        $container = $kernel->getContainer();
        $container->get('plugins')->Core()->ErrorHandler()->registerErrorHandler(E_ALL | E_STRICT);

        /** @var $repository \Shopware\Models\Shop\Repository */
        $repository = $container->get('models')->getRepository('Shopware\Models\Shop\Shop');

        $shop = $repository->getActiveDefault();
        $shop->registerResources();

        if (!self::assertPlugin('SwagImportExport')) {
            throw new \Exception("Plugin ImportExport must be installed.");
        }

        Shopware()->Loader()->registerNamespace('SwagImportExport\Tests', __DIR__ . '/../Tests/');
        Shopware()->Loader()->registerNamespace('Tests\Helper', __DIR__ . '/Helper/');
        Shopware()->Loader()->registerNamespace('Tests\Shopware\ImportExport', __DIR__ . '/Shopware/ImportExport/');
        Shopware()->Loader()->registerNamespace('Shopware\Setup\SwagImportExport', __DIR__ . '/../Setup/SwagImportExport/');
        Shopware()->Loader()->registerNamespace('Shopware\Components', __DIR__ . '/../Components/');
        Shopware()->Loader()->registerNamespace('Shopware\CustomModels', __DIR__ . '/../Models/');

        self::registerResources();
    }

    /**
     * @param string $name
     * @return boolean
     */
    private static function assertPlugin($name)
    {
        $sql = 'SELECT 1 FROM s_core_plugins WHERE name = ? AND active = 1';

        return (boolean) Shopware()->Container()->get('dbal_connection')->fetchColumn($sql, [$name]);
    }

    /**
     * Registers all necessary classes to the di container.
     */
    private static function registerResources()
    {
        Shopware()->Container()->set(
            'swag_import_export.tests.profile_data_provider',
            new ProfileDataProvider(
                Shopware()->Container()->get('models')
            )
        );

        Shopware()->Container()->set(
            'swag_import_export.tests.newsletter_data_provider',
            new NewsletterDataProvider(
                Shopware()->Container()->get('models')
            )
        );

        Shopware()->Container()->set(
            'swag_import_export.tests.backend_controller_test_helper',
            new BackendControllerTestHelper(
                Shopware()->Container()->get('swag_import_export.tests.profile_data_provider')
            )
        );
    }
}

ImportExportTestKernel::start();
