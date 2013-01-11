<?php

namespace mgate\PubliBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class TraitementController extends Controller {

    private $SFD = '~'; //Start Field Delimiter
    private $EFD = '~~'; 

    /*
     * private SAD
     * private EAD
     */

    //Repétition des phases
    private function repeterPhase(&$templateXML, $nombrePhase) {


        $regexRepeatSTART = '<w:bookmarkStart w:id="\d+" w:name="repeatSTART"/>\s*\S*<w:bookmarkEnd w:id="\d+"/>'; //Marqueur de début de repeat
        $regexRepeatEND = '<w:bookmarkStart w:id="\d+" w:name="repeatEND"/>\s*\S*<w:bookmarkEnd w:id="\d+"/>'; //Marqueur de fin de repeat
        $regexpRepeat = '#' . $regexRepeatSTART . '(.*?)' . $regexRepeatEND . '#s'; // *? see ungreedy behavior //Expression régulière filtrage répétition /!\ imbrication interdite !
        
        $SFD = $this->SFD;
        $EFD = $this->EFD;
        $callback = function ($matches) use ($nombrePhase, $SFD, $EFD) { //Fonction de callback prétraitement de la zone à répéter
                    $outputString = "";
                    

                    if(preg_match("#w:vMerge\s*/>#", $matches[1]))//Rowspan ?
                        $premiereLigne = preg_replace('#<w:vMerge\s*/>#', "<w:vMerge w:val=\"restart\"/>", $matches[1]);
                    else
                        $premiereLigne = $matches[1];
                    
                    $outputString .= preg_replace('#' . $SFD . 'Phase_Index' . $EFD . '#U', "1", $premiereLigne);
                            
                    for ($i = 2; $i <= $nombrePhase; $i++)
                        $outputString .= preg_replace('#' . $SFD . 'Phase_Index' . $EFD . '#U', "$i", $matches[1]);
                    return $outputString;
                };

        $templateXML =  preg_replace_callback($regexpRepeat, $callback, $templateXML);
        
        return $templateXML;
    }

    //Remplissage des %champs%
    private function remplirChamps(&$templateXML, $fieldValues) {
        $SFD = $this->SFD;
        $EFD = $this->EFD;

        foreach ($fieldValues as $field => $values) {//Remplacement des champs
            if ($values != NULL) {
                $templateXML = preg_replace('#' . $SFD . $field . $EFD . '#U', $values, $templateXML);
            }
        }


        return $templateXML;
    }

    //Accord en nombre
    /* ¤nombre|pluriel|singulier¤
     * ¤nombre|pluriel¤ (singulier = '')
     * ¤genre|feminin|masculin¤
     * ¤genre|fem¤ (masc = '')
     * >1 = Femme 
     * 0||1 = Homme
     * ¤%sexe%|rendue|rendu¤
     * ¤%sexe%|e¤
     */
    private function accorder(&$templateXML) {
        $regexp = array(//Expression régulière filtrage répétition /!\ imbrication interdite !
            '#¤(\d+)\|([^¤.]*)\|([^¤.]*)¤#', //si deux args ¤3|ont|a¤
            '#¤(\d+)\|([^¤.]*)¤#', //si un arg : ¤3|s¤
        ); // Ou en un seul regex...

        $callback = function ($matches) {//Fonction de callback
                    if (isset($matches[3]))
                        return ($matches[1] > 1) ? $matches[2] : $matches[3];
                    else
                        return ($matches[1] > 1) ? $matches[2] : '';
                };

        $templateXML =  preg_replace_callback($regexp, $callback, $templateXML);
        return $templateXML;
    }

    //Traitement du template
    private function traiterTemplate($templateFullPath, $nombrePhase, $champs) {
        $templateXML = file_get_contents($templateFullPath); //récup contenu XML
        
        $this->repeterPhase($templateXML, $nombrePhase); //Répétion phase
        $this->remplirChamps($templateXML, $champs); //remplissage des champs + phases
        $this->accorder($templateXML); //Accord en nombre /!\ accord en genre ?

        return $templateXML;
    }


    //Vérification du fichier
    //if match %   _   % then pasbien
    private function verifierTemplate($templateXML) {
        $SFD = $this->SFD;
        $EFD = $this->EFD;

        preg_match_all('#' . $SFD . '(.*?)' . $EFD . '#', $templateXML, $matches);
        
        return $matches[1];
    }

