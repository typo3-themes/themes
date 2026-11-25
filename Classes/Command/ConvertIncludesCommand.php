<?php

declare(strict_types=1);

namespace KayStrobach\Themes\Command;

use KayStrobach\Themes\Migration\IncludeTypoScriptConverter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Package\PackageManager;

/**
 * themes:convert-includes: <INCLUDE_TYPOSCRIPT: ...> to @import, see IncludeTypoScriptConverter
 */
final class ConvertIncludesCommand extends Command
{
    /** Default: themes, theme_*, themes_* and skin_* extensions */
    private const DEFAULT_PATTERN = '/^(themes_|theme_\w+|themes_\w+|skin_\w+)$/';

    public function __construct(
        private readonly PackageManager $packageManager,
        private readonly IncludeTypoScriptConverter $converter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Converts <INCLUDE_TYPOSCRIPT: ...> to @import (TYPO3 v14 ignores the old syntax)')
            ->addArgument('extensions', InputArgument::IS_ARRAY | InputArgument::OPTIONAL, 'Extension keys, default: themes, theme_*, themes_*, skin_*')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Only show what would change')
            ->setHelp(<<<'HELP'
Rewrites <INCLUDE_TYPOSCRIPT: source="FILE:..."> and source="DIR:..." to @import in the files of the given
extensions. @import only loads files ending with .typoscript (TSconfig: also .tsconfig), so included files are
renamed: *.setupts -> *.setup.typoscript, *.constantsts -> *.constants.typoscript, *.pagets -> *.tsconfig,
*.txt/*.ts -> *.typoscript, also the entry points setup.txt, constants.txt and tsconfig.txt.
References to renamed files (EXT:...) in TypoScript, PHP, YAML and HTML of these extensions are updated.

Deterministic and idempotent: run it again after an update of an extension, a second run changes nothing.
Includes that can not be converted stay and are listed as warning. Use --dry-run first and check the result
with git diff.

  typo3 themes:convert-includes --dry-run
  typo3 themes:convert-includes theme_bootstrap site_package
HELP);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $extensionPaths = [];
        foreach ($this->packageManager->getActivePackages() as $package) {
            $extensionPaths[$package->getPackageKey()] = $package->getPackagePath();
        }
        ksort($extensionPaths);
        $extensions = (array)$input->getArgument('extensions');
        if ($extensions === []) {
            $extensions = array_values(array_filter(
                array_keys($extensionPaths),
                static fn (string $key): bool => (bool)preg_match(self::DEFAULT_PATTERN, $key)
            ));
        }
        $unknown = array_diff($extensions, array_keys($extensionPaths));
        if ($unknown !== []) {
            $io->error('Extension(s) not active: ' . implode(', ', $unknown));
            return Command::INVALID;
        }

        $plan = $this->converter->plan($extensionPaths, $extensions, Environment::getPublicPath());
        $io->text('Extensions: ' . implode(', ', $extensions));
        $root = Environment::getProjectPath() . '/';
        $short = static fn (string $file): string => str_starts_with($file, $root) ? substr($file, strlen($root)) : $file;
        if ($plan->renames !== []) {
            $io->section(sprintf('Rename (%d)', count($plan->renames)));
            $io->listing(array_map(
                static fn (string $old, string $new): string => $short($old) . ' -> ' . basename($new),
                array_keys($plan->renames),
                $plan->renames
            ));
        }
        if ($plan->contents !== []) {
            $io->section(sprintf('Change (%d)', count($plan->contents)));
            $io->listing(array_map($short, array_keys($plan->contents)));
        }
        foreach ($plan->warnings as $warning) {
            $io->warning($warning);
        }
        if ($plan->isEmpty()) {
            $io->success('Nothing to convert.');
            return Command::SUCCESS;
        }
        if ($input->getOption('dry-run')) {
            $io->note('Dry run: nothing was changed.');
            return Command::SUCCESS;
        }
        $this->converter->apply($plan);
        $io->success('Converted. Flush the caches.');
        return Command::SUCCESS;
    }
}
