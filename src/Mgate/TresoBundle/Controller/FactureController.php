<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mgate\TresoBundle\Controller;

use Mgate\SuiviBundle\Entity\Phase;
use Mgate\TresoBundle\Entity\Facture as Facture;
use Mgate\TresoBundle\Entity\FactureDetail as FactureDetail;
use Mgate\TresoBundle\Form\Type\FactureType as FactureType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FactureController extends Controller
{
    /**
     * @Security("has_role('ROLE_TRESO')")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $factures = $em->getRepository('MgateTresoBundle:Facture')->findAll();

        return $this->render('MgateTresoBundle:Facture:index.html.twig', ['factures' => $factures]);
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     *
     * @param Facture $facture
     *
     * @return Response
     */
    public function voirAction(Facture $facture)
    {
        $deleteForm = $this->createDeleteForm($facture);

        return $this->render('MgateTresoBundle:Facture:voir.html.twig', ['facture' => $facture,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_TRESO')")
     *
     * @param Request $request
     * @param $id
     * @param $etude_id
     *
     * @return RedirectResponse|Response
     */
    public function modifierAction(Request $request, $id, $etude_id)
    {
        $em = $this->getDoctrine()->getManager();
        $keyValueStore = $this->get('app.json_key_value_store');
        if (!$keyValueStore->exists('tva')) {
            throw new \RuntimeException('Le paramètres tva n\'est pas disponible.');
        }
        $tauxTVA = 100 * $keyValueStore->get('tva'); // former value: 20, tva is stored as 0.2 in key-value store
        $compteEtude = 705000;
        $compteFrais = 708500;
        $compteAcompte = 419100;
        if ($keyValueStore->exists('namingConvention')) {
            $namingConvention = $keyValueStore->get('namingConvention');
        } else {
            $namingConvention = 'id';
        }

        if (!$facture = $em->getRepository('MgateTresoBundle:Facture')->find($id)) {
            $facture = new Facture();
            $now = new \DateTime('now');
            $facture->setDateEmission($now);

            if ($etude = $em->getRepository('MgateSuiviBundle:Etude')->find($etude_id)) {
                $formater = $this->container->get('Mgate.conversionlettre');

                $facture->setEtude($etude);
                $facture->setBeneficiaire($etude->getProspect());

                if (!count($etude->getFactures()) && $etude->getAcompte()) {
                    $facture->setType(Facture::TYPE_VENTE_ACCOMPTE);
                    $facture->setObjet('Facture d\'acompte sur l\'étude ' . $etude->getReference($namingConvention) . ', correspondant au règlement de ' . $formater->moneyFormat(($etude->getPourcentageAcompte() * 100)) . ' % de l’étude.');
                    $detail = new FactureDetail();
                    $detail->setCompte($em->getRepository('MgateTresoBundle:Compte')->findOneBy(['numero' => $compteAcompte]));
                    $detail->setFacture($facture);
                    $facture->addDetail($detail);
                    $detail->setDescription('Acompte de ' . $formater->moneyFormat(($etude->getPourcentageAcompte() * 100)) . ' % sur l\'étude ' . $etude->getReference());
                    $detail->setMontantHT($etude->getPourcentageAcompte() * $etude->getMontantHT());
                    $detail->setTauxTVA($tauxTVA);
                } else {
                    $facture->setType(Facture::TYPE_VENTE_SOLDE);
                    if ($etude->getAcompte() && $etude->getFa()) {
                        $montantADeduire = new FactureDetail();
                        $montantADeduire->setDescription('Facture d\'acompte sur l\'étude ' . $etude->getReference($namingConvention) .
                            ', correspondant au règlement de ' . $formater->moneyFormat(($etude->getPourcentageAcompte() * 100)) .
                            ' % de l’étude.')->setFacture($facture);
                        $facture->setMontantADeduire($montantADeduire);
                    }

                    $totalTTC = 0;
                    /** @var Phase $phase */
                    foreach ($etude->getPhases() as $phase) {
                        $detail = new FactureDetail();
                        $detail->setCompte($em->getRepository('MgateTresoBundle:Compte')->findOneBy(['numero' => $compteEtude]));
                        $detail->setFacture($facture);
                        $facture->addDetail($detail);
                        $detail->setDescription('Phase ' . ($phase->getPosition() + 1) . ' : ' . $phase->getTitre() . ' : ' .
                            $phase->getNbrJEH() . ' JEH * ' . $formater->moneyFormat($phase->getPrixJEH()) . ' €');
                        $detail->setMontantHT($phase->getPrixJEH() * $phase->getNbrJEH());
                        $detail->setTauxTVA($tauxTVA);

                        $totalTTC += $phase->getPrixJEH() * $phase->getNbrJEH();
                    }
                    $detail = new FactureDetail();
                    $detail->setCompte($em->getRepository('MgateTresoBundle:Compte')->findOneBy(['numero' => $compteFrais]))
                        ->setFacture($facture)
                        ->setDescription('Frais de dossier')
                        ->setMontantHT($etude->getFraisDossier());
                    $facture->addDetail($detail);
                    $detail->setTauxTVA($tauxTVA);

                    $totalTTC += $etude->getFraisDossier();
                    $totalTTC *= (1 + $tauxTVA / 100);

                    $facture->setObjet('Facture de Solde sur l\'étude ' . $etude->getReference($namingConvention) . '.');
                }
            }
        }

        $form = $this->createForm(FactureType::class, $facture);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);

            if ($form->isValid()) {
                foreach ($facture->getDetails() as $factured) {
                    $factured->setFacture($facture);
                }

                if ($facture->getType() <= Facture::TYPE_VENTE_ACCOMPTE || $facture->getMontantADeduire() === null || $facture->getMontantADeduire()->getMontantHT() == 0) {
                    $facture->setMontantADeduire(null);
                }

                $em->persist($facture);
                $em->flush();

                return $this->redirect($this->generateUrl('MgateTreso_Facture_voir', ['id' => $facture->getId()]));
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('MgateTresoBundle:Facture:modifier.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     *
     * @param Facture $facture
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function supprimerAction(Facture $facture, Request $request)
    {
        $form = $this->createDeleteForm($facture);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->remove($facture);
            $em->flush();

            $this->addFlash('success', 'Facture supprimée');
        } else {
            $this->addFlash('danger', 'Erreur dans le formulaire');
        }

        return $this->redirectToRoute('MgateTreso_Facture_index');
    }

    /**
     * @param Facture $facture
     *
     * @return FormInterface
     */
    private function createDeleteForm(Facture $facture)
    {
        return $this->createFormBuilder(['id' => $facture->getId()])
            ->add('id', HiddenType::class)
            ->setmethod('DELETE')
            ->setAction($this->generateUrl('MgateTreso_Facture_supprimer', ['id' => $facture->getId()]))
            ->getForm();
    }
}
