<?php

namespace App\Controller\Admin;

use App\Entity\Formation;
use App\Form\FormationType;
use App\Repository\FormationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/formation', name: 'app_admin_formation')]
class AdminFormationController extends AbstractController
{
    #[Route('/', name: '_lister')]
    public function lister(FormationRepository $formationRepository): Response
    {
        $formations = $formationRepository->findAll();
        return $this->render('admin/admin_formation/index.html.twig', [
            'formations'=>$formations,
        ]);
    }

    #[Route ('/ajouter', name: '_ajouter')]
    #[Route ('/modifier/{id}', name: '_modifier')]
    public function editer(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger,FormationRepository $formationRepository, int $id = null): Response
    {
        if($id == null){
            $formation = new Formation();
        }else{
            $formation = $formationRepository->find($id);
        }
        $form = $this->createForm(FormationType::class, $formation);
        $form->handleRequest($request);

        //si le form est valide
        if($form->isSubmitted() && $form->isValid()) {

            $photoFile = $form->get('photo')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$photoFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $photoFile->move(
                        $this->getParameter('formations_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $formation->setPhoto($newFilename);
            }

            $entityManager->persist($formation); //sauvegarder le bien
            $entityManager->flush(); //l'instruction finale mettant à jour la base de donnéd
            if($id == null) {
                $this->addFlash('success', 'la formation' . ($id == null) . ' a été ajoutée');
            }else{
                $this->addFlash('success', 'la formation ' . ($id) . ' a été modifiée');
            }
            return $this->redirectToRoute('app_admin_formation_lister');
        }

        return $this->render('admin/admin_formation/editerFormation.html.twig', [
            'form' => $form,
            'formation' => $formation
        ]);
    }

    #[Route ('/supprimer/{id}', name:'_supprimer')]
    public function supprimer(Request $request, EntityManagerInterface $entityManager,FormationRepository $formationRepository, int $id):Response{
        $formation = $formationRepository->find($id);
        $entityManager->remove($formation);
        $entityManager->flush();
        $this->addFlash('danger', 'La formation ' .($id). ' a bien été supprimée');
        return $this->redirectToRoute('app_admin_formation_lister');
    }
}
