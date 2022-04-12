<?php

declare(strict_types=1);

namespace App\Command;

use App\Contract\Repository\AuthorRepositoryInterface;
use App\Contract\Service\AuthenticationServiceInterface;
use App\Exception\CantSaveException;
use App\Model\Author;
use App\Security\Token\ApiToken;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;
use DateTime;

class AddAuthorCommand extends Command
{
    private AuthorRepositoryInterface $authorRepository;
    private AuthenticationServiceInterface $authenticationService;
    private TokenStorageInterface $tokenStorage;

    private InputInterface $input;
    private OutputInterface $output;
    private StyleInterface $style;

    /**
     * @var string
     */
    protected static $defaultName = 'author-add';

    public function __construct(
        AuthorRepositoryInterface $authorRepository,
        AuthenticationServiceInterface $authenticationService,
        TokenStorageInterface $tokenStorage,
        string $name = null
    ) {
        parent::__construct($name);

        $this->authorRepository = $authorRepository;
        $this->authenticationService = $authenticationService;
        $this->tokenStorage = $tokenStorage;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add author')
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED)
            ->addOption('interactive-input', null, InputOption::VALUE_NONE)
            ->addOption('first-name', null, InputOption::VALUE_REQUIRED)
            ->addOption('last-name', null, InputOption::VALUE_REQUIRED)
            ->addOption('birthday', null, InputOption::VALUE_REQUIRED)
            ->addOption('biography', null, InputOption::VALUE_REQUIRED)
            ->addOption('gender', null, InputOption::VALUE_REQUIRED)
            ->addOption('place-of-birth', null, InputOption::VALUE_REQUIRED);
    }

    /**
     * @throws CantSaveException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->style = new SymfonyStyle($input, $output);

        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        $this->authenticate($email, $password);

        $this->style->success('Authenticated');

        if ($input->getOption('interactive-input')) {
            $this->handleInteractive();
            return 0;
        }

        $author = $this->createModel(
            $input->getOption('first-name'),
            $input->getOption('last-name'),
            $input->getOption('birthday'),
            $input->getOption('biography'),
            $input->getOption('gender'),
            $input->getOption('place-of-birth')
        );

        $this->authorRepository->save($author);

        $this->style->success('Author added');

        return 0;
    }

    /**
     * @throws CantSaveException
     */
    private function handleInteractive()
    {
        $helper = new QuestionHelper();

        while (true) {
            $firstName = $helper->ask($this->input, $this->output, new Question('First Name: '));
            $lastName = $helper->ask($this->input, $this->output, new Question('Last Name: '));
            $birthDay = $helper->ask($this->input, $this->output, new Question('Birthday (Y-m-d): '));

            $biography = $helper->ask($this->input, $this->output, new Question('Biography: ', ''));

            $gender = $helper
                ->ask($this->input, $this->output, new Question('Gender (male/female): ', 'male'));

            $placeOfBirth = $helper->ask($this->input, $this->output, new Question('Place of birth: '));

            $author = $this->createModel(
                $firstName,
                $lastName,
                $birthDay,
                $biography,
                $gender,
                $placeOfBirth
            );

            $this->authorRepository->save($author);

            $this->style->success('Saved');

            $addAnother = $helper->ask($this->input, $this->output, new ConfirmationQuestion('Add another? ', false));

            if (!$addAnother) {
                break;
            }
        }
    }

    private function createModel(
        $firstName,
        $lastName,
        $birthDay,
        $biography,
        $gender,
        $placeOfBirth
    ): Author {
        Assert::allStringNotEmpty(
            [
                $firstName,
                $lastName,
                $birthDay,
                $placeOfBirth
            ]
        );

        Assert::oneOf($gender, ['male', 'female']);

        $birthDayDate = DateTime::createFromFormat('Y-m-d', $birthDay);

        Assert::notFalse($birthDayDate);

        return new Author(
            null,
            $firstName,
            $lastName,
            $birthDayDate,
            $placeOfBirth,
            $biography,
            $gender,
        );
    }

    private function authenticate(string $email, string $password)
    {
        $tokenData = $this->authenticationService->getToken($email, $password);

        $token = new ApiToken($tokenData);

        $this->tokenStorage->setToken($token);
    }
}
