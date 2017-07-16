<?php

namespace Ps2alerts\Api\Contract;

use Twig_Environment as Twig;

trait TemplateAwareTrait
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * Set template Driver
     *
     * @param \Twig_Environment $twig
     */
    public function setTemplateDriver(Twig $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Get the template driver
     *
     * @return \Twig_Environment
     */
    public function getTemplateDriver()
    {
        return $this->twig;
    }
}
