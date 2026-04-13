<?php

namespace App\Controller;

use App\DTO\ProjectData;
use App\Entity\User;
use App\Form\ProjectType;
use App\Service\ProjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/projects')]
class ProjectController extends AbstractController
{
    #[Route('', name: 'app_project_index', methods: ['GET'])]
    public function index(ProjectService $projectService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('project/index.html.twig', [
            'projects' => $projectService->listForUser($user),
        ]);
    }

    #[Route('/new', name: 'app_project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProjectService $projectService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = new ProjectData();
        $form = $this->createForm(ProjectType::class, $data, [
            'client_choices' => $projectService->clientChoicesForUser($user),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $project = $projectService->create($user, $data);
            $this->addFlash('success', 'Project created successfully.');

            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        return $this->render('project/form.html.twig', [
            'form' => $form,
            'pageTitle' => 'New project',
            'submitLabel' => 'Create project',
        ]);
    }

    #[Route('/{id}', name: 'app_project_show', methods: ['GET'])]
    public function show(int $id, ProjectService $projectService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('project/show.html.twig', [
            'project' => $projectService->getForUser($id, $user),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, ProjectService $projectService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $project = $projectService->getForUser($id, $user);
        $data = new ProjectData();
        $data->clientId = $project->getClient()?->getId();
        $data->name = $project->getName();
        $data->code = $project->getCode();
        $data->billingModel = $project->getBillingModel();
        $data->hourlyRate = $project->getHourlyRate();
        $data->internalCostRateDefault = $project->getInternalCostRateDefault();
        $data->slaMonthlyFee = $project->getSlaMonthlyFee();
        $data->monthlyHoursIncluded = $project->getMonthlyHoursIncluded();
        $data->fixedMonthlyRetainer = $project->getFixedMonthlyRetainer();
        $data->activeFrom = $project->getActiveFrom()?->format('Y-m-d');
        $data->activeUntil = $project->getActiveUntil()?->format('Y-m-d');
        $data->isActive = $project->isActive();
        $data->description = $project->getDescription();

        $form = $this->createForm(ProjectType::class, $data, [
            'client_choices' => $projectService->clientChoicesForUser($user),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $projectService->update($project, $data, $user);
            $this->addFlash('success', 'Project updated successfully.');

            return $this->redirectToRoute('app_project_show', ['id' => $project->getId()]);
        }

        return $this->render('project/form.html.twig', [
            'form' => $form,
            'pageTitle' => 'Edit project',
            'submitLabel' => 'Save changes',
        ]);
    }

    #[Route('/{id}/delete', name: 'app_project_delete', methods: ['POST'])]
    public function delete(int $id, Request $request, ProjectService $projectService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $project = $projectService->getForUser($id, $user);

        if ($this->isCsrfTokenValid('delete_project_' . $project->getId(), (string) $request->request->get('_token'))) {
            $projectService->delete($project);
            $this->addFlash('success', 'Project deleted.');
        }

        return $this->redirectToRoute('app_project_index');
    }
}
