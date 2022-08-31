<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Task;
use App\Entity\Categories;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class TaskController extends AbstractController
{
    #[Route('/task/listing', name: 'app_listing_task')]
    public function listing(ManagerRegistry $doctrine): Response
    {
        $tasks = $doctrine->getRepository(Task::class)->findAll();
        $categories = $doctrine->getRepository(Categories::class)->findAll();

        return $this->render('task/listing.html.twig', ['tasks' => $tasks, 'categories' => $categories]);
    }

    #[Route('/task/add', name: 'app_add_task')]
    public function add(Request $request, ManagerRegistry $doctrine, TranslatorInterface $translator): Response
    {
        $task = new Task();
        $task->setNameTask('Write a blog post');
        $task->setDueDateTask(new \DateTime('tomorrow'));

        $form = $this->createFormBuilder($task)
            ->add('nameTask', TextType::class)
            ->add('dueDateTask', DateType::class, [
                'label' => $translator->trans('task.duedate'),
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('descriptionTask', TextareaType::class)
            ->add('priorityTask', choiceType::class, [
                'choices' => [
                    $translator->trans('priority.high') =>
                    $translator->trans('priority.high'),
                    $translator->trans('priority.medium') =>
                    $translator->trans('priority.medium'),
                    $translator->trans('priority.low') =>
                    $translator->trans('priority.low'),
                ], 'attr' => ['class' => 'form-control']
            ])
            ->add('category', EntityType::class, [
                // looks for choices from this entity
                'class' => Categories::class,

                // uses the User.username property as the visible option string
                'choice_label' => 'libelleCategory',

                // used to render a select box, check boxes or radios
                // 'multiple' => true,
                // 'expanded' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('descriptionTask', TextareaType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Task', 'attr' => ['class' => 'form-control']])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            $task->setCreatedDateTask(new \DateTime('now'));
            $entityManager = $doctrine->getManager();
            // tell Doctrine you want to (eventually) save the Product (no queries yet)
            $entityManager->persist($task);

            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();

            $this->addFlash(
                'notice',
                'La tâche a été créée !'
            );
            return $this->redirectToRoute('app_listing_task');
        }
        return $this->render('task/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/task/update/{id}', name: 'app_update_task')]
    public function update($id, Request $request, ManagerRegistry $doctrine): Response
    {
        // $task = new Task();
        // $task->setNameTask('Write a blog post');
        // $task->setDueDateTask(new \DateTime('tomorrow'));
        $task =  $doctrine->getRepository(Task::class)->find($id);

        $form = $this->createFormBuilder($task)
            ->add('nameTask', TextType::class, ['attr' => ['class' => 'form-control']])
            ->add('dueDateTask', DateType::class, ['widget' => 'single_text', 'attr' => ['class' => 'form-control']])
            ->add('priorityTask', ChoiceType::class, [
                'choices'  => [
                    'Haute' => 'Haute',
                    'Normale' => 'Normale',
                    'Basse' => 'Basse',
                ], 'attr' => ['class' => 'form-control']
            ])
            ->add('category', EntityType::class, [
                // looks for choices from this entity
                'class' => Categories::class,

                // uses the User.username property as the visible option string
                'choice_label' => 'libelleCategory',

                // used to render a select box, check boxes or radios
                // 'multiple' => true,
                // 'expanded' => true,
                'attr' => ['class' => 'form-control']
            ])
            ->add('descriptionTask', TextareaType::class, ['attr' => ['class' => 'form-control']])
            ->add('save', SubmitType::class, ['label' => 'Update Task', 'attr' => ['class' => 'form-control']])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            $task->setCreatedDateTask(new \DateTime('now'));
            $entityManager = $doctrine->getManager();
            // tell Doctrine you want to (eventually) save the Product (no queries yet)
            $entityManager->persist($task);

            // actually executes the queries (i.e. the INSERT query)
            $entityManager->flush();

            $this->addFlash(
                'notice',
                'La tâche a été modifiée !'
            );
            return $this->redirectToRoute('app_listing_task');
        }
        return $this->render('task/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/task/delete/{id}', name: 'app_delete_task')]
    public function delete($id, Request $request, ManagerRegistry $doctrine): Response
    {
        $task =  $doctrine->getRepository(Task::class)->find($id);
        $entityManager = $doctrine->getManager();
        // tell Doctrine you want to (eventually) save the Product (no queries yet)
        $entityManager->remove($task);

        // actually executes the queries (i.e. the INSERT query)
        $entityManager->flush();

        $this->addFlash(
            'notice',
            'La tâche a été supprimée !'
        );
        return $this->redirectToRoute('app_listing_task');
    }
}
