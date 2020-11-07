<?php

namespace App\Form;

use App\Repository\CountryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublicHolidayType extends AbstractType
{
    private $countryRepository;

    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('country', EntityType::class, [
                'class' => 'App\Entity\Country',
            ])
            ->add('year', IntegerType::class)
            ->add('get_data', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            //
        ]);
    }

    private function getAvailableCountries()
    {
        $availableCountriesArr = [];

        $availableCountiesData = $this->countryRepository->findAll();

        foreach ($availableCountiesData as $availableCountryData) {
            $id = $availableCountryData->getId();
            $fullName = $availableCountryData->getFullName();

            $availableCountriesArr[$fullName] = $id;
        }

        if (empty($availableCountriesArr)) {
            $availableCountriesArr['No available countries'] = 0;
        }

        return $availableCountriesArr;
    }
}
