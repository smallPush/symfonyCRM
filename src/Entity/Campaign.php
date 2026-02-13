<?php

namespace App\Entity;

use App\Repository\CampaignRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CampaignRepository::class)]
class Campaign
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?array $videoConfig = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private ?string $financialGoal = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, options: ['default' => '0.00'])]
    private ?string $currentAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2, nullable: true)]
    private ?string $totalInvestment = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $roiPercentage = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, Asset>
     */
    #[ORM\OneToMany(targetEntity: Asset::class, mappedBy: 'campaign', orphanRemoval: true)]
    private Collection $assets;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'campaign')]
    private Collection $transactions;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'managedCampaigns')]
    private Collection $managers;

    public function __construct()
    {
        $this->assets = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->managers = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getVideoConfig(): ?array
    {
        return $this->videoConfig;
    }

    public function setVideoConfig(?array $videoConfig): static
    {
        $this->videoConfig = $videoConfig;

        return $this;
    }

    public function getFinancialGoal(): ?string
    {
        return $this->financialGoal;
    }

    public function setFinancialGoal(string $financialGoal): static
    {
        $this->financialGoal = $financialGoal;

        return $this;
    }

    public function getCurrentAmount(): ?string
    {
        return $this->currentAmount;
    }

    public function setCurrentAmount(string $currentAmount): static
    {
        $this->currentAmount = $currentAmount;

        return $this;
    }

    public function getTotalInvestment(): ?string
    {
        return $this->totalInvestment;
    }

    public function setTotalInvestment(?string $totalInvestment): static
    {
        $this->totalInvestment = $totalInvestment;

        return $this;
    }

    public function getRoiPercentage(): ?string
    {
        return $this->roiPercentage;
    }

    public function setRoiPercentage(?string $roiPercentage): static
    {
        $this->roiPercentage = $roiPercentage;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, Asset>
     */
    public function getAssets(): Collection
    {
        return $this->assets;
    }

    public function addAsset(Asset $asset): static
    {
        if (!$this->assets->contains($asset)) {
            $this->assets->add($asset);
            $asset->setCampaign($this);
        }

        return $this;
    }

    public function removeAsset(Asset $asset): static
    {
        if ($this->assets->removeElement($asset)) {
            // set the owning side to null (unless already changed)
            if ($asset->getCampaign() === $this) {
                $asset->setCampaign(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setCampaign($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getCampaign() === $this) {
                $transaction->setCampaign(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getManagers(): Collection
    {
        return $this->managers;
    }

    public function addManager(User $manager): static
    {
        if (!$this->managers->contains($manager)) {
            $this->managers->add($manager);
            $manager->addManagedCampaign($this);
        }

        return $this;
    }

    public function removeManager(User $manager): static
    {
        if ($this->managers->removeElement($manager)) {
            $manager->removeManagedCampaign($this);
        }

        return $this;
    }
}
