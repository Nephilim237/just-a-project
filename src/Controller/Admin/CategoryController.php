<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CategoryFormType;
use App\Form\EditProfileFormType;
use App\Form\PostFormType;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryController extends AbstractController
{
    /**
     * @Route("/admin/category", name="category_home")
     * @param CategoryRepository $categoryRepository
     *
     * @return Response
     */
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('admin/category/index.html.twig', [
            'categories'    => $categoryRepository->findAll(),
            'pageTitle'     => 'Gestion des catégories',
            'icon'          => 'article-line'
        ]);
    }

    /**
     * @Route("admin/category/add", name="category_add")
     * @param Request $request
     *
     * @return Response
     */
    public function categoryAdd(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryFormType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($category);
            $manager->flush();

            $this->addFlash('success', "La catégorie {$form->get('title')->getData()} a été ajoutée avec succès");

            return $this->redirectToRoute('category_home');
        }

        return $this->render('admin/category/add.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => "Nouvelle Catégorie",
            'icon' => "article-line"
        ]);

    }

    /**
     * @Route("admin/category/edit/{id}", name="category_edit")
     * @param Category $category
     * @param Request  $request
     *
     * @return Response
     */
    public function categoryEdit(Category $category, Request $request): Response
    {
        $form = $this->createForm(CategoryFormType::class, $category);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($category);
            $manager->flush();

            $this->addFlash('success', "La catégorie {$form->get('title')->getData()} a été ajoutée avec succès");

            return $this->redirectToRoute('category_home');
        }

        return $this->render('admin/category/edit.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => "Modifier Catégorie",
            'icon' => "article-line"
        ]);

    }
}
