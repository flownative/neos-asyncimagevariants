<?php
declare(strict_types=1);

namespace Flownative\Neos\UniqueFilenames;

use Flowpack\JobQueue\Common\Job\JobManager;
use Neos\Flow\Configuration\ConfigurationManager;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Neos\Media\Domain\Model\AssetInterface;
use Neos\Media\Domain\Model\Image;
use Neos\Media\Domain\Service\AssetService;
use Wysiwyg\Brabus\Website\Jobs\CreateImageVariantsJob;

class Package extends \Neos\Flow\Package\Package
{
    public function boot(Bootstrap $bootstrap)
    {
        $dispatcher = $bootstrap->getSignalSlotDispatcher();

        // handle asset variant creation in job queue
        $dispatcher->connect(AssetService::class, 'assetCreated', function (AssetInterface $asset) use ($bootstrap) {
            $configurationManager = $bootstrap->getObjectManager()->get(ConfigurationManager::class);
            if ($configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Neos.Media.autoCreateImageVariantPresets') === false) {
                if ($asset instanceof Image) {
                    $persistenceManager = $bootstrap->getObjectManager()->get(PersistenceManagerInterface::class);
                    $job = new CreateImageVariantsJob(
                        $persistenceManager->getIdentifierByObject($asset)
                    );

                    $queueName = $configurationManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_SETTINGS, 'Flownative.Neos.AsyncImageVariants.jobQueue');
                    $bootstrap->getObjectManager()->get(JobManager::class)->queue($queueName, $job);
                }
            }
        });
    }
}
