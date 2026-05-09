<?php

namespace App\Service;

use App\Entity\Product;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CategoryImageNamer
{
    public function name(UploadedFile $file, object $entity): string
    {
        if (!$entity instanceof Product) {
            return $file->getClientOriginalName();
        }

        $category = $entity->getCategory();
        $categorySlug = $category
            ? strtolower(str_replace([' ', '&', '-'], '_', $category->getName()))
            : 'uncategorized';

        $slug = $entity->getSlug() ?: uniqid();
        $extension = $file->guessExtension() ?? $file->getClientOriginalExtension();

        // Ensure the category subfolder exists
        $uploadDir = dirname(__DIR__, 2) . '/public/images/products/' . $categorySlug;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        return $categorySlug . '/' . $slug . '.' . $extension;
    }
}