<?php

namespace Command;

class NewEntity
{
    public static function init()
    {
        $baseDir = str_replace("\\", "/", realpath(__DIR__ . "/../"));
        $sucCount = 0;
        $errCount = 0;

        echo "[ENTITY FILES GENERATOR] Starting from $baseDir\n\n";

        $entityNameInput = trim((string) readline("Entity name: "));
        while (empty($entityNameInput)) {
            echo "Invalid entity name!\n";
            $entityNameInput = trim((string) readline("Entity name: "));
        }

        $convertedInput = iconv('UTF-8', 'ASCII//TRANSLIT', $entityNameInput);
        $normalizedInput = preg_replace("/[\s\-_]+/", " ", $convertedInput);

        $entityName = implode("", array_map('ucfirst', explode(" ", $normalizedInput)));

        $exceptionInput = self::confirmInput("Create 'NotFoundException' for $entityName?");
        $implementationInput = self::confirmInput("Create repository implementation for $entityName?");
        $viewInput = self::confirmInput("Create default view page for $entityName?");

        echo "\n[ACTIONS]\n";

        // Pasta {Nome} dentro de App\Application\Actions
        self::createDirectory("src/Application/Actions/$entityName", $baseDir, $sucCount, $errCount);

        // Arquivo {Nome}Action.php dentro de App\Application\Actions\{Nome}
        $actionFilePath = self::createFile("src/Application/Actions/$entityName/$entityName" . 'Action.php', $baseDir, $sucCount, $errCount);
        self::writeFileContent(FileType::Action, $actionFilePath, $entityName);

        echo "\n[DOMAIN]\n";

        // Pasta {Nome} dentro de App\Domain
        self::createDirectory("src/Domain/$entityName", $baseDir, $sucCount, $errCount);

        // Arquivo {Nome}.php dentro da Pasta App\Domain\{Nome}
        $entityFilePath = self::createFile("src/Domain/$entityName/$entityName.php", $baseDir, $sucCount, $errCount);
        self::writeFileContent(FileType::Entity, $entityFilePath, $entityName);

        // Arquivo {Nome}Repository.php dentro da Pasta App\Domain\{Nome}
        $repositoryFilePath = self::createFile("src/Domain/$entityName/$entityName" . 'Repository.php', $baseDir, $sucCount, $errCount);
        self::writeFileContent(FileType::Repository, $repositoryFilePath, $entityName);

        if ($exceptionInput) {
            // Arquivo {Nome}NotFoundException.php dentro da Pasta App\Domain\{Nome}
            $exceptionFilePath = self::createFile("src/Domain/$entityName/$entityName" . 'NotFoundException.php', $baseDir, $sucCount, $errCount);
            self::writeFileContent(FileType::NotFoundException, $exceptionFilePath, $entityName);
        }

        if ($implementationInput) {
            echo "\n[PERSISTENCE]\n";

            // Pasta {Nome} dentro de App\Infrastructure\Persistence
            self::createDirectory("/src/Infrastructure/Persistence/$entityName", $baseDir, $sucCount, $errCount);

            // Arquivo {Nome}RepositoryImplementation.php dentro da Pasta App\Infrastructure\Persistence\{Nome}
            $implementationFilePath = self::createFile("src/Infrastructure/Persistence/$entityName/$entityName" . 'RepositoryImplementation.php', $baseDir, $sucCount, $errCount);
            self::writeFileContent(FileType::RepositoryImplementation, $implementationFilePath, $entityName);
        }

        if ($viewInput) {
            echo "\n[VIEWS]\n";

            // Pasta {Nome} dentro de App\Application\Views\Pages
            self::createDirectory("src/Application/Views/Pages/$entityName", $baseDir, $sucCount, $errCount);

            // Arquivo {Nome}.html dentro de App\Application\Views\Pages\{Nome}
            $viewFilePath = self::createFile("src/Application/Views/Pages/$entityName/page.html", $baseDir, $sucCount, $errCount);
            self::writeFileContent(FileType::View, $viewFilePath, $entityName);
        }

        if ($implementationInput) {
            echo "\n[REPOSITORY IMPLEMENTATION WARNING]\n";
            echo "To make it work, declare the repository autowired implementation to its interface in '$baseDir/app/repositories.php': \n";
            echo "...\n" . '$containerBuilder->addDefinitions([';
            echo "\n\t" . $entityName . 'Repository::class => \DI\autowire(' . $entityName . 'RepositoryImplementation::class)';
            echo "\n]);\n...\n";
        }

        echo "\nScript executed with $errCount errors and $sucCount succeeded operations.\n";
    }

