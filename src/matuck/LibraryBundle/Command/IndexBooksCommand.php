<?php
namespace matuck\LibraryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use matuck\LibraryBundle\Entity\Book;
use matuck\LibraryBundle\Entity\BookRepository;
use Ivory\LuceneSearchBundle\Model\Document;
use Ivory\LuceneSearchBundle\Model\Field;

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
        $index = $this->getContainer()->get('ivory_lucene_search')->getIndex('master');
        /* @var $index \Zend\Search\Lucene\Index */
        $bookrepo = $em->getRepository('matuckLibraryBundle:Book');
        /* @var $bookrepo BookRepository */
        $allbooks = $bookrepo->findAll();
        $count = 0;
        foreach($allbooks as $book)
        {
            $doc = new Document();
            $doc->addField(Field::keyword('type', 'book'));
            $doc->addField(Field::binary('objid', $book[0]->getId()));
            $doc->addField(Field::text('title', $book[0]->getTitle()));
            $doc->addField(Field::text('author', $book[0]->getAuthor()->getName()));
            $doc->addField(Field::binary('authorid', $book[0]->getAuthor()->getId()));
            if($series = $book[0]->getSerie())
            {
                $doc->addField(Field::text('series', $series->getName()));
                $doc->addField(Field::binary('serieid', $series->getId()));
                $doc->addField(Field::unIndexed('serieNbr', $book[0]->getSerieNbr()));
            }
            $doc->addField(Field::unIndexed('summary', $book[0]->getSummary()));
            $index->addDocument($doc);
            echo sprintf('%s was added to the index', $book[0]->getTitle())."\n";
            $count++;
            $em->detach($book[0]);
        }
        $index->commit();
        echo "\n\nStarting to optimize the index.";
        $index->optimize();
        $output->writeln(' ');
        $output->writeln(sprintf('%d books were added to the index', $count));
    }
        
}