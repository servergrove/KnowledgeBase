<?php

namespace ServerGrove\KbBundle\Tests\Controller;

use ServerGrove\KbBundle\Tests\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * Class ControllerTestCase
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class ControllerTestCase extends WebTestCase
{
    /**
     * @param string $route
     * @param array  $parameters
     * @param bool   $absolute
     *
     * @return mixed
     */
    protected function generateUrl($route, $parameters = array(), $absolute = false)
    {
        return $this->getContainer()->get('router')->generate($route, $parameters, $absolute);
    }

    /**
     * @param \Symfony\Bundle\FrameworkBundle\Client $client
     *
     * @return string
     */
    protected function getErrorMessage(Client $client)
    {
        /** @var $profile \Symfony\Component\HttpKernel\Profiler\Profile */
        $profile = $client->getProfile();
        if (!$profile) {
            return '';
        }

        /** @var $exception \Symfony\Component\HttpKernel\DataCollector\ExceptionDataCollector */
        $exception = $profile->getCollector('exception');

        if (!$exception->hasException()) {
            return '';
        }

        $arrayTrace = array();

        foreach (array_slice($exception->getTrace(), 0, 10) as $position => $traceLine) {
            if (isset($traceLine['class'])) {
                $arrayTrace[] = sprintf('[%d] %s(%d) %s%s%s(%s)',
                    $position,
                    $traceLine['file'],
                    $traceLine['line'],
                    $traceLine['short_class'],
                    $traceLine['type'],
                    $traceLine['function'],
                    isset($traceLine['args']) ? implode(', ', array_map(function($v) {
                        switch ($v[0]) {
                            case 'array':
                                return 'array('.count($v[1]).')';
                            case 'object':
                                return $v[1];
                            default:
                                return $v[1];

                        }
                    }, $traceLine['args'])) : ''
                );
            }
        }

        if (count($arrayTrace) < count($exception->getTrace())) {
            $arrayTrace[] = '...';
        }

        return $exception->getMessage().PHP_EOL.PHP_EOL.implode(PHP_EOL, $arrayTrace).PHP_EOL;
    }
}
