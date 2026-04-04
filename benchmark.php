<?php

require __DIR__.'/vendor/autoload.php';

use App\Kernel;
use App\Entity\Campaign;
use App\Entity\Asset;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Bundle\FrameworkBundle\Console\Application;

$kernel = new Kernel('test', true);
$kernel->boot();

$container = $kernel->getContainer();
/** @var EntityManagerInterface $em */
$em = $container->get('doctrine')->getManager();

// Setup schema
$application = new Application($kernel);
$application->setAutoExit(false);
$application->run(new ArrayInput([
    'command' => 'doctrine:schema:drop',
    '--force' => true,
    '--env' => 'test'
]), new NullOutput());
$application->run(new ArrayInput([
    'command' => 'doctrine:schema:create',
    '--env' => 'test'
]), new NullOutput());

echo "Creating data...\n";
$campaign = new Campaign();
$campaign->setTitle('Test Campaign');
$campaign->setFinancialGoal('10000');
$campaign->setCurrentAmount('0');

$em->persist($campaign);

$targetAsset = null;
for ($i = 0; $i < 1000; $i++) {
    $asset = new Asset();
    $asset->setFilePath("https://example.com/asset$i.png");
    $asset->setMimeType("image/png");
    $asset->setFilename("asset$i.png");
    $campaign->addAsset($asset);
    $em->persist($asset);

    if ($i === 500) {
        $targetAsset = $asset;
    }
}

$em->flush();
$em->clear();

echo "Benchmarking...\n";
$campaignId = $campaign->getId();
$targetAssetId = $targetAsset->getId();

// Warm up / clear
$em->clear();

$startMemory = memory_get_usage();
$startTime = microtime(true);

$campaign = $em->getRepository(Campaign::class)->find($campaignId);
$targetAsset = $em->getRepository(Asset::class)->find($targetAssetId);

// The operation that benefits from EXTRA_LAZY
$campaign->getAssets()->contains($targetAsset);
$campaign->getAssets()->count();
$campaign->getAssets()->slice(0, 10); // this also benefits

$endTime = microtime(true);
$endMemory = memory_get_usage();

echo "Time taken: " . ($endTime - $startTime) * 1000 . " ms\n";
echo "Memory used: " . ($endMemory - $startMemory) / 1024 . " KB\n";
