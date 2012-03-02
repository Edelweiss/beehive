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
          array('bl' => '146', 'edition' => 'P. Bingen', 'text' => '129', 'tm' => '19768', 'hgv' => '19768', 'ddb' => 'p.bingen;;129', 'biblio' => 0, 'position' => '10-11', 'description' => 'Die Indiktion hat schon vor dem 1. Thoth angefangen, vgl. dazu P. Oxy. 68. 4681, Anm. zu Z. 9-11.', 'task' => array(
            'hgv' => array( 'd' => 'add Bemerkung HGV (via James?) Zum Datum vgl. P. Oxy. 68. 4681, Anm. zu Z. 9-11. or whole remark?')
          )),
          array('bl' => '175', 'edition' => 'P. Cairo Masp. 1', 'text' => '67090', 'tm' => '36829', 'hgv' => '36829', 'ddb' => 'p.cair.masp;1;67090', 'biblio' => 17690, 'position' => 'R°', 'description' => 'Photo: J.-L. Fournet, in: Des Alexandries II, S. 79.', 'task' => array(
            'hgv' => array( 'd' => 'add HGV: Abbildung: J.-L. Fournet, in: Des Alexandries II, S. 79.')
          )),
          array('bl' => '266', 'edition' => 'P. Coll.Youtie 2', 'text' => '69', 'tm' => '20889', 'hgv' => '20889', 'ddb' => 'pap.agon;;9', 'biblio' => 73664, 'position' => '7-8', 'description' => 'Zu Ἀντ̣ι|[νοείῳ vgl. J.-Y. Strasser, B.C.H. 128-129 (2004-2005), S. 467, Anm. 226 (zu P. Agon. 9).', 'task' => array(
            'ddb' => array( 'd' => 'add comlete remark, followed by [BL 13], in DDbDP commentary-line via SoSOL')
          )),
          array('bl' => '312', 'edition' => 'C.P.R. 5', 'text' => '24', 'tm' => '34844', 'hgv' => '34844', 'ddb' => 'cpr;5;24', 'biblio' => 0, 'position' => null, 'description' => 'Herkunft: Oxyrhynchites, P. Oxy. 68. 4685 V°, Anm. zu Z. 1.', 'task' => array(
            'hgv' => array( 'd' => 'already in HGV', 'c' => '2012-01-01')
          )),
          array('bl' => '312', 'edition' => 'C.P.R. 5', 'text' => '24', 'tm' => '34844', 'hgv' => '34844', 'ddb' => 'cpr;5;24', 'biblio' => 0, 'position' => '2', 'description' => 'Derselbe Theodoros Sohn des Leukadios in P. Oxy. 68. 4685 V°, Z. 1; viell. derselbe Leukadios auch in P. Oxy. 7. 1048, Z. 15, vgl. P. Oxy. 68. 4685 V°, Anm. zu Z. 1.', 'task' => array(
            'hgv' => array( 'd' => 'already in HGV', 'c' => '2012-01-01'),
            'tm' => array( 'd' => 'identities for Trismegistos (form wil be functional ca. february 2012) Theodoros 378673 =  387422; Leukadios 378678 = 387455 and perhaps = 374820.')
          )),
          array('bl' => '312', 'edition' => 'C.P.R. 5', 'text' => '24', 'tm' => '34844', 'hgv' => '34844', 'ddb' => 'cpr;5;24', 'biblio' => 0, 'position' => '3, 7', 'description' => 'Derselbe Daniel, Sohn des Valerius in P. Oxy. 68. 4682, Z. 4-5, 4683, Z. 1 (?) und 4685 V°, Z. 8; wohl derselbe Valerius auch in P. Wash.Univ. 2. 83, Z. 7, P. Oxy. 7. 1048, Z. 10, und 62. 4346, Z. 2, vgl. P. Oxy. 68. 4682, Anm. zu Z. 4-5.', 'task' => array(
            'hgv' => array( 'd' => 'add line-nr. in HGV-remark?'),
            'tm' => array( 'd' => 'inform Trismegistos')
          )),
          array('bl' => '312', 'edition' => 'C.P.R. 5', 'text' => '24', 'tm' => '34844', 'hgv' => '34844', 'ddb' => 'cpr;5;24', 'biblio' => 0, 'position' => '6, 10', 'description' => 'Derselbe Ammonianos viell. in P. Oxy. 68. 4685 R°, Z. 8, vgl. dort Anm. zur Z.', 'task' => array(
            'hgv' => array( 'd' => 'add line-nr. in HGV-remark?'),
            'tm' => array( 'd' => 'inform Trismegistos')
          )),
          array('bl' => '377', 'edition' => 'P. Flor. 3', 'text' => '325', 'tm' => '19366', 'hgv' => '19366', 'ddb' => 'p.flor;3;325', 'biblio' => 0, 'position' => '8', 'description' => 'πρίνκιπος → πολιτευομένου, K.A. Worp in: P. Oxy. 68. 4687, Anm. zu Z. 8-9 (am Original geprüft von R. Pintaudi).', 'task' => array(
            'ddb' => array( 'd' => 'add via SoSOL: <:πολιτευομένου= K.A. Worp in: P. Oxy. 68. 4687, Anm. zu Z. 8-9 (BL 13)|ed| πρίνκιπος:>')
          )),
          array('bl' => '496', 'edition' => 'P. Hib. 2', 'text' => '198', 'tm' => '5183', 'hgv' => '5183', 'ddb' => 'p.hib;2;198', 'biblio' => 0, 'position' => '231', 'description' => 'οἱ τὰ βασιλ̣[ικὰ (erg. viell. πραγματευόμενοι, ed.pr., Anm. zur Z.) ]σοντ[ → οἱ τὰ βασιλ[ικὰ πράσ]σοντ[ες], J.-M. Bertrand in: La circulation de l’information, S. 99-100.', 'task' => array(
            'ddb' => array( 'd' => 'Add via SoSOL <:<:τὰ βασιλικὰ πράσσοντες= J.-M. Bertrand in: La circulation de l’information, S. 99-100 (BL 13)|alt|τὰ βασιλ̣[ικὰ πραγματευόμενοι](?)=P.Hib. 2, Anm.:>|ed| τὰ βασιλ̣[ικὰ ca.? ]σοντ[ ca.? ]:>')
          )),
          array('bl' => '524', 'edition' => 'P. Köln 5', 'text' => '234', 'tm' => '21233', 'hgv' => '21233', 'ddb' => 'p.koeln;5;234', 'biblio' => 0, 'position' => '3', 'description' => 'Die Erg. ῥιπαρίῳ wird bestätigt; derselbe Flavius Ioseph in P. Oxy. 68. 4684, Z. 3 und viell. in 4685 V°, Z. 3, vgl. P. Oxy. 68. 4684, Anm. zu Z. 3 und 4685, Einl.', 'task' => array(
            'tm' => array( 'd' => 'Identities for TM People'),
            'ddb' => array( 'd' => 'add SoSOL: <: ῥιπαρίῳ=bestätigt in P. Oxy. 68. 4684, Anm. zu Z. 3 und 4685, Einl. (BL 13)|ed|ῥιπαρίῳ:>')
          )),
          array('bl' => '552', 'edition' => 'P. Laur. 3', 'text' => '74', 'tm' => '31513', 'hgv' => '31513', 'ddb' => 'p.laur;3;74', 'biblio' => 73664, 'position' => '29', 'description' => ']ν̣ → Ὡρείω]ν̣, J.-Y. Strasser, B.C.H. 128-129 (2004-2005), S. 427, Anm. 36.', 'task' => array(
            'ddb' => array( 'd' => 'Add SoSOL: <:Ὡρείω]ν̣= J.-Y. Strasser, B.C.H. 128-129 (2004-2005), S. 427, Anm. 36 (BL 13)|ed|]ν̣:>')
          )),
          array('bl' => '553', 'edition' => 'P. Laur. 4', 'text' => '192', 'tm' => '21277', 'hgv' => '21277', 'ddb' => 'p.laur;4;192', 'biblio' => 31315, 'position' => '7', 'description' => 'ἡμεριῶν: wohl nicht von ἡμερία, sondern vom Diminutiv ἡμέριον, N. Gonis, Bibl.Orient. 62 (2005), S. 51.', 'task' => array(
            'ddb' => array( 'd' => 'Add SoSOL: the whole for commentary line')
          )),
          array('bl' => '584', 'edition' => 'P. Lond. 5', 'text' => '1793', 'tm' => '19765', 'hgv' => '19765', 'ddb' => 'p.lond;5;1793', 'biblio' => 0, 'position' => null, 'description' => 'Die Datierung 2.12.471 n.Chr. (vgl. B.L. 8, S. 193) ist vorzuziehen, P. Oxy. 68. 4695, Anm. zu Z. 2-3.', 'task' => array(
            'hgv' => array( 'd' => 'already in HGV, but ‘vorzuziehen’ not made clear!!')
          )),
          array('bl' => '603', 'edition' => 'P. L.Bat. 25', 'text' => '70', 'tm' => '18487', 'hgv' => '18487', 'ddb' => 'p.leid.inst;;70', 'biblio' => 0, 'position' => '2', 'description' => 'ἐν Ὀξυρυγχ(ιτῶν) → ἐν Ὀξυρύγχ(ων), P. Oxy. 68. 4701, Anm. zu Z. 2.', 'task' => array(
            'ddb' => array( 'd' => 'Add SoSOL: <:ἐν Ὀξυρύγχ(ων)=P. Oxy. 68. 4701, Anm. zu Z. 2 (BL 13)|ed| ἐν Ὀξυρυγχ(ιτῶν):>')
          )),
          array('bl' => '636', 'edition' => 'P. Med. 12', 'text' => '64', 'tm' => '21289', 'hgv' => '21289', 'ddb' => 'p.mil;2;64', 'biblio' => 0, 'position' => '1', 'description' => '[μετὰ τὴν ὑπατείαν (B.L. 7, S. 103): wenn geschrieben, dann fehlerhaft für ὑπατείας, P. Oxy. 68. 4688, Anm. zu Z. 2.', 'task' => array(
            'ddb' => array( 'd' => 'Add SoSOL: <: [ὑπατείας Φλ]= P. Oxy. 68. 4688, Anm. zu Z. 2 (BL 13)|corr|<:[μετὰ τὴν ὑπατείαν Φλ]=BL 7.103|ed|[ ca.? ὑπατείας Φλ]:>:> ugly with half word Fl, but clear')
          )),
          array('bl' => '636', 'edition' => 'P. Med. 12', 'text' => '64', 'tm' => '21289', 'hgv' => '21289', 'ddb' => 'p.mil;2;64', 'biblio' => 0, 'position' => '5', 'description' => 'Die Erg. [ἐναπόγραφος wird abgelehnt, P. Oxy. 68. 4697, Anm. zu Z. 6-7.', 'task' => array(
            'ddb' => array( 'd' => 'Add SoSOL: <:[ ca.11 ]= P. Oxy. 68. 4697, Anm. zu Z. 6-7 (BL 13)|ed|[ἐναπόγραφος]:> misleading with 2nd bracket, probably impossible without 2nd bracket')
          )),
          array('bl' => '636', 'edition' => 'P. Med. 12', 'text' => '64', 'tm' => '21289', 'hgv' => '21289', 'ddb' => 'p.mil;2;64', 'biblio' => 0, 'position' => '9', 'description' => 'δ[ε]κ̣άτης (B.L. 7, S. 103) → ἐνάτης (wie ed.pr.) (nach dem Photo); also zu datieren: 6.12.440 n.Chr., P. Oxy. 68. 4688, Anm. zu Z. 2.', 'task' => array(
            'hgv' => array('d' => 'Date HGV'),
            'ddb' => array('d' => 'Add SoSOL: <:ἐνάτης=P. Oxy. 68. 4688, Anm. zu Z. 2 (BL 13)<:δ[ε]κ̣άτης=BL 7.103|ed|ἐνάτης:>:>')
          )),
          array('bl' => '312', 'edition' => 'C.P.R. 5', 'text' => '13', 'tm' => '12866', 'hgv' => '12866', 'ddb' => 'chla;43;1248', 'biblio' => 0, 'position' => null, 'description' => '(+ P.Rainer Cent. 165) Nd. mit italienischer Übersetzung: S. Daris, Aevum 74 (200), S. 157-159 (Kol. I, Z. 9 nostris Tippfehler statt sostris).', 'task' => array()),
        );
        


        foreach($dataList as $data){
          $correction = new Correction();
          $correction->setText($data['text']);
          if($data['biblio'] != 0){
            $correction->setSource($data['biblio']);
          }
          $correction->setTm($data['tm']);
          $correction->setHgv($data['hgv']);
          $correction->setDdb($data['ddb']);
          $correction->setPosition($data['position']);
          $correction->setDescription($data['description']);
          $correction->setCompilation($this->getReference('compilation'));
          $correction->setEdition($this->getReference('edition_' . $data['edition']));

          foreach($data['task'] as $category => $taskData){
            $task = new Task();
            $task->setCategory($category);
            $task->setDescription($taskData['d']);
            if(isset($taskData['c']) && $taskData['c']){
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
        return 3;
    }
}
?>