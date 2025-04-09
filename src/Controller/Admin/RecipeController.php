<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\CategoryRepository;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/admin/recettes', name: 'admin.recipe.')]
final class RecipeController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(RecipeRepository $repository, CategoryRepository $categoryRepository, EntityManagerInterface $em): Response
    {
        

        $recipes = $repository->findWithDurationLowerThan(30);

        // $recipe = new Recipe();

        // Supprimer un élément de la DB : $em->remove($recipes[0]);
        // Ajouter un élément à la DB : $recipe->setTitle('Barbe à papa')
        //     ->setSlug('barbe-a-papa')
        //     ->setContent('Recette de barbe à papa')
        //     ->setDuration(2)
        //     ->setCreatedAt(new \DateTimeImmutable())
        //     ->setUpdatedAt(new \DateTimeImmutable());
        // $em->persist($recipe);
        // Mettre à jour un élément : $recipes[0]->setTitle('Pâtes bolognaise');
        // $em->flush();
        return $this->render('admin/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    
    #[Route('/create', name: 'create')]

    public function create(Request $request, EntityManagerInterface $em)
    {
        $recipe = new Recipe();
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($recipe);
            $em->flush();
            $this->addFlash('success', 'Recette créée avec succès !');
            return $this->redirectToRoute('admin.recipe.index');
        }
        return $this->render('admin/recipe/create.html.twig', [
            'form' => $form
        ]);
     
    }


    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => Requirement::DIGITS])]
    public function edit(Recipe $recipe, Request $request, EntityManagerInterface $em): Response{
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('thumbnailFile')->getData();
            $filename = $recipe->getId() . '.' . $file->getClientOriginalExtension();
            $file->move($this->getParameter('kernel.project_dir' ) . '/public/recettes/images', $filename);
            $recipe->setThumbnail($filename);
            $em->flush();
            $this->addFlash('success', 'Recette mise à jour avec succès !');
            return $this->redirectToRoute('admin.recipe.index');
        }
        return $this->render('admin/recipe/edit.html.twig', [
            'recipe' => $recipe,
            'form' => $form
        ]);
    }


    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function remove(Recipe $recipe, EntityManagerInterface $em)
    {
        $em->remove($recipe);
        $em->flush();
        $this->addFlash('success', 'Recette supprimée avec succès !');
        return $this->redirectToRoute('admin.recipe.index');
    }

}
