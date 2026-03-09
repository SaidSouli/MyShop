<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $category1 = new Category();
        $category1->setName('Coffee');
        $category1->setSlug('coffee');
        $category1->setDescription('Experience the journey from bean to cup. Our curated selection of premium coffees features single-origin beans and signature blends, each roasted to perfection to highlight unique flavor profiles. From the bright, floral acidity of African harvests to the deep, chocolatey undertones of South American estates, discover a world of aroma and craft in every pour.');
        $category2 = new Category();
        $category2->setName('Tea');
        $category2->setSlug('tea');
        $category2->setDescription('Discover the rich tradition of tea-making with our carefully selected blends. From delicate green teas to robust black teas, each variety offers a unique taste experience.');
        $category3 = new Category();
        $category3->setName('Juice');
        $category3->setSlug('juice');
        $category3->setDescription('Savor the natural goodness of fresh-pressed juices made from the finest fruits and vegetables.');
        $category3->setSlug(slug: 'juice');
        $category4 = new Category();
        $category4->setName('Soda');
        $category4->setDescription('Indulge in the effervescent delight of our sodas, bursting with bold flavors and crafted with care.');
        $category4->setSlug('soda');
        $manager->persist($category1);
        $manager->persist($category2);
        $manager->persist($category3);
        $manager->persist($category4);
        $manager->flush();

        $productsData = [
            'Coffee' => [
                [
                    'name' => 'Ethiopian Yirgacheffe',
                    'price' => 18.50,
                    'stock' => 50,
                    'desc' => 'Floral and citrus notes with a light body.',
                    'image' => 'images/coffee/ethiopian-yirgacheffe.jpg',
                ],
                [
                    'name' => 'Colombian Dark Roast',
                    'price' => 15.99,
                    'stock' => 100,
                    'desc' => 'Bold, nutty flavor with a smooth chocolate finish.',
                    'image' => 'images/coffee/colombian-dark-roast.jpg',
                ],
                [
                    'name' => 'Sumatra Mandheling',
                    'price' => 17.25,
                    'stock' => 30,
                    'desc' => 'Earthy, complex, and full-bodied with low acidity.',
                    'image' => 'images/coffee/sumatra-mandheling.jpg',
                ],
                [
                    'name' => 'Espresso House Blend',
                    'price' => 14.00,
                    'stock' => 200,
                    'desc' => 'The perfect base for lattes and cappuccinos.',
                    'image' => 'images/coffee/espresso-blend.jpg',
                ],
                [
                    'name' => 'Decaf Peru',
                    'price' => 16.50,
                    'stock' => 45,
                    'desc' => 'All the flavor of South American coffee without the caffeine.',
                    'image' => 'images/coffee/decaf-peru.jpg',
                ],
                [
                    'name' => 'Costa Rican Tarrazu',
                    'price' => 19.25,
                    'stock' => 35,
                    'desc' => 'Bright acidity with notes of tropical fruit and honey.',
                    'image' => 'images/coffee/costa-rican-tarrazu.jpg',
                ],
                [
                    'name' => 'French Roast',
                    'price' => 14.99,
                    'stock' => 75,
                    'desc' => 'Smoky, bold and intense with low acidity.',
                    'image' => 'images/coffee/french-roast.jpg',
                ],
            ],
            'Tea' => [
                [
                    'name' => 'Organic Jade Green',
                    'price' => 12.00,
                    'stock' => 80,
                    'desc' => 'High-quality steamed green tea leaves from Japan.',
                    'image' => 'images/tea/jade-green.jpg',
                ],
                [
                    'name' => 'Royal Earl Grey',
                    'price' => 10.50,
                    'stock' => 120,
                    'desc' => 'Black tea infused with premium bergamot oil.',
                    'image' => 'images/tea/earl-grey.jpg',
                ],
                [
                    'name' => 'Egyptian Chamomile',
                    'price' => 9.99,
                    'stock' => 60,
                    'desc' => 'Calming herbal tea with notes of apple and honey.',
                    'image' => 'images/tea/chamomile.jpg',
                ],
                [
                    'name' => 'Masala Chai Mix',
                    'price' => 13.75,
                    'stock' => 40,
                    'desc' => 'A spicy blend of black tea, ginger, and cardamom.',
                    'image' => 'images/tea/masala-chai.jpg',
                ],
                [
                    'name' => 'Jasmine Dragon Pearls',
                    'price' => 15.50,
                    'stock' => 55,
                    'desc' => 'Hand-rolled green tea scented with jasmine blossoms.',
                    'image' => 'images/tea/jasmine-pearls.jpg',
                ],
                [
                    'name' => 'Peppermint Herbal',
                    'price' => 8.99,
                    'stock' => 90,
                    'desc' => 'Refreshing and soothing pure peppermint leaves.',
                    'image' => 'images/tea/peppermint.jpg',
                ],
            ],
            'Juice' => [
                [
                    'name' => 'Orange Juice',
                    'price' => 5.99,
                    'stock' => 150,
                    'desc' => 'Freshly squeezed Valencia oranges, pulp-free.',
                    'image' => 'images/juice/orange.jpg',
                ],
                [
                    'name' => 'Apple Cider',
                    'price' => 6.50,
                    'stock' => 85,
                    'desc' => 'Crisp, unfiltered apple cider from local orchards.',
                    'image' => 'images/juice/apple-cider.jpg',
                ],
                [
                    'name' => 'Green Detox Blend',
                    'price' => 7.99,
                    'stock' => 40,
                    'desc' => 'Kale, spinach, cucumber, and green apple.',
                    'image' => 'images/juice/green-detox.jpg',
                ],
                [
                    'name' => 'Tropical Punch',
                    'price' => 6.25,
                    'stock' => 110,
                    'desc' => 'Mango, pineapple, and passion fruit combination.',
                    'image' => 'images/juice/tropical-punch.jpg',
                ],
                [
                    'name' => 'Cranberry Raspberry',
                    'price' => 5.75,
                    'stock' => 95,
                    'desc' => 'Tangy cranberry with sweet raspberry notes.',
                    'image' => 'images/juice/cranberry-raspberry.jpg',
                ],
            ],
            'Sodas' => [
                [
                    'name' => 'Classic Cola',
                    'price' => 2.50,
                    'stock' => 300,
                    'desc' => 'The original refreshing cola taste.',
                    'image' => 'images/sodas/classic-cola.jpg',
                ],
                [
                    'name' => 'Ginger Ale',
                    'price' => 2.75,
                    'stock' => 200,
                    'desc' => 'Crisp and bubbly with real ginger flavor.',
                    'image' => 'images/sodas/ginger-ale.jpg',
                ],
                [
                    'name' => 'Root Beer',
                    'price' => 2.99,
                    'stock' => 180,
                    'desc' => 'Old-fashioned recipe with sassafras and vanilla.',
                    'image' => 'images/sodas/root-beer.jpg',
                ],
                [
                    'name' => 'Lemon Lime Soda',
                    'price' => 2.50,
                    'stock' => 250,
                    'desc' => 'Zesty citrus blend that tickles your tongue.',
                    'image' => 'images/sodas/lemon-lime.jpg',
                ],
            ],
        ];
        $categoryMap = [
            'Coffee' => $category1,
            'Tea' => $category2,
            'Juice' => $category3,
            'Sodas' => $category4,
        ];

        foreach ($productsData as $categoryName => $products) {
            foreach ($products as $Data) {
                $product = new Product();
                $product->setName($Data['name']);
                $product->setPrice($Data['price']);
                $product->setStock($Data['stock']);
                $product->setDescription($Data['desc']);
                $product->setImage($Data['image']);
                $product->setCategory($categoryMap[$categoryName]);
                $product->setCreatedAt(new \DateTimeImmutable());
                $manager->persist($product);
            }
        }
        $manager->flush();
    }
}
