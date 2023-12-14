<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager){}

    #[Route('/team/create', name: 'team', methods: ['POST', 'GET'], format: 'json')]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $team = new Team();
        $formTeam = $this->createForm(TeamType::class, $team);
        $formTeam->handleRequest($request);

        $formTeam->submit($data);

        if ($formTeam->isValid()) {
            $this->entityManager->persist($team);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Team created successfully',
                'teamName' => $team->getTeamName(),
                'region' => $team->getRegion(),
                'roster' => $team->getRoster(),
            ], 200);
        }
        return $this->json([
            'message' => 'Team not created',
            'Team' =>$team,
        ], 404);
    }

    #[Route('/teams', name: '_getTeams', methods: ['GET'], format: 'json')]
    public function getTeams(): JsonResponse
    {
        $teams = $this->entityManager->getRepository(Team::class)->findAll();
        if (isset($teams)) {
            $teamsData = [];
            foreach ($teams as $team) {
                $teamsData[] = [
                    'teamName' => $team->getTeamName(),
                    'region' => $team->getRegion(),
                    'roster' => $team->getRoster(),
                ];
            }
            return $this->json([
                'message' => 'Teams get successfully',
                'teams' => $teamsData,
            ], 200);
        }
        return $this->json([
            'message' => 'Teams list does not exist',
        ], 404);
    }

    #[Route('/team/{id}', name: '_getTeam', methods: ['GET'], format: 'json')]
    public function getTeam(int $id): JsonResponse
    {
        $team= $this->entityManager->getRepository(Team::class)->find($id);
        if (isset($team)) {
            return $this->json([
                'message' => 'Team get successfully',
                'teamName' => $team->getTeamName(),
                'region' => $team->getRegion(),
                'roster' => $team->getRoster(),
            ], 200);
        }
        return $this->json([
            'message' => 'Teams list does not exist',
        ], 404);
    }

    #[Route('/team/update/{id}', name: '_patchTeam', methods: ['PATCH'], format: 'json')]
    public function patchTeam(Request $request, int $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $team= $this->entityManager->getRepository(Team::class)->find($id);
        if (array_key_exists('teamName', $data)) {$team->setName($data['teamName']);
        }
        if (array_key_exists('region', $data)) {$team->setRegion($data['region']);
        }
        if (array_key_exists('roster', $data)) {$team->setRoster($data['roster']);
        }
        if (!empty($team)) {
            $this->entityManager->persist($team);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Team get successfully',
                'teamName' =>$team->getTeamName(),
                'region' =>$team->getRegion(),
                'roster' =>$team->getRoster(),
            ], 200);
        }
        return $this->json([
            'message' => 'Teams list does not exist',
        ], 404);
    }

    /**
     * @throws Exception
     */
    #[Route('/team/delete/{id}', name: '_deleteTeam', methods: ['DELETE'], format: 'json')]
    public function deleteTeam(int $id): JsonResponse
    {
        try {
            $team= $this->entityManager->getRepository(Team::class)->find($id);

            if (!$team) {
                throw new Exception('Team not found');
            }
            $this->entityManager->remove($team);
            $this->entityManager->flush();

            return $this->json([
                'message' => 'Team deleted successfully',
            ], 200);
        } catch (Exception $exception) {
            return $this->json([
                'message' => 'Team does not exists' . $exception,
            ], 404);
        }
    }
}
