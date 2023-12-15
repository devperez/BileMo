<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Phone;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        for ($i = 0; $i < 250; $i++) {
            $phone = new Phone();
            $phone->setBrand($faker->randomElement(['Apple', 'Samsung', 'Google', 'Huawei', 'Xiaomi']));
            $phone->setModel($faker->word);
            $phone->setPrice($faker->numberBetween(500, 1900));
            $phone->setColor($faker->safeColorName);
            $phone->setScreenSize($faker->randomFloat(2, 4.5, 7.0));
            $phone->setDescription($faker->text(200));
            $manager->persist($phone);
        }
        $manager->flush();

        // Customer creation

        //1
        $customer = new Customer();
        $customer->setName('Orange');
        $customer->setEmail('orange@mail.fr');
        $customer->setPassword($this->passwordHasher->hashPassword($customer, '123456'));
        $customer->setRoles(["customer"]);
        $manager->persist($customer);

        // 2
        $customer = new Customer();
        $customer->setName('SFR');
        $customer->setEmail('sfr@mail.fr');
        $customer->setPassword($this->passwordHasher->hashPassword($customer, '123456'));
        $customer->setRoles(["customer"]);
        $manager->persist($customer);

        // 3
        $customer = new Customer();
        $customer->setName('Bouygues Telecom');
        $customer->setEmail('bouygues@mail.fr');
        $customer->setPassword($this->passwordHasher->hashPassword($customer, '123456'));
        $customer->setRoles(["customer"]);
        $manager->persist($customer);

        // 4
        $customer = new Customer();
        $customer->setName('Free');
        $customer->setEmail('free@mail.fr');
        $customer->setPassword($this->passwordHasher->hashPassword($customer, '123456'));
        $customer->setRoles(["customer"]);
        $manager->persist($customer);

        $manager->flush();

        // User creation

        $customerRepository = $manager->getRepository(Customer::class);
        $customers = $customerRepository->findAll();
        $faker = Factory::create();

        foreach ($customers as $customer)
        {
            for ($i = 0; $i < 150; $i++)
            {
            $user = (new User())
                ->setFirstName($faker->firstName)
                ->setLastName($faker->lastName)
                ->setUserName($faker->userName)
                ->setEmail($faker->unique()->email)
                ->setCustomer($customer);
            
            $manager->persist($user);
            }
        }
        $manager->flush();
    }
}
