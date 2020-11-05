<?php

namespace App\Entity;

use App\Repository\CountryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CountryRepository::class)
 */
class Country
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $country_code;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $full_name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\PublicHoliday", mappedBy="country")
     */
    private $public_holiday;

    public function __construct()
    {
        $this->public_holiday = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCountryCode(): ?string
    {
        return $this->country_code;
    }

    public function setCountryCode(string $country_code): self
    {
        $this->country_code = $country_code;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->full_name;
    }

    public function setFullName(string $full_name): self
    {
        $this->full_name = $full_name;

        return $this;
    }

    /**
     * @return Collection|PublicHoliday[]
     */
    public function getPublicHoliday(): Collection
    {
        return $this->public_holiday;
    }

    public function addPublicHoliday(PublicHoliday $publicHoliday): self
    {
        if (!$this->public_holiday->contains($publicHoliday)) {
            $this->public_holiday[] = $publicHoliday;
            $publicHoliday->setCountry($this);
        }

        return $this;
    }

    public function removePublicHoliday(PublicHoliday $publicHoliday): self
    {
        if ($this->public_holiday->removeElement($publicHoliday)) {
            // set the owning side to null (unless already changed)
            if ($publicHoliday->getCountry() === $this) {
                $publicHoliday->setCountry(null);
            }
        }

        return $this;
    }
}
