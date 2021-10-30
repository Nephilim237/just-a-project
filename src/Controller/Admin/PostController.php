<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Image;
use App\Entity\Post;
use App\Entity\User;
use App\Form\CategoryFormType;
use App\Form\EditProfileFormType;
use App\Form\PostFormType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class PostController extends AbstractController
{
    /**
     * @var false|string
     */
    private $day;

    public function __construct()
    {
        $this->day = date('Ymd');
    }

    /**
     * @Route("/admin/post", name="post_home")
     * @param PostRepository $postRepository
     *
     * @return Response
     */
    public function index(PostRepository $postRepository): Response
    {
        return $this->render('admin/post/index.html.twig', [
            'posts' => $postRepository->findAll(),
            'pageTitle' => 'Gestion des posts',
            'icon' => 'article-line'
        ]);
    }

    /**
     * @Route("admin/post/add", name="post_add")
     * @param Request $request
     *
     * @return Response
     */
    public function postAdd(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostFormType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUser($this->getUser())
                ->setActive(false);
            $this->getImages($form, $post);
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($post);
            $manager->flush();

            $this->addFlash('success', "L'article {$form->get('title')->getData()} a été ajoutée avec succès");

            return $this->redirectToRoute('post_home');
        }

        return $this->render('admin/post/add.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => 'Publier un article',
            'icon' => 'add-circle-line'
        ]);
    }

    /**
     * @Route("admin/post/edit/{slug}", name="post_edit")
     * @param Post    $post
     * @param Request $request
     *
     * @return Response
     */
    public function postEdit(Post $post, Request $request): Response
    {
        $form = $this->createForm(PostFormType::class, $post);
        $day = date('Ymd');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUser($this->getUser())
                ->setActive(false);
            $this->getImages($form, $post);
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($post);
            $manager->flush();

            $this->addFlash('success', "L'article {$form->get('title')->getData()} a été modifié avec succès");

            return $this->redirectToRoute('post_home');
        }

        return $this->render('admin/post/edit.html.twig', [
            'form' => $form->createView(),
            'pageTitle' => 'Modifier un article',
            'icon' => 'refresh-line',
            'post' => $post,
            'day'  => $day,
        ]);
    }


    /**
     * @param Post $post
     * @Route("admin/post/activate/{id}", name="post_activate")
     *
     * @return Response
     */
    public function postActivate(Post $post)
    {
        $post->setActive($post->getActive() ? false : true);
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($post);
        $manager->flush();

        return new Response('true');
    }


    /**
     * @param Post $post
     * @Route("admin/post/delete/{id}", name="post_delete")
     *
     * @return Response
     */
    public function postDelete(Post $post)
    {
        $manager = $this->getDoctrine()->getManager();
        $manager->remove($post);
        $manager->flush();
        $this->addFlash('success', "Article Supprimé avec succès.");
        return $this->redirectToRoute('post_home');
    }

    /**
     * @Route("/drop/image/{id}", name="post_image_drop", methods={"DELETE"})
     * @param Image   $image
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function dropImage(Image $image, Request $request) {
        $data = json_decode($request->getContent(), true);
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $data['_token'])){
            unlink($this->getParameter('posts_images_directory'). '/'. $image->getName());
            $em = $this->getDoctrine()->getManager();
            $em->remove($image);
            $em->flush();

            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token invalide'], 400);
        }
    }



    private function getImages($form, Post $post) {
        $images = $form->get('images')->getData();
        foreach ($images as $image) {
            $file = date('YmdHis') . $image->getClientOriginalName();
            $image->move(
                $this->getParameter('posts_images_directory') . "/{$this->day}/",
                $file
            );

            $img = new Image();
            $img->setName("{$this->day}/" . $file);
            $post->addImage($img);
        }
    }
}
