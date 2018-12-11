<?php
/**
 * Created by PhpStorm.
 * User: kay
 * Date: 30.07.15
 * Time: 14:11.
 */
namespace KayStrobach\Themes\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ThemesCommand extends Command
{
    protected function configure()
    {
        $this->setDescription('themes')
            ->setHelp('require css file')
            ->setDefinition([
                new InputArgument('cssFile', InputArgument::REQUIRED, 'CssFile'),
                new InputOption('outputExtension', null, InputOption::VALUE_OPTIONAL, 'outputExtension'),
                new InputOption('path', null, InputOption::VALUE_OPTIONAL, 'path'),
            ])
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     * @throws \TYPO3\Flow\Utility\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cssFile = $input->getArgument('cssFile');
        $outputExtension = $input->getOption('outputExtension');
        $path = $input->getOption('path');

        if ($outputExtension !== null) {
            $pageTsFile = ExtensionManagementUtility::extPath($outputExtension).'Configuration/PageTS/themes.icons.pagets';
            $setupTsFile = ExtensionManagementUtility::extPath($outputExtension).'Configuration/TypoScript/Library/lib.icons.cssMap.setupts';
            !file_exists(dirname($pageTsFile)) && mkdir(dirname($pageTsFile), 0777, true);
            !file_exists(dirname($setupTsFile)) && mkdir(dirname($setupTsFile), 0777, true);
        } elseif (file_exists($path)) {
            $pageTsFile = $path.'/themes.icons.pagets';
            $setupTsFile = $path.'/lib.icons.cssMap.setupts';
        } else {
            throw new \Exception('Please specify either an extension or an path where to store the icon files, '.$path);
        }
        if (!is_file($cssFile)) {
            throw new \Exception('CssFile not found');
        }

        $cssFileContent = file_get_contents($cssFile);

        $pattern = '#\.(.*)-(.*):before#Ui';
        preg_match_all($pattern, $cssFileContent, $iconMatches);


        file_put_contents(
            $pageTsFile,
            $this->renderContent(
                ExtensionManagementUtility::extPath('themes').'Resources/Private/Templates/ThemesCommand/PageTs.txt',
                $iconMatches[2],
                'fa'
            )
        );

        file_put_contents(
            $setupTsFile,
            $this->renderContent(
                ExtensionManagementUtility::extPath('themes').'Resources/Private/Templates/ThemesCommand/SetupTs.txt',
                $iconMatches[2],
                'fa'
            )
        );
    }

    protected function renderContent($template, $icons, $prefix)
    {
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
        $view = GeneralUtility::makeInstance('TYPO3\CMS\Fluid\View\StandaloneView');
        $view->setTemplatePathAndFilename($template);
        $view->assign('prefix', $prefix);
        $view->assign('icons', $icons);

        return $view->render();
    }
}
