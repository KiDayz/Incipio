<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\Project;

use App\Entity\Project\Etude;
use App\Entity\Project\Mission;
use App\Entity\Project\RepartitionJEH;
use App\Form\Project\MissionType;
use App\Service\Project\EtudePermissionChecker;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MissionsController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="project_missions_modifier", path="/suivi/missions/modifier/{id}", methods={"GET","HEAD","POST"})
     *
     * @return RedirectResponse|Response
     */
    public function modifier(
        Request $request, 
        Mission $mission, 
        EtudePermissionChecker $permChecker)
    {
        $em = $this->getDoctrine()->getManager();

        $etude = $mission->getEtude();

        if ($permChecker->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        /* Form handling */
        $form = $this->createForm(
            MissionType::class, 
            $mission,
            ['etude' => $etude]);

        $deleteForm = $this->createDeleteForm($mission->getId());

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $m = $form->getData();
                foreach ($form->get('repartitionsJEH') as $repartitionForm) {
                    $r = $repartitionForm->getData();
                    /* @var RepartitionJEH $r */
                    $r->setMission($m);
                }
                /* @var Mission $m */
                $m->setEtude($etude);

                $em->persist($etude);
                $em->flush();
                $this->addFlash('success', 'Mission enregistrée');

                return $this->redirectToRoute('project_missions_modifier', ['id' => $mission->getId()]);
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('Project/Mission/missions.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="project_missions_ajouter", path="/suivi/missions/ajouter/{id}", methods={"GET","HEAD","POST"})
     *
     * @return RedirectResponse|Response
     */
    public function add(
        Request $request,
        Etude $etude,
        EtudePermissionChecker $permChecker
    ) {
        $em = $this->getDoctrine()->getManager();

        if ($permChecker->confidentielRefus($etude, $this->getUser())) {
            throw new AccessDeniedException('Cette étude est confidentielle');
        }

        $mission = new Mission();
        $etude->addMission($mission);

        /* Form handling */
        $form = $this->createForm(
            MissionType::class, 
            $mission,
            ['etude' => $etude]);

        $deleteForm = $this->createDeleteForm($mission->getId());

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $m = $form->getData();
                foreach ($form->get('repartitionsJEH') as $repartitionForm) {
                    $r = $repartitionForm->getData();
                    /* @var RepartitionJEH $r */
                    $r->setMission($m);
                }
                /* @var Mission $m */
                $m->setEtude($etude);

                $em->persist($etude);
                $em->flush();
                $this->addFlash('success', 'Mission enregistrée');

                return $this->redirectToRoute('project_missions_modifier', ['id' => $mission->getId()]);
            }
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->render('Project/Mission/missions.html.twig', [
            'form' => $form->createView(),
            'etude' => $etude,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_SUIVEUR')")
     * @Route(name="project_mission_supprimer", path="/suivi/mission/supprimer/{id}", methods={"GET","HEAD","POST"})
     *
     * @return RedirectResponse
     */
    public function delete(Request $request, Mission $mission, EtudePermissionChecker $permChecker)
    {
        $form = $this->createDeleteForm($mission->getId());
        $form->handleRequest($request);
        $etude = $mission->getEtude();

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if ($permChecker->confidentielRefus($etude, $this->getUser())) {
                throw new AccessDeniedException('Cette étude est confidentielle');
            }

            $em->remove($mission);
            $em->flush();
            $this->addFlash('success', 'PV supprimé');
        } else {
            $this->addFlash('danger', 'Le formulaire contient des erreurs.');
        }

        return $this->redirectToRoute('project_etude_voir', ['nom' => $etude->getNom(), '_fragment' => 'tab3']);
    }

    private function createDeleteForm($id_mission)
    {
        return $this->createFormBuilder(['id' => $id_mission])
            ->add('id', HiddenType::class)
            ->getForm();
    }
}
