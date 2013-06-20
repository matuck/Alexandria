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
        $index = $this->getContainer()->get('ivory_lucene_search')->getIndex('master');
        /* @var $index \Zend\Search\Lucene\Index */
        $authorrepo = $em->getRepository('matuckLibraryBundle:Author');
        /* @var $authorrepo AuthorRepository */
        $allauthors = $authorrepo->findAll();
        $count = 0;
        foreach($allauthors as $author)
        {
            $doc = new Document();
            $doc->addField(Field::keyword('type', 'author'));
            $doc->addField(Field::binary('objid', $author[0]->getId()));
            $doc->addField(Field::text('name', $author[0]->getName()));
            $doc->addField(Field::text('bio', $author[0]->getBiography()));
            $index->addDocument($doc);
            echo sprintf('%s was added to the index', $author[0]->getName())."\n";
            $count++;
            $em->detach($author[0]);
        }
        $index->commit();
        echo "\n\nStarting to optimize the index.";
        $index->optimize();
        $output->writeln(' ');
        $output->writeln(sprintf('%d authors were added to the index', $count));
    }
}