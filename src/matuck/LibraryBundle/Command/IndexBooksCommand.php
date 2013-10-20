<?php
namespace matuck\LibraryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use matuck\LibraryBundle\Entity\Book;
use matuck\LibraryBundle\Entity\BookRepository;

class IndexBooksCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('library:index:books')
            ->setDescription('Indexes books for search')
            ->setHelp('This command indexes the book so that it may be searched more easily.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        /* @var $em \Doctrine\ORM\EntityManager */
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        
        $indexer = $this->getContainer()->get('matuck_library.searchindexer');
        /* @var $indexer \matuck\LibraryBundle\Lib\Indexer */
        
        $bookrepo = $em->getRepository('matuckLibraryBundle:Book');
        /* @var $bookrepo BookRepository */
        $allbooks = $bookrepo->findAll();
        $count = 0;
        foreach($allbooks as $book)
        {
            $indexer->indexBook($book[0]);
            echo sprintf('%s was added to the index', $book[0]->getTitle())."\n";
            $count++;
            $em->detach($book[0]);
        }
        $indexer->commit();
        echo "\n\nStarting to optimize the index.";
        $indexer->optimize();
        $output->writeln(' ');
        $output->writeln(sprintf('%d books were added to the index', $count));
    }
        
}