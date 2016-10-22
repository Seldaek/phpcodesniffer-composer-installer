<?php

/**
 * This file is part of the Dealerdirect PHP_CodeSniffer Standards
 * Composer Installer Plugin package.
 *
 * @copyright 2016 Dealerdirect B.V.
 * @license MIT
 */

namespace Dealerdirect\Composer\Plugin\Installers\PHPCodeSniffer;

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

/**
 * PHP_CodeSniffer standard installation manager.
 *
 * @author Franck Nijhof <f.nijhof@dealerdirect.nl>
 */
class Installer extends LibraryInstaller
{
    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        $this->initializeVendorDir();

        $rootPackageExtra = $this->composer->getPackage()->getExtra();
        $packageExtra     = $package->getExtra();

        if (isset($rootPackageExtra['phpcodesniffer-mapping'][$package->getPrettyName()]) === true) {
            $standardDir = $rootPackageExtra['phpcodesniffer-mapping'][$package->getPrettyName()];
        } elseif (isset($packageExtra['phpcodesniffer-standard']) === true) {
            $standardDir = $packageExtra['phpcodesniffer-standard'];
        } else {
            $standardDir = $this->generateStandardNameFromPackage($package);
        }

        return implode(
            DIRECTORY_SEPARATOR,
            [
                $this->vendorDir ? $this->vendorDir : '.',
                'squizlabs',
                'php_codesniffer',
                $this->getCodeSnifferSourceDirectory(),
                'Standards',
                $standardDir,
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getPackageBasePath(PackageInterface $package)
    {
        return $this->getInstallPath($package);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return ($packageType === 'phpcodesniffer-standard' || $packageType === 'phpcs-standard');
    }

    /**
     * Get the source directory of the PHP_CodeSniffer version installed.
     *
     * For version 3.* the source folder name has changed.
     *
     * @return string PHP_CodeSniffer source directory
     */
    protected function getCodeSnifferSourceDirectory()
    {
        $localRepository = $this->composer->getRepositoryManager()->getLocalRepository();

        if ($localRepository->findPackage('squizlabs/php_codesniffer', '^3.0.0') === null) {
            return 'CodeSniffer';
        }

        return 'src';
    }

    /**
     * Generates a coding standard name.
     *
     * In case there is no coding standard name provided, this function
     * will generate one based on the Composer package name.
     *
     * @param PackageInterface $package Composer package
     *
     * @return string Coding standard name
     */
    protected function generateStandardNameFromPackage(PackageInterface $package)
    {
        list($vendorName, $packageName) = explode('/', $package->getPrettyName(), 2);

        $sanitizePackageNamePatterns = [
            '/^standards?$/i',
            '/^coding-?standards?$/i',
            '/^standards?-/i',
            '/^coding-?standards?-/i',
            '/^(php)?codesniffer-/i',
            '/-coding-?standards?$/i',
            '/-standards?$/i',
        ];

        $packageName = preg_replace($sanitizePackageNamePatterns, '', $packageName);
        $packageName = str_replace('-', ' ', $packageName);
        $vendorName  = str_replace('-', ' ', $vendorName);

        if ($packageName === '') {
            $standardName = $vendorName;
        } else {
            $standardName = sprintf('%s - %s', $vendorName, $packageName);
        }

        $standardName = ucwords($standardName);
        $standardDir  = str_replace(' ', '', $standardName);
    }
}
