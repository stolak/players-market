<?php

namespace App\Entity;

use App\Repository\OrdersRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrdersRepository::class)]
class Orders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $player_id = null;

    #[ORM\Column]
    protected ?int $team_id = null;

    #[ORM\Column]
    protected ?int $interested_team = null;

    #[ORM\Column]
    protected ?float $offer_amount = null;

    #[ORM\Column]
    private ?bool $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlayerId(): ?int
    {
        return $this->player_id;
    }

    public function setPlayerId(int $player_id): self
    {
        $this->player_id = $player_id;

        return $this;
    }

    public function getTeamId(): ?int
    {
        return $this->team_id;
    }

    public function setTeamId(int $team_id): self
    {
        $this->team_id = $team_id;

        return $this;
    }

    public function getInterestedTeam(): ?int
    {
        return $this->interested_team;
    }

    public function setInterestedTeam(int $interested_team): self
    {
        $this->interested_team = $interested_team;

        return $this;
    }

    public function getOfferAmount(): ?float
    {
        return $this->offer_amount;
    }

    public function setOfferAmount(float $offer_amount): self
    {
        $this->offer_amount = $offer_amount;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }
}
