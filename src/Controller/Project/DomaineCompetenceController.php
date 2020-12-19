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

use App\Entity\Project\DomaineCompetence;
use App\Form\Project\DomaineCompetenceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DomaineCompetenceController extends AbstractController
{
    /**
     * @Security("has_role('ROLE_CA')")
     * @Route(name="project_domaine_index", path="/suivi/DomainesDeCompetence", methods={"GET","HEAD","POST"})
     *
     * @return RedirectResponse|Response
     */
    public function index(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository(DomaineCompetence::class)->findAll();

        $domaine = new DomaineCompetence();

        $form = $this->createForm(DomaineCompetenceType::class, $domaine);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->persist($domaine);
                $em->flush();

                return $this->redirectToRoute('project_domaine_index');
            }
        }

        return $this->render('Project/DomaineCompetence/index.html.twig', [
            'domaines' => $entities,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Security("has_role('ROLE_ADMIN')")
     * @Route(name="project_domaine_delete", path="/suivi/DomainesDeCompetence/Supprimer/{id}", methods={"GET","HEAD","POST"})
     *
     * @return RedirectResponse
     */
    public function delete(DomaineCompetence $domaine)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($domaine);
        $em->flush();

        return $this->redirectToRoute('project_domaine_index');
    }
}
