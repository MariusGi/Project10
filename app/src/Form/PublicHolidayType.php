<?php

namespace App\Form;

use App\Entity\Country;
use App\Repository\CountryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
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
            ->add('country', ChoiceType::class, [
                'choices' => $this->getAvailableCountries(),
            ])
            ->add('year', NumberType::class)
            ->add('get_data', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Country::class,
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
