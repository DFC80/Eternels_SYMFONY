<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\Consumption;
use App\Entity\ConsumptionItem;
use App\Entity\Equipment;
use App\Entity\Event;
use App\Entity\EventParticipation;
use App\Entity\Game;
use App\Entity\Meal;
use App\Entity\Membership;
use App\Entity\Photo;
use App\Entity\Subscription;
use App\Entity\SubscriptionRate;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private Generator $faker;

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $activities = $this->loadActivities($manager);
        $rates      = $this->loadSubscriptionRates($manager, $activities);
        $items      = $this->loadConsumptionItems($manager);
        $users      = $this->loadUsers($manager);
        $this->loadMemberships($manager, $users, $activities);
        $this->loadSubscriptions($manager, $users, $rates);
        $events     = $this->loadEvents($manager, $activities);
        $this->loadMealsAndParticipations($manager, $events, $users);
        $this->loadEquipment($manager, $users);
        $this->loadGames($manager, $activities);
        $this->loadConsumptions($manager, $users, $items, $events);
        $this->loadPhotos($manager, $activities, $events, $users);

        $manager->flush();
    }

    // ------------------------------------------------------------------ //
    //  Activities
    // ------------------------------------------------------------------ //

    private function loadActivities(ObjectManager $manager): array
    {
        $definitions = [
            ['name' => 'Airsoft',         'type' => 'airsoft',      'description' => 'Jeux de simulation militaire en extérieur avec répliques airsoft. Parties organisées en forêt et en zone urbaine.'],
            ['name' => 'Jeux de société', 'type' => 'board_game',   'description' => 'Soirées jeux de société tous publics : stratégie, coopératif, ambiance et party games.'],
            ['name' => 'Jeux vidéo',      'type' => 'video_game',   'description' => 'Tournois et sessions multijoueurs sur consoles et PC. Ambiance conviviale garantie.'],
            ['name' => 'Jeux de cartes',  'type' => 'card_game',    'description' => 'Magic : The Gathering, Pokémon TCG, jeux de cartes classiques et modernes.'],
            ['name' => 'Jeux de rôle',    'type' => 'role_playing', 'description' => 'Donjons & Dragons, Pathfinder et autres JDR en campagnes régulières ou one-shots.'],
        ];

        $activities = [];
        foreach ($definitions as $def) {
            $activity = new Activity();
            $activity->setName($def['name']);
            $activity->setType($def['type']);
            $activity->setDescription($def['description']);
            $activity->setIsActive(true);
            $manager->persist($activity);
            $activities[] = $activity;
        }

        return $activities;
    }

    // ------------------------------------------------------------------ //
    //  Subscription rates
    // ------------------------------------------------------------------ //

    private function loadSubscriptionRates(ObjectManager $manager, array $activities): array
    {
        $rateMatrix = [
            ['Adulte annuel',              45.00, 'annual',    'adult'],
            ['Étudiant / -26 ans annuel',  30.00, 'annual',    'student'],
            ['Enfant annuel',              20.00, 'annual',    'child'],
            ['Famille annuelle',           80.00, 'annual',    'family'],
            ['Mensuel adulte',             10.00, 'monthly',   'adult'],
        ];

        $rates = [];
        foreach ($activities as $activity) {
            foreach ($rateMatrix as [$label, $amount, $period, $category]) {
                $rate = new SubscriptionRate();
                $rate->setActivity($activity);
                $rate->setLabel($label . ' — ' . $activity->getName());
                $rate->setAmount((string) $amount);
                $rate->setPeriod($period);
                $rate->setMemberCategory($category);
                $rate->setIsActive(true);
                $manager->persist($rate);
                $rates[] = $rate;
            }
        }

        return $rates;
    }

    // ------------------------------------------------------------------ //
    //  Consumption items
    // ------------------------------------------------------------------ //

    private function loadConsumptionItems(ObjectManager $manager): array
    {
        $catalog = [
            ['Coca-Cola',       'drink', 2.00],
            ['Eau minérale',    'drink', 1.00],
            ["Jus d'orange",    'drink', 2.00],
            ['Café',            'drink', 1.50],
            ['Bière pression',  'drink', 3.00],
            ['Soda pétillant',  'drink', 2.00],
            ['Chips',           'snack', 1.50],
            ['Barre chocolatée','snack', 1.00],
            ['Sandwich',        'food',  4.00],
            ['Pizza (part)',    'food',  3.50],
        ];

        $items = [];
        foreach ($catalog as [$name, $category, $price]) {
            $item = new ConsumptionItem();
            $item->setName($name);
            $item->setCategory($category);
            $item->setPrice((string) $price);
            $item->setIsAvailable(true);
            $manager->persist($item);
            $items[] = $item;
        }

        return $items;
    }

    // ------------------------------------------------------------------ //
    //  Users
    // ------------------------------------------------------------------ //

    private function loadUsers(ObjectManager $manager): array
    {
        $users = [];

        // Super-admin
        $admin = new User();
        $admin->setEmail('admin@eternels.fr');
        $admin->setFirstName('Admin');
        $admin->setLastName('Association');
        $admin->setPhone('06 00 00 00 00');
        $admin->setAddress('1 rue du Club, 75001 Paris');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(true);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin1234'));
        $manager->persist($admin);
        $users[] = $admin;

        // Demo member (easy to remember credentials)
        $demo = new User();
        $demo->setEmail('membre@eternels.fr');
        $demo->setFirstName('Jean');
        $demo->setLastName('Durand');
        $demo->setPhone('06 12 34 56 78');
        $demo->setAddress('42 avenue de la Victoire, 69001 Lyon');
        $demo->setRoles(['ROLE_USER']);
        $demo->setIsVerified(true);
        $demo->setPassword($this->passwordHasher->hashPassword($demo, 'membre1234'));
        $manager->persist($demo);
        $users[] = $demo;

        // 20 random members
        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setEmail($this->faker->unique()->safeEmail());
            $user->setFirstName($this->faker->firstName());
            $user->setLastName($this->faker->lastName());
            $user->setPhone($this->faker->phoneNumber());
            $user->setAddress($this->faker->address());
            $user->setRoles(['ROLE_USER']);
            $user->setIsVerified($this->faker->boolean(85));
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
            $manager->persist($user);
            $users[] = $user;
        }

        return $users;
    }

    // ------------------------------------------------------------------ //
    //  Memberships
    // ------------------------------------------------------------------ //

    private function loadMemberships(ObjectManager $manager, array $users, array $activities): void
    {
        $statuses = ['active', 'active', 'active', 'expired', 'cancelled'];

        foreach (array_slice($users, 1) as $user) {
            $count    = $this->faker->numberBetween(1, 3);
            $shuffled = $activities;
            shuffle($shuffled);

            for ($j = 0; $j < $count; $j++) {
                $start = $this->faker->dateTimeBetween('-2 years', 'now');
                $membership = new Membership();
                $membership->setMember($user);
                $membership->setActivity($shuffled[$j]);
                $membership->setStartDate($start);
                $membership->setEndDate((clone $start)->modify('+1 year'));
                $membership->setStatus($this->faker->randomElement($statuses));
                $manager->persist($membership);
            }
        }
    }

    // ------------------------------------------------------------------ //
    //  Subscriptions
    // ------------------------------------------------------------------ //

    private function loadSubscriptions(ObjectManager $manager, array $users, array $rates): void
    {
        foreach (array_slice($users, 1) as $user) {
            $count = $this->faker->numberBetween(1, 4);
            for ($j = 0; $j < $count; $j++) {
                $rate        = $this->faker->randomElement($rates);
                $paymentDate = $this->faker->dateTimeBetween('-2 years', 'now');

                $sub = new Subscription();
                $sub->setMember($user);
                $sub->setRate($rate);
                $sub->setAmountPaid($rate->getAmount());
                $sub->setPaymentDate($paymentDate);
                $sub->setValidFrom($paymentDate);
                $sub->setValidUntil((clone $paymentDate)->modify('+1 year'));
                $sub->setPaymentMethod($this->faker->randomElement(['cash', 'card', 'transfer', 'check']));
                $sub->setStatus('paid');
                $manager->persist($sub);
            }
        }
    }

    // ------------------------------------------------------------------ //
    //  Events
    // ------------------------------------------------------------------ //

    private function loadEvents(ObjectManager $manager, array $activities): array
    {
        $eventDefs = [
            // [title, activity index, status, daysOffset from today]
            ['Partie Airsoft — Forêt de Rambouillet',  0, 'open',      +14],
            ['Tournoi Airsoft CQB',                    0, 'open',      +30],
            ['Opération Nuit Noire (Airsoft)',          0, 'planned',   +60],
            ['Soirée Catan & Stratégie',               1, 'open',       +7],
            ['Nuit des Jeux de Société',               1, 'completed', -10],
            ['Tournoi Carcassonne',                    1, 'planned',   +21],
            ['LAN Party — FPS & Stratégie',            2, 'open',      +10],
            ['Tournoi Super Smash Bros',               2, 'completed',  -5],
            ['Draft Magic : The Gathering',            3, 'open',      +12],
            ['Soirée Jeu de Rôle — Aventure D&D',     4, 'open',       +9],
        ];

        $events = [];
        foreach ($eventDefs as [$title, $actIdx, $status, $days]) {
            $startDate = (new \DateTime())->modify("{$days} days");

            $event = new Event();
            $event->setTitle($title);
            $event->setDescription($this->faker->paragraphs(2, true));
            $event->setStartDate($startDate);
            $event->setEndDate((clone $startDate)->modify('+6 hours'));
            $event->setLocation($this->faker->city() . ' — ' . $this->faker->streetAddress());
            $event->setActivity($activities[$actIdx]);
            $event->setMaxParticipants($this->faker->numberBetween(10, 40));
            $event->setPrice($this->faker->randomElement(['0', '5.00', '10.00', '15.00']));
            $event->setStatus($status);
            $manager->persist($event);
            $events[] = $event;
        }

        return $events;
    }

    // ------------------------------------------------------------------ //
    //  Meals (airsoft events) + Participations
    // ------------------------------------------------------------------ //

    private function loadMealsAndParticipations(ObjectManager $manager, array $events, array $users): void
    {
        $members = array_slice($users, 1);

        foreach ($events as $event) {
            $meals = [];

            if ($event->isAirsoftEvent()) {
                foreach ([
                    ['Burger + frites',   8.50, false],
                    ['Salade composée',   7.00, true],
                    ['Croque-monsieur',   6.00, false],
                    ['Wrap végétarien',   7.50, true],
                ] as [$name, $price, $veg]) {
                    $meal = new Meal();
                    $meal->setEvent($event);
                    $meal->setName($name);
                    $meal->setPrice((string) $price);
                    $meal->setIsVegetarian($veg);
                    $meal->setMaxQuantity(20);
                    $meal->setDescription($this->faker->sentence());
                    $manager->persist($meal);
                    $meals[] = $meal;
                }
            }

            $shuffled = $members;
            shuffle($shuffled);
            $count = $this->faker->numberBetween(3, min(12, count($members)));

            foreach (array_slice($shuffled, 0, $count) as $user) {
                $participation = new EventParticipation();
                $participation->setEvent($event);
                $participation->setMember($user);
                $participation->setStatus(
                    $this->faker->randomElement(['registered', 'confirmed', 'attended'])
                );
                if ($meals) {
                    $participation->setSelectedMeal($this->faker->randomElement($meals));
                }
                $manager->persist($participation);
            }
        }
    }

    // ------------------------------------------------------------------ //
    //  Equipment (airsoft)
    // ------------------------------------------------------------------ //

    private function loadEquipment(ObjectManager $manager, array $users): void
    {
        $catalog = [
            ['AEG M4 Carbine',            'weapon',     3, 'good',  180.00],
            ['Sniper VSR-10',             'weapon',     2, 'good',  150.00],
            ['Pistolet GBB Glock 18',     'weapon',     1, 'new',    90.00],
            ['Masque intégral protection','protection', 10, 'good',   25.00],
            ['Gilet tactique',            'protection',  8, 'fair',   30.00],
            ['Genouillères / coudières',  'protection',  6, 'good',   20.00],
            ['Tenue camouflage forêt',    'clothing',    5, 'good',   40.00],
            ['Radio PMR446',              'accessory',   6, 'new',    25.00],
            ['Chargeur 500 billes',       'accessory',  20, 'good',    8.00],
            ['Chrono compteur de billes', 'accessory',   1, 'good',   55.00],
        ];

        $members = array_slice($users, 1);

        foreach ($catalog as [$name, $cat, $qty, $cond, $price]) {
            $equipment = new Equipment();
            $equipment->setName($name);
            $equipment->setCategory($cat);
            $equipment->setQuantity($qty);
            $equipment->setCondition($cond);
            $equipment->setPurchasePrice((string) $price);
            $equipment->setPurchaseDate(
                $this->faker->dateTimeBetween('-3 years', '-6 months')
            );
            $equipment->setIsAvailable($this->faker->boolean(80));
            $equipment->setDescription($this->faker->sentence());

            if (!$equipment->isAvailable() && $members) {
                $equipment->setAssignedTo($this->faker->randomElement($members));
            }

            $manager->persist($equipment);
        }
    }

    // ------------------------------------------------------------------ //
    //  Games
    // ------------------------------------------------------------------ //

    private function loadGames(ObjectManager $manager, array $activities): void
    {
        $activityMap = [];
        foreach ($activities as $activity) {
            $activityMap[$activity->getType()] = $activity;
        }

        $gamesByType = [
            'board_game' => [
                ['Catan',               2,  4, 'Kosmos',              1995, 'medium',  90],
                ['Carcassonne',         2,  5, 'Hans im Glück',       2000, 'easy',    45],
                ['Pandemic',            2,  4, 'Z-Man Games',         2008, 'medium',  60],
                ['7 Wonders',           2,  7, 'Repos Production',    2010, 'medium',  45],
                ['Terraforming Mars',   1,  5, 'FryxGames',           2016, 'hard',   150],
                ['Ticket to Ride',      2,  5, 'Days of Wonder',      2004, 'easy',    75],
                ['Wingspan',            1,  5, 'Stonemaier',          2019, 'medium',  70],
                ['Azul',                2,  4, 'Plan B Games',        2017, 'easy',    45],
                ['Root',                2,  4, 'Leder Games',         2018, 'hard',    90],
                ['Gloomhaven',          1,  4, 'Cephalofair',         2017, 'expert', 180],
            ],
            'video_game' => [
                ['Super Smash Bros Ultimate', 2, 8, 'Nintendo',  2018, 'medium', 30],
                ['Mario Kart 8 Deluxe',       2, 4, 'Nintendo',  2017, 'easy',   30],
                ['Street Fighter 6',          2, 2, 'Capcom',    2023, 'hard',   15],
                ['Age of Empires IV',         2, 8, 'Relic',     2021, 'hard',   60],
                ['Rocket League',             2, 6, 'Psyonix',   2015, 'medium', 20],
                ['FIFA 25',                   2, 4, 'EA Sports', 2024, 'easy',   15],
                ['Starcraft II',              2, 2, 'Blizzard',  2010, 'expert', 40],
                ['Minecraft',                 2, 8, 'Mojang',    2011, 'easy',   60],
            ],
            'card_game' => [
                ['Magic : The Gathering',  2, 2, 'Wizards of the Coast',         1993, 'expert', 30],
                ['Pokémon TCG',            2, 2, 'The Pokémon Company',           1996, 'medium', 20],
                ['Hearthstone',            2, 2, 'Blizzard',                      2014, 'medium', 20],
                ['Flesh and Blood',        2, 2, 'Legend Story Studios',          2019, 'hard',   30],
                ['Uno',                    2,10, 'Mattel',                         1971, 'easy',   15],
                ['Belote',                 4, 4, 'Traditionnel',                  null, 'medium', 30],
            ],
            'role_playing' => [
                ['Donjons & Dragons 5e',    2, 6, 'Wizards of the Coast', 2014, 'medium', 240],
                ['Pathfinder 2e',           2, 6, 'Paizo',                2019, 'hard',   240],
                ['Call of Cthulhu',         2, 6, 'Chaosium',             1981, 'medium', 180],
                ['Starfinder',              2, 6, 'Paizo',                2017, 'hard',   240],
                ['Warhammer Fantasy RPG',   2, 6, 'Cubicle 7',            1986, 'hard',   180],
            ],
            'airsoft' => [
                ['Assaut & Défense',     2,  30, null, null, 'medium',  60],
                ['Capture de drapeau',   2,  40, null, null, 'easy',    45],
                ['VIP Escort',           4,  20, null, null, 'medium',  60],
                ['Milsim (24h)',         6,  60, null, null, 'expert', 1440],
                ['Domination de zones',  2,  30, null, null, 'medium',  90],
            ],
        ];

        foreach ($gamesByType as $type => $games) {
            if (!isset($activityMap[$type])) {
                continue;
            }
            $activity = $activityMap[$type];

            foreach ($games as [$name, $min, $max, $publisher, $year, $difficulty, $duration]) {
                $game = new Game();
                $game->setName($name);
                $game->setActivity($activity);
                $game->setMinPlayers($min);
                $game->setMaxPlayers($max);
                $game->setPublisher($publisher);
                $game->setYear($year);
                $game->setDifficulty($difficulty);
                $game->setAverageDuration($duration);
                $game->setDescription($this->faker->sentence(10));
                $game->setIsAvailable($this->faker->boolean(90));
                $manager->persist($game);
            }
        }
    }

    // ------------------------------------------------------------------ //
    //  Consumptions
    // ------------------------------------------------------------------ //

    private function loadConsumptions(ObjectManager $manager, array $users, array $items, array $events): void
    {
        foreach (array_slice($users, 1) as $user) {
            $count = $this->faker->numberBetween(2, 8);
            for ($j = 0; $j < $count; $j++) {
                $item  = $this->faker->randomElement($items);
                $qty   = $this->faker->numberBetween(1, 3);
                $price = (float) $item->getPrice();

                $consumption = new Consumption();
                $consumption->setMember($user);
                $consumption->setItem($item);
                $consumption->setQuantity($qty);
                $consumption->setUnitPrice((string) $price);
                $consumption->setTotalPrice((string) round($price * $qty, 2));
                $consumption->setConsumedAt(
                    \DateTimeImmutable::createFromMutable(
                        $this->faker->dateTimeBetween('-6 months', 'now')
                    )
                );

                if ($this->faker->boolean(40)) {
                    $consumption->setEvent($this->faker->randomElement($events));
                }

                $manager->persist($consumption);
            }
        }
    }

    // ------------------------------------------------------------------ //
    //  Photos
    // ------------------------------------------------------------------ //

    private function loadPhotos(ObjectManager $manager, array $activities, array $events, array $users): void
    {
        $members = array_slice($users, 1);

        foreach ($activities as $activity) {
            $count = $this->faker->numberBetween(3, 8);
            for ($i = 0; $i < $count; $i++) {
                $photo = new Photo();
                $photo->setActivity($activity);
                $photo->setFilename('placeholder_' . $activity->getType() . '_' . ($i + 1) . '.jpg');
                $photo->setCaption($this->faker->sentence(6));
                $photo->setUploadedBy($this->faker->randomElement($members));
                $photo->setIsPublic($this->faker->boolean(80));

                $relatedEvents = array_values(array_filter(
                    $events,
                    fn(Event $e) => $e->getActivity() === $activity
                ));
                if ($relatedEvents && $this->faker->boolean(50)) {
                    $photo->setEvent($this->faker->randomElement($relatedEvents));
                }

                $manager->persist($photo);
            }
        }
    }
}
