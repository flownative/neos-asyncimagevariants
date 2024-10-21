[![MIT license](http://img.shields.io/badge/license-MIT-brightgreen.svg)](http://opensource.org/licenses/MIT)
[![Packagist](https://img.shields.io/packagist/v/flownative/neos-asyncimagevariants.svg)](https://packagist.org/packages/flownative/neos-asyncimagevariants)
[![Packagist](https://img.shields.io/packagist/dm/flownative/neos-asyncimagevariants)](https://packagist.org/packages/flownative/neos-asyncimagevariants)
[![Maintenance level: Love](https://img.shields.io/badge/maintenance-%E2%99%A1%E2%99%A1-ff69b4.svg)](https://www.flownative.com/en/products/open-source.html)

# Flownative.Neos.AsyncImageVariants

## Description

This [Flow](https://flow.neos.io) package allows to asynchronously generate image variants for Neos.Media images.

It does this by switching off automatic variant creation (through settings) and wiring a slot to the `assetCreated`
signal emitted in the `AssetService`. That slot creates a job in the job queue that executes the asset variant
creation asynchronously.

## Installation

This is installed as a regular Flow package via Composer.  For your existing project, simply include
`flownative/neos-asyncimagevariants` into  the dependencies of your Flow or Neos distribution:

    composer require flownative/neos-asyncimagevariants

## Configuration

The package itself has one configuration option for the job queue name to use, it defaults to `media-queue`.

```yaml
Flownative:
  Neos:
    AsyncImageVariants:
      # the queue to use for jobs
      jobQueue: 'media-queue'
```

That queue of course needs to be configured, e.g. like this:

```yaml
Flowpack:
  JobQueue:
    Common:
      queues:
        'media-queue':
          className: 'Flowpack\JobQueue\Doctrine\Queue\DoctrineQueue'
          executeIsolated: true
          releaseOptions:
            delay: 15
          options:
            backendOptions:
              driver: '%env:DATABASE_DRIVER%'
              host: '%env:DATABASE_HOST%'
              port: '%env:DATABASE_PORT%'
              dbname: '%env:DATABASE_NAME%'
              user: '%env:DATABASE_USER%'
              password: '%env:DATABASE_PASSWORD%'
```

Make sure to run `./flow job:work media-queue` continuously in the background.

## Troubleshooting

- If things don't work as expected, check the system log.
- Check if jobs are queued by using `./flow queue:list`
- Run `./flow job:work media-queue --verbose --limit 1` to debug job execution
