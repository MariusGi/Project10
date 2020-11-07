<?php

namespace App\Entity;

use App\Repository\PublicHolidayRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PublicHolidayRepository::class)
 */
class PublicHoliday
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint")
     */
    private $year;

    /**
     * @ORM\Column(type="json")
     */
    private $month_day = [];

    /**
     * @ORM\Column(type="smallint")
     */
    private $total_amount;

    /**
     * @ORM\Column(type="smallint")
     */
    private $max_free_days_in_a_row;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Country", inversedBy="public_holiday", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $country;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getMonthDay(): ?array
    {
        return $this->month_day;
    }

    public function setMonthDay(array $month_day): self
    {
        $this->month_day = $month_day;

        return $this;
    }

    public function getTotalAmount(): ?int
    {
        return $this->total_amount;
    }

    public function setTotalAmount(int $total_amount): self
    {
        $this->total_amount = $total_amount;

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

    public function getMaxFreeDaysInARow(): ?int
    {
        return $this->max_free_days_in_a_row;
    }

    public function setMaxFreeDaysInARow(int $max_free_days_in_a_row): self
    {
        $this->max_free_days_in_a_row = $max_free_days_in_a_row;

        return $this;
    }
}
