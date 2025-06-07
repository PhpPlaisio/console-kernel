<?php
declare(strict_types=1);

namespace Plaisio\Console\Kernel\Command;

use Noodlehaus\Config;
use Plaisio\Console\Command\PlaisioCommand;
use Plaisio\Console\Helper\PlaisioXmlPathHelper;
use Plaisio\Console\Helper\TwoPhaseWrite;
use Plaisio\Console\Kernel\Helper\PlaisioXmlQueryHelper;
use Plaisio\PlaisioKernel;
use SetBased\Config\TypedConfig;
use SetBased\Helper\Cast;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for setting the type of the DataLayer in the kernel.
 */
class DataLayerTypeCommand extends PlaisioCommand
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * The declaration of the DataLayer.
   */
  const string PUBLIC_STATIC_DL = '/(?P<property>.*@property-read) (?P<class>.+) (?P<dl>\$DL) (?P<comment>.*)$/';

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function configure(): void
  {
    $this->setName('plaisio:kernel-data-layer-type')
         ->setDescription(sprintf('Sets the type of the DataLayer in %s', PlaisioKernel::class))
         ->addArgument('class', InputArgument::OPTIONAL, 'The class of the DataLayer');
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->io->title('Plaisio: DataLayer Type Annotation');

    $wrapperClass = Cast::toOptString($input->getArgument('class'));
    if ($wrapperClass===null)
    {
      $configFilename = $this->phpStratumConfigFilename();
      $wrapperClass   = $this->wrapperClass($configFilename);
    }

    $configPath = PlaisioXmlPathHelper::vendorDir().DIRECTORY_SEPARATOR.'plaisio/kernel/plaisio-kernel.xml';
    $config     = new PlaisioXmlQueryHelper($configPath);
    $xml        = $config->updateDataLayerType($wrapperClass);

    $helper = new TwoPhaseWrite($this->io);
    $helper->write($configPath, $xml);

    return 0;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the name of the PhpStratum configuration file.
   *
   * @return string
   */
  private function phpStratumConfigFilename(): string
  {
    $path1  = PlaisioXmlPathHelper::plaisioXmlPath('stratum');
    $helper = new PlaisioXmlQueryHelper($path1);

    $path2 = $helper->queryPhpStratumConfigFilename();

    return PlaisioXmlPathHelper::relativePath(dirname($path1).DIRECTORY_SEPARATOR.$path2);
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Extracts the wrapper class name from the PhpStratum configuration file.
   *
   * @param string $configFilename The name of the PhpStratum configuration file.
   *
   * @return string
   */
  private function wrapperClass(string $configFilename): string
  {
    $config = new TypedConfig(new Config($configFilename));

    return $config->getManString('wrapper.wrapper_class');
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
