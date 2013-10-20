<?php
namespace matuck\LibraryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use matuck\LibraryBundle\Entity\Serie;
use matuck\LibraryBundle\Entity\SerieRepository;

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
        $em->getConnection()->getConfiguration()->setSQLLogger(null);
        
        $indexer = $this->getContainer()->get('matuck_library.searchindexer');
        /* @var $indexer \matuck\LibraryBundle\Lib\Indexer */
        $serierepo = $em->getRepository('matuckLibraryBundle:Serie');
        /* @var $serierepo SerieRepository */
        $allseries = $serierepo->findAll();
        $count = 0;
        foreach($allseries as $series)
        {
            $indexer->indexSeries($series[0]);
            echo sprintf('%s was added to the index', $series[0]->getName())."\n";
            $count++;
            $em->detach($series[0]);
        }
        $indexer->commit();
        echo "\n\nStarting to optimize the index.";
        $indexer->optimize();
        $output->writeln(' ');
        $output->writeln(sprintf('%d series were added to the index', $count));
    }
}