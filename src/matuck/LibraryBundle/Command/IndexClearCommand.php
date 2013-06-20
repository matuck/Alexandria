<?php
namespace matuck\LibraryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IndexClearCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('library:index:clear')
            ->setDescription('Erases an index specified at runtime.')
            ->addArgument('index', InputArgument::REQUIRED, 'Name of index to clear?')
            ->setHelp('Erases/Clears an index specified in runtime.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexname = $input->getArgument('index');
        $search = $this->getContainer()->get('ivory_lucene_search');
        /* @var $search \Ivory\LuceneSearchBundle\Model\LuceneManager */
        $search->eraseIndex($indexname);
        $output->writeln(sprintf('%s has been cleared successfully!', $indexname));
    }
}