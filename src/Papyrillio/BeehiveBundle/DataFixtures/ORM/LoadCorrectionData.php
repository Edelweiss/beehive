<?php
namespace Papyrillio\BeehiveBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Papyrillio\BeehiveBundle\Entity\Compilation;
use Papyrillio\BeehiveBundle\Entity\Correction;
use Papyrillio\BeehiveBundle\Entity\Task;
use DateTime;

class LoadCorrectionData extends AbstractFixture implements OrderedFixtureInterface
{
    function load(ObjectManager $manager)
    {
        $dataList = array(
          array('b' => 125, 't' => 12345, 'h' => '12345', 'd' => 'z.elda;8;16', 'p' => 'havi 3', 'desc' => 'δημιουργια παράδειγμα → δημιουργια όχι παράδειγμα; Da pre jeno havi mekao, igi os tuje oficiala (Jes elparolo antaŭelemento ad c.rono/c.ross 123;98)', 'task' => array()),
          array('b' => 125, 't' => 12345, 'h' => '12345', 'd' => 'z.elda;8;16', 'p' => 'havi 4, numero 78', 'desc' => 'δημιουργια παράδειγμα → δημιουργια όχι παράδειγμα; Da pre jeno havi mekao, igi os tuje oficiala (Jes elparolo antaŭelemento ad c.rono/c.ross 123;98)', 'task' => array()),
          array('b' => 125, 't' => 12345, 'h' => '12345', 'd' => 'z.elda;8;16', 'p' => 'igi 4/78', 'desc' => 'Κρατάει δεδομένη → ιδιαίτερα τις πω', 'task' => array()),
          array('b' => 125, 't' => 12345, 'h' => '12345', 'd' => 'z.elda;8;16', 'p' => 'Edzo 478;001', 'desc' => 'Δύο κι τεσσαρών → Δύο κι τεσσαρώνήδη', 'task' => array()),
          
          array('b' => 126, 't' => 12359, 'h' => '12359a', 'd' => 'l.ink;;49', 'p' => 'mekao 234 d', 'desc' => 'Ζητήσεις δημιουργια παράδειγμα ματ το, οι τρόπο πετούν ανά. Λαμβάνουν εκτελείται όχι σε, μικρής μετράει προσοχή στη τη. Ροή διακοπής σφαλμάτων εφαμοργής τι. Με βγήκε ατόμου γραφικά ήδη. Επιτυχία εργαζόμενοι έξι δε, σημεία τρέξει δυσκολότερο θα όρο. Ανά συνεχώς προσθέσει χρονοδιαγράμματα οι, κάποιο φακέλους γειτονιάς να πιο.', 'task' => array()),
          array('b' => 126, 't' => 12359, 'h' => '12359b', 'd' => 'l.ink;;49', 'p' => 'mekao 342 e', 'desc' => 'ξέχασε → ξέκόσε', 'task' => array()),
          array('b' => 126, 't' => 12359, 'h' => '12359c', 'd' => 'l.ink;;49', 'p' => 'mekao 989 r', 'desc' => 'εκτός → εκτόςύψος', 'task' => array()),
          
          array('b' => 321, 't' => 878, 'h' => '878', 'd' => 'G.Anon II 1234;78', 'p' => 'iama IIa', 'desc' => 'ως → ως tuje oficiala τέτοιο', 'task' => array()),
          array('b' => 321, 't' => 878, 'h' => '878', 'd' => 'G.Anon II 1234;78', 'p' => 'iama IIb (havi mekao)', 'desc' => 'Si ano trae troa, ot nome sensubjekta muo. Gingivalo nederlando haltostreko nen ve. Kiam geto morgaŭo ik dev. Ene hu pako internacia, tek mo kunigi tutampleksa (απομόνωση προκαλείς συνεντεύξεις ως πες).', 'task' => array()),
          
          array('b' => 345, 't' => 23633, 'h' => '23633', 'd' => 'p.ammon;2;27', 'p' => 'alikaŭze 1-4', 'desc' => 'Plue (ξεχειλίζει αντιλήφθηκαν) ni geto kilometro inkluzive, am enz kab\'o alikaŭze. U ist jaro frazo. Nen at participo posttagmezo, eŭro intera ies vi. Tiuj alies centimetro cii on.', 'task' => array(
          'hgv' => array('d' => 'Jen ator 23 inter ju, hago.', 'c' => '2010-01-13'),
          'ddb' => array('d' => 'Pluso elnombrado sat io, ador vortfarado', 'c' => '2010-01-13'),
          'apis' => array('d' => 'Sub nf timi nuna 23 trans 89 mf dekuma.', 'c' => '2010-01-13'),
          'tm' => array('d' => 'El sep frota refleksiva, aga timi iama.', 'c' => null),
          'bl' => array('d' => 'Ts tet vasta deloke 1. demandosigno 1932.', 'c' => null)
          )),

        );

        foreach($dataList as $data){
          $correction = new Correction();
          $correction->setBl($data['b']);
          $correction->setTm($data['t']);
          $correction->setHgv($data['h']);
          $correction->setDdb($data['d']);
          $correction->setPosition($data['p']);
          $correction->setDescription($data['desc']);
          $correction->setCompilation($this->getReference('compilation'));
          
          foreach($data['task'] as $category => $taskData){
            $task = new Task();
            $task->setCategory($category);
            $task->setDescription($taskData['d']);
            if($taskData['c']){
              $task->setCleared(new DateTime($taskData['c']));
            }
            $task->setCorrection($correction);
            $manager->persist($task);
          }

          $manager->persist($correction);
        }

        $manager->flush();
    }

    public function getOrder()
    {
        return 2;
    }
}
?>