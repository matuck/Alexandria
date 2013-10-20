<?php
namespace matuck\LibraryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use matuck\LibraryBundle\Entity\Author;
use matuck\LibraryBundle\Entity\AuthorRepository;
use Ivory\LuceneSearchBundle\Model\Document;
use Ivory\LuceneSearchBundle\Model\Field;

class IndexAuthorsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('library:index:authors')
            ->setDescription('Indexes authors for search')
            ->setHelp('This command indexes authors so that it may be searched more easily.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        /* @var $em \Doctrine\ORM\EntityManager */
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        
        $indexer = $this->getContainer()->get('matuck_library.searchindexer');
        /* @var $indexer \matuck\LibraryBundle\Lib\Indexer */
        
        $authorrepo = $em->getRepository('matuckLibraryBundle:Author');
        /* @var $authorrepo AuthorRepository */
        $allauthors = $authorrepo->findAll();
        $count = 0;
        foreach($allauthors as $author)
        {
            $indexer->indexAuthor($author[0]);
            echo sprintf('%s was added to the index', $author[0]->getName())."\n";
            $count++;
            $em->detach($author[0]);
        }
        $indexer->commit();
        echo "\n\nStarting to optimize the index.";
        $indexer->optimize();
        $output->writeln(' ');
        $output->writeln(sprintf('%d authors were added to the index', $count));
    }
}