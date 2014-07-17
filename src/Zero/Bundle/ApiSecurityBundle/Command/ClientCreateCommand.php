<?php


namespace Zero\Bundle\ApiSecurityBundle\Command;


use OAuth2\OAuth2;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class ClientCreateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('zero:security:client:create')
            ->setDescription('Creates a new client')
            ->addArgument('name', InputArgument::REQUIRED, 'Client name', null)
            ->addOption(
                'redirect-uri',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Redirect uri for client.',
                null
            )
            ->addOption(
                'grant-type',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Allowed grant type for client.',
                null
            );
    }

    protected function getDialogHelper()
    {
        $dialog = $this->getHelperSet()->get('dialog');
        if (!$dialog || get_class($dialog) !== 'Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper') {
            $this->getHelperSet()->set($dialog = new DialogHelper());
        }

        return $dialog;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getDialogHelper();
        $dialog->writeSection($output, 'OAuth2 authentication client creator');

        // namespace
        $output->writeln(
            array(
                'This command helps you create OAuth2 client.',
                '',
                'First, you need to give the client name you want to create.',
                ''
            )
        );

        $helper = $this->getHelper('question');

        $nameQuestion = new Question('<info>Client name</info>: ', $input->getArgument('name'));
        $nameQuestion->setValidator(
            function ($answer) {
                if ($answer == '') {
                    throw new \RuntimeException('The client name cannot be empty');
                }

                return $answer;
            }
        );
        $nameQuestion->setMaxAttempts(2);

        $name = $helper->ask($input, $output, $nameQuestion);
        $input->setArgument('name', $name);

        if (empty($input->getOption('redirect-uri'))) {
            $output->writeln(array('', 'You can optionally add redirect-uris for this client', ''));

            $redirectUris = array();
            $redirectUriQuestion = new Question('<info>Redirect URI</info> (press <return> to stop adding): ', '');
            $redirectUriQuestion->setMaxAttempts(2);
            $redirectUriQuestion->setValidator(
                function ($answer) use (&$redirectUris) {
                    if (in_array($answer, $redirectUris)) {
                        throw new \RuntimeException(sprintf('"%s" is already added as redirect URI', $answer));
                    }
                    return $answer;
                }
            );
            while (true) {
                $redirectUri = $helper->ask($input, $output, $redirectUriQuestion);
                if ($redirectUri == '') {
                    break;
                }
                $redirectUris[] = $redirectUri;
            }
            $input->setOption('redirect-uri', $redirectUris);
        }

        if (empty($input->getOption('grant-type'))) {
            $output->writeln(array('', 'You can optionally add grant types for this client', ''));

            $grantTypes = array();

            $grantTypeChoices = array(
                OAuth2::GRANT_TYPE_AUTH_CODE,
                OAuth2::GRANT_TYPE_IMPLICIT,
                OAuth2::GRANT_TYPE_USER_CREDENTIALS,
                OAuth2::GRANT_TYPE_CLIENT_CREDENTIALS,
                OAuth2::GRANT_TYPE_REFRESH_TOKEN
            );

            $grantTypeQuestion = new ChoiceQuestion('<info>Grant type</info> (press <return> to stop adding): ', $grantTypeChoices, '');
            $grantTypeQuestion->setMaxAttempts(2);
            $grantTypeQuestion->setValidator(
                function ($answer) use (&$grantTypes, $grantTypeChoices) {
                    if ($answer != '') {
                        if (!array_key_exists($answer, $grantTypeChoices)) {
                            throw new \RuntimeException('Invalid choice');
                        } else {
                            $answer = $grantTypeChoices[$answer];
                        }

                        if (in_array($answer, $grantTypes)) {
                            throw new \RuntimeException(sprintf('"%s" is already added as grant type', $answer));
                        }
                    }

                    return $answer;
                }
            );
            while (true) {
                $grantType = $helper->ask($input, $output, $grantTypeQuestion);
                if ($grantType == '') {
                    break;
                }
                $grantTypes[] = $grantType;
            }
            $input->setOption('grant-type', $grantTypes);
        }

        // summary
        $output->writeln(
            array(
                '',
                $this->getHelper('formatter')->formatBlock('Summary before creation', 'bg=blue;fg=white', true),
                '',
                sprintf("You are going to create a client with name \"<info>%s</info>\"", $name),
                ''
            )
        );

        if (!empty($input->getOption('redirect-uri'))) {
            $lines = array(
                "It has the following redirect uris: "
            );

            foreach($input->getOption('redirect-uri') as $uri) {
                $lines[] = sprintf(' - <info>%s</info>', $uri);
            }

            $lines[] = '';
            $output->writeln($lines);
        } else {
            $output->writeln('With <info>no</info> redirect uris');
        }

        if (!empty($input->getOption('grant-type'))) {
            $lines = array(
                "And has the following grant types: ",
            );

            foreach($input->getOption('grant-type') as $type) {
                $lines[] = sprintf(' - <info>%s</info>', $type);
            }

            $lines[] = '';
            $output->writeln($lines);
        } else {
            $output->writeln('And <info>no</info> grant types');
        }

        $output->writeln('');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->isInteractive()) {
            $helper = $this->getHelper('question');
            $continue = new ConfirmationQuestion('Continue with creation? [<comment>no</comment>] ', false);
            if(!$helper->ask($input, $output, $continue)) {
                $output->writeln('<error>Command aborted</error>');

                return 1;
            }
        }

        $clientManager = $this->getContainer()->get('fos_oauth_server.client_manager.default');
        $client = $clientManager->createClient();
        $client->setName($input->getArgument('name'));
        $client->setRedirectUris($input->getOption('redirect-uri'));
        $client->setAllowedGrantTypes($input->getOption('grant-type'));
        $clientManager->updateClient($client);
        $output->writeln(sprintf('Added a new client with name <info>%s</info> and public id <info>%s</info>.', $client->getName(), $client->getPublicId()));
    }
} 