<?php
declare(strict_types=1);

namespace Plaisio\Console\Helper\Kernel;

use Plaisio\Console\Exception\ConfigException;

/**
 * Helper class for querying information from a plaisio.xml file.
 */
class PlaisioXmlQueryHelper extends \Plaisio\Console\Helper\PlaisioXmlQueryHelper
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns all kernel properties in this PhpPlaisio config file.
   *
   * @return array
   */
  public function queryKernelProperties(): array
  {
    $properties = [];

    $xpath = new \DOMXpath($this->xml);
    $list  = $xpath->query('/kernel/properties/property');
    foreach ($list as $item)
    {
      $properties[] = ['type'        => $xpath->query('type', $item)[0]->nodeValue ?? null,
                       'name'        => $xpath->query('name', $item)[0]->nodeValue ?? null,
                       'description' => $xpath->query('description', $item)[0]->nodeValue ?? null];
    }

    return $properties;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Returns the path to the config file of PhpStratum.
   *
   * @return string
   */
  public function queryPhpStratumConfigFilename(): string
  {
    $xpath = new \DOMXpath($this->xml);
    $node  = $xpath->query('/stratum/config')->item(0);

    if ($node===null)
    {
      throw new ConfigException('PhpStratum configuration file not defined in %s', $this->path);
    }

    return $node->nodeValue;
  }

  //--------------------------------------------------------------------------------------------------------------------
  /**
   * Replaces the type annotation of the DataLayer with the actual wrapper class.
   *
   * @param string $wrapperClass The name of the class of the DataLayer.
   *
   * @return string
   */
  public function updateDataLayerType(string $wrapperClass): string
  {
    $xpath = new \DOMXpath($this->xml);
    $query = "/kernel/properties/property/name[text()='DL']";
    $list  = $xpath->query($query);
    if ($list->length!==1)
    {
      throw new ConfigException('Unable to find the DataLayer in %s', $this->path);
    }

    $parent = $list->item(0)->parentNode;
    $query  = 'type';
    $list   = $xpath->query($query, $parent);
    if ($list->length!==1)
    {
      throw new ConfigException('Unable to find the type of the DataLayer in %s', $this->path);
    }

    $list->item(0)->nodeValue = '\\'.ltrim($wrapperClass, '\\');

    return $this->xml->saveXML($this->xml);
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
