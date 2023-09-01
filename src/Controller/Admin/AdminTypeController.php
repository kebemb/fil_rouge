<?php

namespace App\Controller\Admin;

use App\Entity\Type;
use App\Form\TypeType;
use App\Repository\TypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/type', name: 'app_admin_type')]
class AdminTypeController extends AbstractController
{

    #[Route('/', name: '_lister')]
    public function lister(TypeRepository $typeRepository): Response
    {
        $types = $typeRepository->findAll();
        return $this->render('admin/admin_type/index.html.twig', [
            'types'=>$types,
        ]);
    }

    #[Route ('/ajouter', name: '_ajouter')]
    #[Route ('/modifier/{id}', name: '_modifier')]
    public function editer(Request $request, EntityManagerInterface $entityManager,TypeRepository $typeRepository, int $id = null): Response
    {
        if($id == null){
            $type = new Type();
        }else{
            $type = $typeRepository->find($id);
        }
        $form = $this->createForm(TypeType::class, $type);
        $form->handleRequest($request);

        //si le form est valide
        if($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($type); //sauvegarder le bien
            $entityManager->flush(); //l'instruction finale mettant à jour la base de donnéd
            if($id == null) {
                $this->addFlash('success', 'Le type de formation' . ($id == null) . ' a été ajouté');
            }else{
                $this->addFlash('success', 'Le type de formation ' . ($id) . ' a été modifié');
            }
            return $this->redirectToRoute('app_admin_type_lister');
        }

        return $this->render('admin/admin_type/editerType.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route ('/supprimer/{id}', name:'_supprimer')]
    public function supprimer(Request $request, EntityManagerInterface $entityManager,TypeRepository $typeRepository, int $id):Response{
        $type = $typeRepository->find($id);
        $entityManager->remove($type);
        $entityManager->flush();
        $this->addFlash('danger', 'Le type de formation ' .($id). ' a bien été supprimé');
        return $this->redirectToRoute('app_admin_type_lister');
    }
}
