<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\Phone;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Phone creation

        // 1
        $phone = new Phone();
        $phone->setBrand('Samsung');
        $phone->setModel('Galaxy Z Fold5');
        $phone->setPrice('1899€');
        $phone->setColor('Blanc');
        $phone->setScreenSize('7.6');
        $phone->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus vestibulum in mi ornare tincidunt. In semper mi consectetur sodales gravida nullam.');
        $manager->persist($phone);

        // 2
        $phone = new Phone();
        $phone->setBrand('Samsung');
        $phone->setModel('Galaxy Z Flip5');
        $phone->setPrice('1199€');
        $phone->setColor('Vert');
        $phone->setScreenSize('6.7');
        $phone->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus vestibulum in mi ornare tincidunt. In semper mi consectetur sodales gravida nullam.');
        $manager->persist($phone);

        // 3
        $phone = new Phone();
        $phone->setBrand('Apple');
        $phone->setModel('iPhone 15 Pro Max');
        $phone->setPrice('1459€');
        $phone->setColor('Noir');
        $phone->setScreenSize('6.7');
        $phone->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus vestibulum in mi ornare tincidunt. In semper mi consectetur sodales gravida nullam.');
        $manager->persist($phone);

        // 4
        $phone = new Phone();
        $phone->setBrand('Samsung');
        $phone->setModel('Galaxy S23 Ultra');
        $phone->setPrice('1419€');
        $phone->setColor('Noir');
        $phone->setScreenSize('6.8');
        $phone->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus vestibulum in mi ornare tincidunt. In semper mi consectetur sodales gravida nullam.');

        $manager->persist($phone);

        // 5
        $phone = new Phone();
        $phone->setBrand('Apple');
        $phone->setModel('iPhone 14 Pro Max');
        $phone->setPrice('1239€');
        $phone->setColor('Gris');
        $phone->setScreenSize('6.7');
        $phone->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus vestibulum in mi ornare tincidunt. In semper mi consectetur sodales gravida nullam.');
        $manager->persist($phone);

        // 6
        $phone = new Phone();
        $phone->setBrand('Samsung');
        $phone->setModel('Galaxy S23+');
        $phone->setPrice('1219€');
        $phone->setColor('Blanc');
        $phone->setScreenSize('6.6');
        $phone->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus vestibulum in mi ornare tincidunt. In semper mi consectetur sodales gravida nullam.');
        $manager->persist($phone);

        // 7
        $phone = new Phone();
        $phone->setBrand('Apple');
        $phone->setModel('iPhone 15 Pro');
        $phone->setPrice('1209€');
        $phone->setColor('Gris');
        $phone->setScreenSize('6.1');
        $phone->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus vestibulum in mi ornare tincidunt. In semper mi consectetur sodales gravida nullam.');
        $manager->persist($phone);

        // 8
        $phone = new Phone();
        $phone->setBrand('Apple');
        $phone->setModel('iPhone 14 Pro');
        $phone->setPrice('1139€');
        $phone->setColor('Noir');
        $phone->setScreenSize('6.1');
        $phone->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus vestibulum in mi ornare tincidunt. In semper mi consectetur sodales gravida nullam.');
        $manager->persist($phone);

        // 9
        $phone = new Phone();
        $phone->setBrand('Google');
        $phone->setModel('Pixel 8 Pro');
        $phone->setPrice('1089€');
        $phone->setColor('Noir');
        $phone->setScreenSize('6.7');
        $phone->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus vestibulum in mi ornare tincidunt. In semper mi consectetur sodales gravida nullam.');
        $manager->persist($phone);

        // 10
        $phone = new Phone();
        $phone->setBrand('Xiaomi');
        $phone->setModel('13');
        $phone->setPrice('999€');
        $phone->setColor('Gris');
        $phone->setScreenSize('6.36');
        $phone->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus vestibulum in mi ornare tincidunt. In semper mi consectetur sodales gravida nullam.');
        $manager->persist($phone);

        $manager->flush();

        // Customer creation

        // 1
        $customer = new Customer();
        $customer->setName('Orange');
        $customer->setEmail('orange@mail.fr');
        $manager->persist($customer);

        // 2
        $customer = new Customer();
        $customer->setName('SFR');
        $customer->setEmail('sfr@mail.fr');
        $manager->persist($customer);

        // 3
        $customer = new Customer();
        $customer->setName('Bouygues Telecom');
        $customer->setEmail('bouygues@mail.fr');
        $manager->persist($customer);

        // 4
        $customer = new Customer();
        $customer->setName('Free');
        $customer->setEmail('free@mail.fr');
        $manager->persist($customer);

        $manager->flush();

        // User creation

        $customerRepository = $manager->getRepository(Customer::class);
        $customers = $customerRepository->findAll();
        foreach ($customers as $customer)
        {
            for ($i = 0; $i < 50; $i++)
            {
            $user = (new User())
                ->setFirstName('firstname_'. $i)
                ->setLastName('lastname_'. $i)
                ->setUserName('username_'. $i)
                ->setEmail('user_'.$i.'@mail.fr')
                ->setCustomer($customer);
            
            $manager->persist($user);
            }
        }
        $manager->flush();
    }
}
