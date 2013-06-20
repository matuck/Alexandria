<?php
namespace matuck\LibraryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use matuck\LibraryBundle\Entity\Serie;
use matuck\LibraryBundle\Entity\SerieRepository;
use Ivory\LuceneSearchBundle\Model\Document;
use Ivory\LuceneSearchBundle\Model\Field;

class IndexSeriesCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('library:index:series')
            ->setDescription('Indexes series for search')
            ->setHelp('This command indexes series so that it may be searched more easily.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        /* @var $em \Doctrine\ORM\EntityManager */
        $index = $this->getContainer()->get('ivory_lucene_search')->getIndex('master');
        /* @var $index \Zend\Search\Lucene\Index */
        $serierepo = $em->getRepository('matuckLibraryBundle:Serie');
        /* @var $serierepo SerieRepository */
        $allseries = $serierepo->findAll();
        $count = 0;
        foreach($allseries as $series)
        {
            $doc = new Document();
            $doc->addField(Field::keyword('type', 'serie'));
            $doc->addField(Field::binary('objid', $series[0]->getId()));
            $doc->addField(Field::text('name', $series[0]->getName()));
            $index->addDocument($doc);
            echo sprintf('%s was added to the index', $series[0]->getName())."\n";
            $count++;
            $em->detach($series[0]);
        }
        $index->commit();
        echo "\n\nStarting to optimize the index.";
        $index->optimize();
        $output->writeln(' ');
        $output->writeln(sprintf('%d series were added to the index', $count));
    }
}