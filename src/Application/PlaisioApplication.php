<?php
declare(strict_types=1);

namespace Plaisio\Console\Application;

use Symfony\Component\Console\Application;

/**
 * The Plaisio application.
 */
class PlaisioApplication extends Application
{
  //--------------------------------------------------------------------------------------------------------------------
  /**
   * PlaisioApplication constructor.
   */
  public function __construct()
  {
    parent::__construct('Plaisio', '1.0.1');

    $this->setCommandLoader(new CommandLoader());
  }

  //--------------------------------------------------------------------------------------------------------------------
}

//----------------------------------------------------------------------------------------------------------------------
