<?php
namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'app:download-images', description: 'Downloads high-quality product images')]
class DownloadProductImagesCommand extends Command
{
    private const IMAGE_DIR = 'public/images/products/';

    // Key = folder name (matches generateRandomImage logic)
    // Value = array of [product_type => keyword] pairs
    private const PRODUCT_IMAGES = [
        'electronics' => [
            'smartphone'   => 'smartphone',
            'laptop'       => 'laptop computer',
            'tablet'       => 'tablet device',
            'headphones'   => 'headphones',
            'smart_watch'  => 'smartwatch',
            'camera'       => 'digital camera',
            'speaker'      => 'bluetooth speaker',
            'monitor'      => 'computer monitor',
            'keyboard'     => 'computer keyboard',
            'mouse'        => 'computer mouse',
        ],
        'clothing' => [
            'tshirt'       => 'tshirt fashion',
            'jeans'        => 'jeans denim',
            'jacket'       => 'jacket coat',
            'dress'        => 'dress fashion',
            'sweater'      => 'sweater knitwear',
            'shorts'       => 'shorts casual',
            'hat'          => 'hat cap',
            'scarf'        => 'scarf',
            'hoodie'       => 'hoodie sweatshirt',
            'sneakers'     => 'sneakers shoes',
        ],
        'books' => [
            'novel'        => 'novel book',
            'cookbook'     => 'cookbook food',
            'guide'        => 'travel guide book',
            'biography'    => 'biography book',
            'textbook'     => 'textbook study',
            'magazine'     => 'magazine print',
            'journal'      => 'journal notebook',
            'dictionary'   => 'dictionary reference',
            'encyclopedia' => 'encyclopedia books',
        ],
        'home_garden' => [
            'lamp'         => 'table lamp lighting',
            'chair'        => 'armchair furniture',
            'table'        => 'wooden table furniture',
            'vase'         => 'flower vase decor',
            'rug'          => 'area rug carpet',
            'plant_pot'    => 'plant pot indoor',
            'tool_set'     => 'garden tool set',
            'storage_box'  => 'storage box organizer',
            'cushion'      => 'cushion pillow',
            'curtain'      => 'window curtain drape',
        ],
        'sports_outdoors' => [
            'yoga_mat'     => 'yoga mat exercise',
            'dumbbell'     => 'dumbbell weight gym',
            'tent'         => 'camping tent',
            'backpack'     => 'hiking backpack',
            'bicycle'      => 'bicycle cycling',
            'fishing_rod'  => 'fishing rod',
            'soccer_ball'  => 'soccer ball football',
            'tennis_racket'=> 'tennis racket',
            'camping_stove'=> 'camping stove outdoor',
        ],
        'toys_games' => [
            'puzzle'       => 'jigsaw puzzle',
            'board_game'   => 'board game family',
            'action_figure'=> 'action figure toy',
            'doll'         => 'doll toy',
            'building_set' => 'building blocks toy',
            'stuffed_animal'=> 'stuffed animal plush',
            'remote_car'   => 'remote control car',
            'lego_set'     => 'lego building set',
            'dinosaur_toy' => 'dinosaur toy figurine',
        ],
    ];

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filesystem = new Filesystem();

        foreach (self::PRODUCT_IMAGES as $folder => $productTypes) {
            $io->section("Downloading images for: $folder");
            $path = self::IMAGE_DIR . $folder;
            $filesystem->mkdir($path);

            foreach ($productTypes as $typeKey => $keyword) {
                $filename = $typeKey . '.jpg';
                $fullPath = $path . '/' . $filename;

                $url = 'https://loremflickr.com/800/800/' . urlencode($keyword);
                $io->text("Fetching '$keyword' → $folder/$filename");

                try {
                    copy($url, $fullPath);
                } catch (\Exception $e) {
                    $io->error("Failed: $folder/$filename");
                }
            }
        }

        $io->success('All images downloaded!');
        return Command::SUCCESS;
    }
}