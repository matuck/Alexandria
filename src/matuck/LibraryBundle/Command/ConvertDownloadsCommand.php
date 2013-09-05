<?php
namespace matuck\LibraryBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use matuck\LibraryBundle\Entity\Download;
use matuck\LibraryBundle\Entity\DownloadRepository;

class ConvertDownloadsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('library:downloads:convertdownloads')
            ->setDescription('Converts the downloads table')
            ->setHelp('Converts the downloads table into daily downloads and book downloads.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn = $this->getContainer()->get('doctrine')->getManager()->getConnection();
        /* @var $conn \Doctrine\DBAL\Connection */
        $res = $conn->executeQuery('SELECT DATE(created_at) date, count(*) as dcount FROM download GROUP BY DATE(created_at)')
                ->fetchAll();
        foreach($res as $day)
        {
            echo $day['date']."\t".$day['dcount']."\n";
            $res2 = $conn->executeQuery('SELECT * FROM dailydownloads WHERE day = "'.$day['date'].'  00:00:00"')->fetch();
            if($res2 === FALSE)
            {
                $conn->executeQuery("INSERT INTO dailydownloads (day, downloads) VALUES ('".$day['date']."',".$day['dcount'].") ");
                echo sprintf('Inserting %s with downloads %d', $day['date'], $day['dcount'])."\n\n";
            }
            else
            {
                echo "The day already exist going to run update\n\n";
                $newcount = $res2['downloads'] + $day['dcount'];
                echo sprintf("The existing count %d, the additional count %d, the new count %d.\n\n", $res2['downloads'], $day['dcount'], $newcount);
                $conn->executeQuery(sprintf('UPDATE dailydownloads SET downloads = %d WHERE day = "%s"', $newcount, $res2['day']));
            }
        }
        echo "Adding downloads table book counts to the book.\n\n";
        $conn->executeQuery('UPDATE book SET book.downcount = IFNULL(book.downcount, 0) + ( SELECT COUNT( d.book_id ) FROM download d WHERE d.book_id = book.id )');
        $conn->executeQuery('TRUNCATE TABLE download');
    }
}