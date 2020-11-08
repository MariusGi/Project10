<?php

namespace App\Entity;

use App\Repository\DailyCountryChecksOnRequestRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DailyCountryChecksOnRequestRepository::class)
 */
class DailyCountryChecksOnRequest
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type_of_day;

    /**
     * @ORM\Column(type="date")
     */
    private $updated_on;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Country", inversedBy="daily_country_checks_on_request", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $country;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTypeOfDay(): ?string
    {
        return $this->type_of_day;
    }

    public function setTypeOfDay(string $type_of_day): self
    {
        $this->type_of_day = $type_of_day;

        return $this;
    }

    public function getUpdatedOn(): ?\DateTimeInterface
    {
        return $this->updated_on;
    }

    public function setUpdatedOn(\DateTimeInterface $updated_on): self
    {
        $this->updated_on = $updated_on;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): self
    {
        $this->country = $country;

        return $this;
    }
}
