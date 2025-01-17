<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Sylius\Behat\Context\Cli;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Bundle\CoreBundle\Console\Command\InstallSampleDataCommand;
use Sylius\Bundle\CoreBundle\Console\Command\SetupCommand;
use Sylius\Bundle\CoreBundle\Installer\Checker\CommandDirectoryChecker;
use Sylius\Bundle\CoreBundle\Installer\Setup\ChannelSetupInterface;
use Sylius\Bundle\CoreBundle\Installer\Setup\CurrencySetupInterface;
use Sylius\Bundle\CoreBundle\Installer\Setup\LocaleSetupInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\User\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

final class InstallerContext implements Context
{
    private ?Application $application = null;

    private ?CommandTester $tester = null;

    private ?Command $command = null;

    private array $inputChoices = [
        'currency' => 'USD',
        'locale' => 'en_US',
        'e-mail' => 'test@email.com',
        'username' => 'test',
        'password' => 'pswd',
        'confirmation' => 'pswd',
    ];

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly EntityManagerInterface $entityManager,
        private readonly CommandDirectoryChecker $commandDirectoryChecker,
        private readonly CurrencySetupInterface $currencySetup,
        private readonly LocaleSetupInterface $localeSetup,
        private readonly ChannelSetupInterface $channelSetup,
        private readonly FactoryInterface $adminUserFactory,
        private readonly UserRepositoryInterface $adminUserRepository,
        private readonly ValidatorInterface $validator,
        private readonly string $publicDir,
    ) {
    }

    /**
     * @When I run Sylius CLI installer
     */
    public function iRunSyliusCommandLineInstaller(): void
    {
        $this->application = new Application($this->kernel);
        $this->application->add(new SetupCommand(
            $this->entityManager,
            $this->commandDirectoryChecker,
            $this->currencySetup,
            $this->localeSetup,
            $this->channelSetup,
            $this->adminUserFactory,
            $this->adminUserRepository,
            $this->validator,
        ));

        $this->command = $this->application->find('sylius:install:setup');
        $this->tester = new CommandTester($this->command);

        $this->iExecuteCommandWithInputChoices('sylius:install:setup');
    }

    /**
     * @Given I run Sylius Install Load Sample Data command
     */
    public function iRunSyliusInstallSampleDataCommand(): void
    {
        $this->application = new Application($this->kernel);
        $this->application->add(new InstallSampleDataCommand(
            $this->entityManager,
            $this->commandDirectoryChecker,
            $this->publicDir,
        ));
        $this->command = $this->application->find('sylius:install:sample-data');
        $this->tester = new CommandTester($this->command);
    }

    /**
     * @Given I confirm loading sample data
     */
    public function iConfirmLoadingData(): void
    {
        $this->iExecuteCommandAndConfirm('sylius:install:sample-data');
    }

    /**
     * @Then the command should finish successfully
     */
    public function commandSuccess(): void
    {
        Assert::same($this->tester->getStatusCode(), 0);
    }

    /**
     * @Then I should see output :text
     */
    public function iShouldSeeOutput(string $text): void
    {
        Assert::contains($this->tester->getDisplay(), $text);
    }

    /**
     * @Given I do not provide an email
     */
    public function iDoNotProvideEmail(): void
    {
        $this->inputChoices['e-mail'] = '';
    }

    /**
     * @Given I do not provide a correct email
     */
    public function iDoNotProvideCorrectEmail(): void
    {
        $this->inputChoices['e-mail'] = 'janusz';
    }

    /**
     * @Given I provide full administrator data
     */
    public function iProvideFullAdministratorData(): void
    {
        $this->inputChoices['e-mail'] = 'test@admin.com';
        $this->inputChoices['username'] = 'test';
        $this->inputChoices['password'] = 'pswd1$';
        $this->inputChoices['confirmation'] = $this->inputChoices['password'];
    }

    private function iExecuteCommandWithInputChoices(string $name): void
    {
        try {
            $this->tester->setInputs($this->inputChoices);
            $this->tester->execute(['command' => $name]);
        } catch (\Exception) {
        }
    }

    private function iExecuteCommandAndConfirm(string $name): void
    {
        try {
            $this->tester->setInputs(['y']);
            $this->tester->execute(['command' => $name]);
        } catch (\Exception) {
        }
    }
}
