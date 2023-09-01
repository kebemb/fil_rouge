<?php

namespace App\Controller;

use App\Repository\FormationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/formation', name: 'app_formation')]
class FormationController extends AbstractController
{
    #[Route('/', name: '_lister')]
    public function index(FormationRepository $formationRepository): Response
    {

        return $this->render('formation/index.html.twig', [
            'formations' => $formationRepository->findAll()
        ]);
    }

    #[Route('/{id}', name:'_voir', requirements: ['id' => '\d+'])]
    public function voir(formationRepository $formationRepository, $id):Response{
        return $this->render('formation/voir.html.twig', [
            'formation' => $formationRepository->find($id)]);

    }
}
