<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/user")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository, EntityManagerInterface $manager): Response
    {
        // $users = $manager->getRepository(User::class)->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="user_new", methods={"GET", "POST"})
     */
    public function new(): Response
    {
        return $this->render('user/new.html.twig');
    }

    /**
     * @Route("/store", name="user_store", methods={"GET", "POST"})
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param UserPasswordHasherInterface $passwordHasher
     * @return Response
     */
    public function store(Request $request, EntityManagerInterface $manager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $adresse = new Adresse();
        $adresse->setLibelle($request->request->get('libelle'));
        $adresse->setCp($request->request->get('cp'));
        $adresse->setVille($request->request->get('ville'));
        $manager->persist($adresse);
        $manager->flush();

        $user = new User;

        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $request->request->get('password')
        );

        $user->setEmail($request->request->get('email'));
        $user->setPassword($hashedPassword);
        $user->setAdresse($adresse);
        $manager->persist($user);
        $manager->flush();

        return $this->redirectToRoute('user_index');
    }

    /**
     * @Route("/{user}_{adresse}", name="user_show", methods={"GET"})
     * @param User $user
     * @param Adresse $adresse
     * @return Response
     */
    public function show(User $user, Adresse $adresse): Response
    {

        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index', [], Response::HTTP_SEE_OTHER);
    }
}
