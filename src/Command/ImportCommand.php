<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Fods;
use App\Entity\Task;
use App\Entity\Edition;
use App\Entity\Compilation;
use App\Entity\Register;
use App\Entity\Correction;
use DateTime;

ini_set('memory_limit', -1);

class ImportCommand extends ReadFodsCommand
{
    protected $entityManager = null;

    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:import';

    static function fallback($value, $fallback){
      if(!isset($value) || $value === null || $value === '')
      {
        return $fallback;
      }
      return $value;
    }

    function __construct(Fods $fods, EntityManagerInterface $entityManager){
	      $this->entityManager = $entityManager;
        parent::__construct($fods);
    }

    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('dry-run', InputArgument::OPTIONAL, 'just simulate, don’t write to database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);
        $flushCounter = 1;

        $editionRepository     = $this->entityManager->getRepository(Edition::class);
        $registerRepository    = $this->entityManager->getRepository(Register::class);
        $compilationRepository = $this->entityManager->getRepository(Compilation::class);

        foreach($this->dataTable as $row){

          $correction = new Correction();

          if($register = $registerRepository->findOneBy(['id' => $row['register_id']])){
            $correction->addRegisterEntry($register);
          }
          if($compilation = $compilationRepository->findOneBy(['id' => $row['compilation_id']])){
            $correction->setCompilation($compilation);
          } else {
            echo $flushCounter . ': ACHTUNG: keine compilation (' . $row['compilation_id'] . ') '. implode('|',$row) . "\n";
            continue;
          }
          if($edition = $editionRepository->findOneBy(['id' => $row['edition_id']])){
            $correction->setEdition($edition);
          } else {
            echo $flushCounter . ': ACHTUNG: keine edition (' . $row['edition_id'] . ') '. implode('|',$row) . "\n";
            continue;
          }

          $taskList = [];
          $taskList['ddb'] = self::fallback($row['task_ddb'], null);
          foreach($taskList as $category => $description){
            if($description){
              $task = new Task();
              $task->setCategory($category);
              $task->setDescription($description);
              $task->setCorrection($correction);
              $this->entityManager->persist($task);
            }
          }

          $correction->setCompilationPage(self::fallback($row['compilation_page'], null));
          $correction->setText(self::fallback($row['text'], null));
          $correction->setPosition(self::fallback($row['position'], null));
          $correction->setDescription($row['description']);
          $correction->setSource(self::fallback($row['source'], null));
          $correction->setCreator(self::fallback($row['creator'], 'system'));

          echo $flushCounter . ': ' . implode('|',$row) . "\n";

          $this->entityManager->persist($correction);
          if(!($flushCounter++ % 500) && !$input->getArgument('dry-run')){
            $this->entityManager->flush();
            echo '________________________________ F L U S H ________________________________' . "\n";
          }

        }
        if(!$input->getArgument('dry-run')){
          $this->entityManager->flush();
        }

        return Command::SUCCESS;
        // return Command::FAILURE;
        // return Command::INVALID;
    }

}