    private function getAllChamp($etude) {
        //$etude = new \mgate\SuiviBundle\Entity\Etude();//Juste pour avoir l'autocompletion :D

        $phases = $etude->getPhases();
        $nombrePhase = count($phases);
        $date = date("d/m/Y");


        $Total_HT_Lettres = "Total_HT_Lettres______DefautValue";
        $TVA = "TVA______DefautValue";
        $Montant_TVA = "Montant_TVA______DefautValue";
        $Montant_TVA_Lettres = "Montant_TVA_Lettres______DefautValue";
        $Total_TTC = "Total_TTC______DefautValue";
        $Total_TTC_Lettres = "Total_TTC_Lettres______DefautValue";
        $Entite_Sociale = "Entite_Sociale______DefautValue";
        $Adresse_Client = "Adresse_Client______DefautValue";
        $Nom_Signataire = "Nom_Signataire______DefautValue";
        $Fonction_Signataire = "Fonction_Signataire______DefautValue";
        $Description_Prestation = "Description_Prestation______DefautValue";
        $Delais_Semaines = "Delais_Semaines______DefautValue";
        $Total_HT = "Total_HT______DefautValue";
        $Nbr_JEH_Total = "Nbr_JEH_Total______DefautValue";
        $Nbr_JEH_Total_Lettres = "Nbr_JEH_Total_Lettres______DefautValue";
        $Montant_Total_HT = "Montant_Total_HT______DefautValue";
        $Montant_Total_HT_Lettres = "Montant_Total_HT_Lettres______DefautValue";
        $Frais_HT = "Frais_HT______DefautValue";
        $Frais_HT_Lettres = "Frais_HT_Lettres______DefautValue";
        $Acompte_HT = "Acompte_HT______DefautValue";
        $Acompte_HT_Lettres = "Acompte_HT_Lettres______DefautValue";
        $Acompte_TTC = "Acompte_TTC______DefautValue";
        $Acompte_TTC_Lettres = "Acompte_TTC_Lettres______DefautValue";
        $Solde_PVR_HT = "Solde_PVR_HT______DefautValue";
        $Solde_PVR_HT_Lettres = "Solde_PVR_HT_Lettres______DefautValue";
        $Solde_PVR_TTC = "Solde_PVR_TTC______DefautValue";
        $Solde_PVR_TTC_Lettres = "Solde_PVR_TTC_Lettres______DefautValue";
        $Phase_1_Nbre_JEH = "Phase_1_Nbre_JEH______DefautValue";
        $Phase_1_Prix_JEH_HT = "Phase_1_Prix_JEH_HT______DefautValue";
        $Phase_1_Prix_Phase_HT = "Phase_1_Prix_Phase_HT______DefautValue";
        $Total_TVA = "Total_TVA______DefautValue";
        $Acompte_TVA = "Acompte_TVA______DefautValue";
        $Acompte_Pourcentage = "Acompte_Pourcentage______DefautValue";
        $Date_Emission = "Date_Emission______DefautValue";
        $Date_Limite = "Date_Limite______DefautValue";
        $Reference_PVR = "Reference_PVR______DefautValue";
        $Date_Debut = "Date_Debut______DefautValue";
        $Date_Fin = "Date_Fin______DefautValue";
        $Reference_Etude = "Reference_Etude______DefautValue";
        $Reference_CC = "Reference_CC______DefautValue";
        $Reference_AP = "Reference_AP______DefautValue";
        $Reference_OM = "Reference_OM______DefautValue";
        $Reference_CE = "Reference_CE______DefautValue";
        $Nom_Etudiant = "Nom_Etudiant______DefautValue";
        $Prenom_Etudiant = "Prenom_Etudiant______DefautValue";
        $Sexe = "Sexe______DefautValue";
        $Adresse_Etudiant = "Adresse_Etudiant______DefautValue";
        $Montant_JEH_Verse = "Montant_JEH_Verse______DefautValue";
        $Montant_JEH_Verse_Lettres = "Montant_JEH_Verse_Lettres______DefautValue";
        $Nbre_JEH = "Nbre_JEH______DefautValue";
        $Nbre_JEH_Lettres = "Nbre_JEH_Lettres______DefautValue";
        $Remuneration_Brut = "Remuneration_Brut______DefautValue";
        $Remuneration_Brut_Lettres = "Remuneration_Brut_Lettres______DefautValue";
        $Date_Fin_Etude = "Date_Fin_Etude______DefautValue";
        $Nom_Client = "Nom_Client______DefautValue";




        $Total_HT = $this->get('mgate.etude_manager')->getTotalJEHHT($etude);
        $Montant_Total_HT = $this->get('mgate.etude_manager')->getTotalHT($etude);
        $Total_TTC = $this->get('mgate.etude_manager')->getTotalTTC($etude);

        $Total_HT_Lettres = $this->get('mgate.conversionlettre')->ConvNumberLetter($Total_HT);
        $Montant_Total_HT_Lettres = $this->get('mgate.conversionlettre')->ConvNumberLetter($Montant_Total_HT);
        $Total_TTC_Lettres = $this->get('mgate.conversionlettre')->ConvNumberLetter($Total_TTC);

        $champs = Array(
            "date" => $date,
            "TVA" => $TVA,
            "Description_Prestation" => $etude->getDescriptionPrestation(),
            "Delais_Semaines" => $Delais_Semaines,
            "Nbr_JEH_Total_Lettres" => $Nbr_JEH_Total_Lettres,
            "Montant_TVA" => $Montant_TVA,
            "Montant_TVA_Lettres" => $Montant_TVA_Lettres,
            "Nbr_JEH_Total" => $this->get('mgate.etude_manager')->getNbrJEH($etude),
            "Total_HT" => $Total_HT,
            "Montant_Total_HT" => $Montant_Total_HT,
            "Total_TTC" => $Total_TTC,
            "Total_HT_Lettres" => $Total_HT_Lettres,
            "Montant_Total_HT_Lettres" => $Montant_Total_HT_Lettres,
            "Total_TTC_Lettres" => $Total_TTC_Lettres,
            "Frais_HT" => $etude->getFraisDossier(),
            "Frais_HT_Lettres" => $Frais_HT_Lettres,
            "Acompte_HT" => $Acompte_HT,
            "Acompte_HT_Lettres" => $Acompte_HT_Lettres,
            "Acompte_TTC" => $Acompte_TTC,
            "Acompte_TTC_Lettres" => $Acompte_TTC_Lettres,
            "Solde_PVR_HT" => $Solde_PVR_HT,
            "Solde_PVR_HT_Lettres" => $Solde_PVR_HT_Lettres,
            "Solde_PVR_TTC" => $Solde_PVR_TTC,
            "Solde_PVR_TTC_Lettres" => $Solde_PVR_TTC_Lettres,
            "Total_TVA" => $Total_TVA,
            "Acompte_TVA" => $Acompte_TVA,
            "Acompte_Pourcentage" => $Acompte_Pourcentage,
            "Date_Emission" => $Date_Emission,
            "Date_Limite" => $Date_Limite,
            "Reference_PVR" => $Reference_PVR,
            "Date_Debut" => $Date_Debut,
            "Date_Fin" => $Date_Fin,
            "Reference_Etude" => $Reference_Etude,
            "Reference_CC" => $Reference_CC,
            "Reference_AP" => $Reference_AP,
            "Reference_OM" => $Reference_OM,
            "Reference_CE" => $Reference_CE,
            "Nom_Etudiant" => $Nom_Etudiant,
            "Prenom_Etudiant" => $Prenom_Etudiant,
            "Sexe" => $Sexe,
            "Adresse_Etudiant" => $Adresse_Etudiant,
            "Montant_JEH_Verse" => $Montant_JEH_Verse,
            "Montant_JEH_Verse_Lettres" => $Montant_JEH_Verse_Lettres,
            "Nbre_JEH" => $Nbre_JEH,
            "Nbre_JEH_Lettres" => $Nbre_JEH_Lettres,
            "Remuneration_Brut" => $Remuneration_Brut,
            "Remuneration_Brut_Lettres" => $Remuneration_Brut_Lettres,
            "Date_Fin_Etude" => $Date_Fin_Etude,
            "Nom_Client" => $Nom_Client,
            "Description_Prestation" => $etude->getDescriptionPrestation(),
            "Nbr_JEH_Total" => 6,
            "Nbr_Developpeurs" => 2,
            "Nbr_Phases" => $nombrePhase,
        );

        $etude = new \mgate\SuiviBundle\Entity\Etude();
        //block dépendant de prospect
        if ($etude->getProspect() != NULL) {
            $this->array_push_assoc($champs, "Entite_Sociale", $etude->getProspect()->getEntite());
            $this->array_push_assoc($champs, "Adresse_Client", $etude->getProspect()->getAdresse());
        }

        //block dépendant de AP
        if ($etude->getAp() != NULL) {
            //Block dependant de AP->Signataire 2
            if ($etude->getSignateire2() != NULL) {
                $this->array_push_assoc($champs, "Nom_Signataire", $etude->getAp()->getSignataire2()->getPrenomNom());
                $this->array_push_assoc($champs, "Fonction_Signataire", $etude->getAp()->getSignataire2()->getPoste());
            }
        }



        //$phase = new \mgate\SuiviBundle\Entity\Phase();

        foreach ($phases as $phase) {
            $i = $phase->getPosition() + 1;

            $this->array_push_assoc($champs, 'Phase_' . $i . '_Titre', $phase->getTitre());
            $this->array_push_assoc($champs, 'Phase_' . $i . '_Nbre_JEH', $phase->getNbrJEH());
            $this->array_push_assoc($champs, 'Phase_' . $i . '_Prix_JEH_HT', $phase->getPrixJEH());
            $this->array_push_assoc($champs, 'Phase_' . $i . '_Prix_Phase_HT', $phase->getNbrJEH() * $phase->getPrixJEH());
            $this->array_push_assoc($champs, 'Phase_' . $i . '_Prix_Phase', $phase->getNbrJEH() * $phase->getPrixJEH());
            $this->array_push_assoc($champs, 'Phase_' . $i . '_Date_Debut', $phase->getDateDebut()->format('d/m/Y'));
            $this->array_push_assoc($champs, 'Phase_' . $i . '_Delai', $phase->getDelai());
            $this->array_push_assoc($champs, 'Phase_' . $i . '_Objectif', $phase->getObjectif());
            $this->array_push_assoc($champs, 'Phase_' . $i . '_Methodo', $phase->getMethodo());
            $this->array_push_assoc($champs, 'Phase_' . $i . '_Rendu', $phase->getValidation());
        }

        return $champs;
    }

