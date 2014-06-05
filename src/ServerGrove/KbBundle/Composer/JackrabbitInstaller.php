<?php

namespace ServerGrove\KbBundle\Composer;

use Composer\Util\Filesystem;
use Composer\Util\RemoteFilesystem;

/**
 * Class JackrabbitInstaller
 *
 * @author Ismael Ambrosi<ismael@servergrove.com>
 */
class JackrabbitInstaller
{
    const JACKRABBIT_VERSION = '2.4.5';

    /**
     * @var string
     */
    private static $downloadUrls;

    /**
     * @static
     *
     * @param \Composer\Script\CommandEvent $event
     */
    public static function checkAndInstall($event)
    {
        $appDir        = getcwd().'/app';
        $resourcesPath = $appDir.'/Resources';
        if (is_dir($resourcesPath)) {
            $filesystem    = new Filesystem();
            $jackrabbitDir = $resourcesPath.'/java/jackrabbit';

            $filesystem->ensureDirectoryExists($jackrabbitDir);

            if (!self::check($jackrabbitDir) && false !== ($file = self::download($event->getIO(), $jackrabbitDir))) {
                self::install($file, $appDir);
            }
        }
    }

    /**
     * @static
     *
     * @param string $destination
     *
     * @return bool
     */
    private static function check($destination)
    {
        $url = current(self::getDownloadUrl());

        return false !== $url && file_exists($destination.'/'.basename(parse_url($url, PHP_URL_PATH)));
    }

    /**
     * @static
     *
     * @param \Composer\IO\IOInterface $io
     * @param string                   $destination
     *
     * @return bool
     */
    private static function download(\Composer\IO\IOInterface $io, $destination)
    {
        $io->write('<info>Installing jackrabbit</info>');
        if (false === ($urls = self::getDownloadUrl())) {
            $io->write('Invalid URLs');
        } else {
            reset($urls);
            $r = new RemoteFilesystem($io);

            do {
                try {
                    $url  = current($urls);
                    $file = $destination.'/'.basename(parse_url($url, PHP_URL_PATH));
                    $io->write(sprintf('Retrieving Jackrabbit from "%s"', $url), true);

                    $result = $r->copy('', $url, $file, true);
                } catch (\Composer\Downloader\TransportException $ex) {
                    $io->write('', true);
                    $result = false;
                    $file   = null;
                }
            } while (false === $result && next($urls));

            if (is_null($file)) {
                throw new \Exception('Invalid file name');
            }

            return $file;
        }

        return false;
    }

    private static function install($file, $appDir)
    {
        $parametersFile = $appDir.'/config/jackrabbit.yml';

        if (!file_exists($parametersFile)) {
            touch($parametersFile);
        }

        $content = sprintf(
            'parameters: %s    doctrine_phpcr.jackrabbit_jar: %s%1$s',
            PHP_EOL,
            str_replace($appDir, '%kernel.root_dir%', $file)
        );

        file_put_contents($parametersFile, $content);
    }

    /**
     * @static
     * @return bool|string
     */
    private static function getDownloadUrl()
    {
        if (!is_array(self::$downloadUrls)) {
            $version = self::JACKRABBIT_VERSION;
            if (false === ($content = file_get_contents(self::getMirrorListUrl($version)))) {
                throw new \Exception('Unable to retrive mirror list');
            }
            $content = strip_tags($content);
            $pattern = '#(?P<url>[https|http|ftp]+\://[\w\.\-/]+/jackrabbit\-standalone\-(?P<version>[0-9\.]+).jar)#';

            if (!preg_match_all($pattern, $content, $out)) {
                return false;
            }

            $map = array();
            foreach ($out['url'] as $position => $url) {
                if (isset($out['version'][$position]) && $version === $out['version'][$position]) {
                    $map[] = $url;
                }
            }

            self::$downloadUrls = array_unique($map);
        }

        return self::$downloadUrls;
    }

    private static function getMirrorListUrl($version)
    {
        return strtr(
            'http://www.apache.org/dyn/closer.cgi/jackrabbit/:version/jackrabbit-standalone-:version.jar',
            array(':version' => $version)
        );
    }
}
