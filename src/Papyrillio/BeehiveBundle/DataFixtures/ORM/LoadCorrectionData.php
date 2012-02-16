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
          array('bl' => '146', 'edition' => 'P. Bingen', 'text' => '129', 'tm' => '23633', 'hgv' => '23633', 'ddb' => 'p.ammon;2;27', 'position' => '10-11', 'description' => 'Die Indiktion hat schon vor dem 1. Thoth angefangen, vgl. dazu P. Oxy. 68. 4681, Anm. zu Z. 9-11.', 'task' => array()),
          array('bl' => '175', 'edition' => 'P. Cairo Masp. 1', 'text' => '67090', 'tm' => '23633', 'hgv' => '23633', 'ddb' => 'p.ammon;2;27', 'position' => 'R°', 'description' => 'Photo: J.-L. Fournet, in: Des Alexandries II, S. 79.', 'task' => array()),
          array('bl' => '266', 'edition' => 'P. Coll.Youtie 2', 'text' => '69', 'tm' => '23633', 'hgv' => '23633', 'ddb' => 'p.ammon;2;27', 'position' => '7-8', 'description' => 'Zu Ἀντ̣ι|[νοείῳ vgl. J.-Y. Strasser, B.C.H. 128-129 (2004-2005), S. 467, Anm. 226 (zu P. Agon. 9).', 'task' => array()),
          array('bl' => '312', 'edition' => 'C.P.R. 5', 'text' => '24', 'tm' => '23633', 'hgv' => '23633', 'ddb' => 'p.ammon;2;27', 'position' => '', 'description' => 'Herkunft: Oxyrhynchites, P. Oxy. 68. 4685 V°, Anm. zu Z. 1.', 'task' => array()),
          array('bl' => '312', 'edition' => 'C.P.R. 5', 'text' => '24', 'tm' => '23633', 'hgv' => '23633', 'ddb' => 'p.ammon;2;27', 'position' => '2', 'description' => 'Derselbe Theodoros Sohn des Leukadios in P. Oxy. 68. 4685 V°, Z. 1; viell. derselbe Leukadios auch in P. Oxy. 7. 1048, Z. 15, vgl. P. Oxy. 68. 4685 V°, Anm. zu Z. 1.', 'task' => array()),
          array('bl' => '312', 'edition' => 'C.P.R. 5', 'text' => '24', 'tm' => '23633', 'hgv' => '23633', 'ddb' => 'p.ammon;2;27', 'position' => '3, 7', 'description' => 'Derselbe Daniel, Sohn des Valerius in P. Oxy. 68. 4682, Z. 4-5, 4683, Z. 1 (?) und 4685 V°, Z. 8; wohl derselbe Valerius auch in P. Wash.Univ. 2. 83, Z. 7, P. Oxy. 7. 1048, Z. 10, und 62. 4346, Z. 2, vgl. P. Oxy. 68. 4682, Anm. zu Z. 4-5.', 'task' => array()),
          array('bl' => '312', 'edition' => 'C.P.R. 5', 'text' => '24', 'tm' => '23633', 'hgv' => '23633', 'ddb' => 'p.ammon;2;27', 'position' => '6, 10', 'description' => 'Derselbe Ammonianos viell. in P. Oxy. 68. 4685 R°, Z. 8, vgl. dort Anm. zur Z.', 'task' => array()),
          array('bl' => '377', 'edition' => 'P. Flor. 3', 'text' => '325', 'tm' => '23633', 'hgv' => '23633', 'ddb' => 'p.ammon;2;27', 'position' => '8', 'description' => 'πρίνκιπος → πολιτευομένου, K.A. Worp in: P. Oxy. 68. 4687, Anm. zu Z. 8-9 (am Original geprüft von R. Pintaudi).', 'task' => array()),
          array('bl' => '496', 'edition' => 'P. Hib. 2', 'text' => '198', 'tm' => '23633', 'hgv' => '23633', 'ddb' => 'p.ammon;2;27', 'position' => '231', 'description' => 'οἱ τὰ βασιλ̣[ικὰ (erg. viell. πραγματευόμενοι, ed.pr., Anm. zur Z.) ]σοντ[ → οἱ τὰ βασιλ[ικὰ πράσ]σοντ[ες], J.-M. Bertrand in: La circulation de l’information, S. 99-100.', 'task' => array()),
          array('bl' => '524', 'edition' => 'P. Köln 5', 'text' => '234', 'tm' => '23633', 'hgv' => '23633', 'ddb' => 'p.ammon;2;27', 'position' => '3', 'description' => 'Die Erg. ῥιπαρίῳ wird bestätigt; derselbe Flavius Ioseph in P. Oxy. 68. 4684, Z. 3 und viell. in 4685 V°, Z. 3, vgl. P. Oxy. 68. 4684, Anm. zu Z. 3 und 4685, Einl.', 'task' => array()),
          array('bl' => '552', 'edition' => 'P. Laur. 3', 'text' => '74', 'tm' => '23633', 'hgv' => '23633', 'ddb' => 'p.ammon;2;27', 'position' => '29', 'description' => ']ν̣ → Ὡρείω]ν̣, J.-Y. Strasser, B.C.H. 128-129 (2004-2005), S. 427, Anm. 36.', 'task' => array()),
          array('bl' => '553', 'edition' => 'P. Laur. 4', 'text' => '192', 'tm' => '23633', 'hgv' => '23633', 'ddb' => 'p.ammon;2;27', 'position' => '7', 'description' => 'ἡμεριῶν: wohl nicht von ἡμερία, sondern vom Diminutiv ἡμέριον, N. Gonis, Bibl.Orient. 62 (2005), S. 51.', 'task' => array()),
          array('bl' => '584', 'edition' => 'P. Lond. 5', 'text' => '1793', 'tm' => '23633', 'hgv' => '23633', 'ddb' => 'p.ammon;2;27', 'position' => '', 'description' => 'Die Datierung 2.12.471 n.Chr. (vgl. B.L. 8, S. 193) ist vorzuziehen, P. Oxy. 68. 4695, Anm. zu Z. 2-3.', 'task' => array()),
          array('bl' => '603', 'edition' => 'P. L.Bat. 25', 'text' => '70', 'tm' => '23633', 'hgv' => '23633', 'ddb' => 'p.ammon;2;27', 'position' => '2', 'description' => 'ἐν Ὀξυρυγχ(ιτῶν) → ἐν Ὀξυρύγχ(ων), P. Oxy. 68. 4701, Anm. zu Z. 2.', 'task' => array()),
          array('bl' => '636', 'edition' => 'P. Med. 12', 'text' => '64', 'tm' => '23633', 'hgv' => '23633', 'ddb' => 'p.ammon;2;27', 'position' => '1', 'description' => '[μετὰ τὴν ὑπατείαν (B.L. 7, S. 103): wenn geschrieben, dann fehlerhaft für ὑπατείας, P. Oxy. 68. 4688, Anm. zu Z. 2.', 'task' => array()),
          array('bl' => '636', 'edition' => 'P. Med. 12', 'text' => '64', 'tm' => '23633', 'hgv' => '23633', 'ddb' => 'p.ammon;2;27', 'position' => '5', 'description' => 'Die Erg. [ἐναπόγραφος wird abgelehnt, P. Oxy. 68. 4697, Anm. zu Z. 6-7.', 'task' => array()),
          array('bl' => '636', 'edition' => 'P. Med. 12', 'text' => '64', 'tm' => '23633', 'hgv' => '23633', 'ddb' => 'p.ammon;2;27', 'position' => '9', 'description' => 'δ[ε]κ̣άτης (B.L. 7, S. 103) → ἐνάτης (wie ed.pr.) (nach dem Photo); also zu datieren: 6.12.440 n.Chr., P. Oxy. 68. 4688, Anm. zu Z. 2.', 'task' => array(
          'hgv' => array('d' => 'Jen ator 23 inter ju, hago.', 'c' => '2010-01-13'),
          'ddb' => array('d' => 'Pluso elnombrado sat io, ador vortfarado', 'c' => '2010-01-13'),
          'apis' => array('d' => 'Sub nf timi nuna 23 trans 89 mf dekuma.', 'c' => '2010-01-13'),
          'tm' => array('d' => 'El sep frota refleksiva, aga timi iama.', 'c' => null),
          'bl' => array('d' => 'Ts tet vasta deloke 1. demandosigno 1932.', 'c' => null)
          )),

        );

        foreach($dataList as $data){
          $correction = new Correction();
          $correction->setBl($data['bl']);
          $correction->setEdition($data['edition']);
          $correction->setText($data['text']);
          $correction->setTm($data['tm']);
          $correction->setHgv($data['hgv']);
          $correction->setDdb($data['ddb']);
          $correction->setPosition($data['position']);
          $correction->setDescription($data['description']);
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