    private function array_push_assoc(&$array, $key, $value) {
        $array[$key] = $value;
        return $array;
    }

    private function getEtudeFromID($id_etude) {
        $em = $this->getDoctrine()->getEntityManager();

        //Récupère l'étude avec son id
        if (!$etude = $em->getRepository('mgate\SuiviBundle\Entity\Etude')->find($id_etude))
            throw $this->createNotFoundException('Etude[id=' . $id_etude . '] inexistant');

        return $etude;
    }

    private function getDoctypeAbsolutePathFromName($doc) {
        $em = $this->getDoctrine()->getEntityManager();

        $request = $this->get('request');

        if (!$documenttype = $em->getRepository('mgate\PubliBundle\Entity\DocumentType')->findOneBy(array('name' => $doc))) {
            echo 'DocumentType[name=' . $doc . '] non trouvé: on utilise un asset<br /><br />';
            $chemin = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/bundles/mgatepubli/document-type/' . $doc . '.xml';
            //throw $this->createNotFoundException('DocumentType[name=' . $doc . '] inexistant');
        } else {
            echo 'DocumentType uploadé trouvé<br /><br />';
            $chemin = $documenttype->getWebPath();
        }
        return $chemin;
    }

    //publication du doc
    public function publiposterAction($id_etude, $doc) {



        $etude = $this->getEtudeFromID($id_etude);
        $chemin = $this->getDoctypeAbsolutePathFromName($doc);
        $nombrePhase = count($etude->getPhases());
        $champs = $this->getAllChamp($etude);

        
        //debug
        if (false)
            $chemin = 'C:\wamp\www\My-M-GaTE\src\mgate\PubliBundle\Resources\public\document-type/' . $doc . '.xml';
        if (false)
            $chemin = 'C:\Users\flo\Desktop\DocType Fonctionnel/FA.xml';
        
        $templateXMLtraite = $this->traiterTemplate($chemin, $nombrePhase, $champs); //Ne sais ou mettre mes ressources


        $champsBrut = $this->verifierTemplate($templateXMLtraite);

        $repertoire = 'tmp';
        $idDocx = (int) strtotime("now") + rand();
        if (!file_exists($repertoire))
                mkdir ($repertoire/*,0700*/);
        $handle = fopen($repertoire.'/' . $idDocx, "w+");
        fwrite($handle, $templateXMLtraite);
        fclose($handle);

        $_SESSION['idDocx'] = $idDocx;
        $_SESSION['refDocx'] = $this->get('mgate.etude_manager')->getRefDoc($etude, $doc, 1);

        return $this->render('mgatePubliBundle:Traitement:index.html.twig', array('champsNonRemplis' => $champsBrut));
    }

    public function telechargerAction($docType = 'AP') {

        if (isset($_SESSION['idDocx'])) {
            $idDocx = $_SESSION['idDocx'];
            $refDocx = (isset($_SESSION['refDocx']) ? $_SESSION['refDocx'] : $docType);

            $doc = 'tmp/' . $idDocx;

            header('Content-Type: application/msword');
            header('Content-Length: ' . filesize($doc));
            header('Content-disposition: attachment; filename=' . $doc);
            header('Pragma: no-cache');
            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');
            readfile($doc);
            exit();

        
        }
        else
        {
            echo 'fail';
        }
        return $this->render('mgatePubliBundle:Default:index.html.twig', array('name' => 'ololilioloiol'));
    }

}
