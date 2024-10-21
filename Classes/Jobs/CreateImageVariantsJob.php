<?php
declare(strict_types=1);

namespace Flownative\Neos\UniqueFilenames\Jobs;

use Flowpack\JobQueue\Common\Job\JobInterface;
use Flowpack\JobQueue\Common\Queue\Message;
use Flowpack\JobQueue\Common\Queue\QueueInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Media\Domain\Repository\ImageRepository;
use Neos\Media\Domain\Service\AssetVariantGenerator;
use Psr\Log\LoggerInterface;

class CreateImageVariantsJob implements JobInterface
{
    protected string $imageIdentifier;

    /**
     * @Flow\Inject
     * @var ImageRepository
     */
    protected $imageRepository;

    /**
     * @Flow\Inject
     * @var AssetVariantGenerator
     */
    protected $assetVariantGenerator;

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(string $imageIdentifier)
    {
        $this->imageIdentifier = $imageIdentifier;
    }

    public function execute(QueueInterface $queue, Message $message): bool
    {
        try {
            $image = $this->imageRepository->findByIdentifier($this->imageIdentifier);
            if ($image === null) {
                $this->logger->notice(sprintf('%s Job skipped for image %s – image not found.', $this->getIdentifier(), $this->imageIdentifier), LogEnvironment::fromMethodName(__METHOD__));
                return true;
            }
            $variants = $this->assetVariantGenerator->createVariants($image);
            $this->imageRepository->update($image);
            $this->logger->debug(sprintf('%s Job succeeded for image %s – %d variants created.', $this->getIdentifier(), $this->imageIdentifier, count($variants)), LogEnvironment::fromMethodName(__METHOD__));
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('%s Job failed. Error message: %s', $this->getIdentifier(), $exception->getMessage()), LogEnvironment::fromMethodName(__METHOD__));
            return false;
        }

        return true;
    }

    public function getIdentifier(): string
    {
        return 'CreateImageVariants';
    }

    public function getLabel(): string
    {
        return sprintf('CreateImageVariantsJob (image: "%s")', $this->imageIdentifier);
    }
}
