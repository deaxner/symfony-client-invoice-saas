<?php

namespace App\Controller\Api;

use App\DTO\ProjectData;
use App\Entity\User;
use App\Form\ProjectType;
use App\Service\ProjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/projects')]
class ProjectApiController extends AbstractController
{
    #[Route('', name: 'api_project_index', methods: ['GET'])]
    public function index(ProjectService $projectService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'data' => array_map([$projectService, 'serialize'], $projectService->listForUser($user)),
        ]);
    }

    #[Route('/{id}', name: 'api_project_show', methods: ['GET'])]
    public function show(int $id, ProjectService $projectService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'data' => $projectService->serialize($projectService->getForUser($id, $user)),
        ]);
    }

    #[Route('', name: 'api_project_create', methods: ['POST'])]
    public function create(Request $request, ProjectService $projectService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = $this->submit(
            $request,
            new ProjectData(),
            $projectService,
            $user,
        );
        $project = $projectService->create($user, $data);

        return $this->json(['data' => $projectService->serialize($project)], 201);
    }

    #[Route('/{id}', name: 'api_project_update', methods: ['PUT'])]
    public function update(int $id, Request $request, ProjectService $projectService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $project = $projectService->getForUser($id, $user);
        $data = $this->submit(
            $request,
            new ProjectData(),
            $projectService,
            $user,
        );
        $projectService->update($project, $data, $user);

        return $this->json(['data' => $projectService->serialize($project)]);
    }

    #[Route('/{id}', name: 'api_project_delete', methods: ['DELETE'])]
    public function delete(int $id, ProjectService $projectService): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $project = $projectService->getForUser($id, $user);
        $projectService->delete($project);

        return $this->json(['data' => ['deleted' => true]]);
    }

    private function submit(Request $request, ProjectData $data, ProjectService $projectService, User $user): ProjectData
    {
        $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $form = $this->createForm(ProjectType::class, $data, [
            'csrf_protection' => false,
            'client_choices' => $projectService->clientChoicesForUser($user),
        ]);
        $form->submit($payload);

        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $origin = $error->getOrigin();
                $field = $origin?->getName() ?? 'form';
                $errors[$field][] = $error->getMessage();
            }

            throw new \InvalidArgumentException(json_encode($errors, JSON_THROW_ON_ERROR));
        }

        return $data;
    }
}