    public static function confirmInput(string $prompt): bool
    {
        $input = strtolower((string) readline($prompt . " [Y/n]: "));

        while ($input !== "y" && $input !== "" && $input !== "n") {
            echo "Invalid option!\n";
            $input = strtolower((string) readline($prompt . " [Y/n]:"));
        }

        return $input === "y" || $input === "";
    }

    public static function createDirectory(string $path, string $baseDir, int &$sucCount, int &$errCount)
    {
        echo "Creating directory $path ......  ";
        $fullPath = $baseDir . '/' . $path;

        if (mkdir($fullPath, 0777, true)) {
            echo "OK!\n";
            $sucCount++;
        } else {
            echo "ERROR =(\n";
            $errCount++;
        }

        return file_exists($path);
    }

    public static function createFile(string $path, string $baseDir, int &$sucCount, int &$errCount): string
    {
        echo "Creating file $path ......  ";
        $fullPath = $baseDir . '/' . $path;

        if (touch($fullPath)) {
            echo "OK!\n";
            $sucCount++;
        } else {
            echo "ERROR =(\n";
            $errCount++;
        }

        return $fullPath;
    }

    public static function writeFileContent(FileType $fileType, string $path, string $entityName)
    {
        echo "Writing on $path ......  ";

        $handler = fopen($path, 'w');

        if (!$handler) {
            echo "ERROR =(\n";
            return $fileType;
        }

        switch ($fileType) {
            case FileType::Action:
                $content = strtr('<?php

declare(strict_types=1);

namespace App\Application\Actions\{{ entityName }};

use App\Application\Actions\Action;
use App\Domain\{{ entityName }}\{{ entityName }}Repository;

abstract class {{ entityName }}Action extends Action
{
    protected {{ entityName }}Repository ${{ lowerEntityName }}Repository;

    public function __construct(
        {{ entityName }}Repository ${{ lowerEntityName }}Repository
    ) {
        parent::__construct();
        $this->{{ lowerEntityName }}Repository = ${{ lowerEntityName }}Repository;
    }
}
',
                    [
                        '{{ entityName }}' => $entityName,
                        '{{ lowerEntityName }}' => lcfirst($entityName),
                    ]);
                break;
            case FileType::View:
                $content = '{% extends "Templates/base.html" %}

{% block title %}' . $entityName . '{% endblock %}

{% block styles %}{% endblock %}

{% block content %}{% endblock %}

{% block scripts %}{% endblock %}';
                break;
            case FileType::Entity:
                $content = strtr('<?php

declare(strict_types=1);

namespace App\Domain\{{ entityName }};

use App\Domain\Entity;
use JsonSerializable;

class {{ entityName }} implements Entity, JsonSerializable
{
    public function __construct()
    {
    }

    public function __get(string $name)
    {
        return property_exists($this, $name) ? $this->$name : null;
    }

    public function __set(string $name, $value): void
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }

    public static function fromJson(array $json): {{ entityName }}
    {
        return new {{ entityName }}();
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return [];
    }
}
',
                    [
                        '{{ entityName }}' => $entityName
                    ]);
                break;
            case FileType::Repository:
                $content = strtr('<?php

declare(strict_types=1);

namespace App\Domain\{{ entityName }};

interface {{ entityName }}Repository
{
    // Use this interface to declare repository methods to be implemented
}
',
                    [
                        '{{ entityName }}' => $entityName
                    ]);
                break;
            case FileType::NotFoundException:
                $content = strtr('<?php

declare(strict_types=1);

namespace App\Domain\{{ entityName }};

use App\Domain\DomainException\DomainRecordNotFoundException;

class {{ entityName }}NotFoundException extends DomainRecordNotFoundException
{
    public $message = "The {{ lowerEntityName }} you requested does not exist.";
}
',
                    [
                        '{{ entityName }}' => $entityName,
                        '{{ lowerEntityName }}' => lcfirst($entityName),
                    ]);
                break;
            case FileType::RepositoryImplementation:
                $content = strtr('<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\{{ entityName }};

use App\Domain\{{ entityName }}\{{ entityName }}Repository;

class {{ entityName }}RepositoryImplementation implements {{ entityName }}Repository
{
    // Declare dependencies to be autowired in the constructor
    public function __construct()
    {
    }

    // Use this class to implement repository interface methods
}
',
                    [
                        '{{ entityName }}' => $entityName
                    ]);
                break;
            default:
                $content = "<?php\n";
                break;
        }

        if (!fwrite($handler, $content)) {
            echo "ERROR =(\n";
        } else {
            echo "OK!\n";
        }

        return $fileType;
    }
}
