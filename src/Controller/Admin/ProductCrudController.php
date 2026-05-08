<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;




class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Product')
            ->setEntityLabelInPlural('Products')
            ->setPageTitle('index', 'Product Management')
            ->setPageTitle(
                'detail',
                fn(Product $p) => sprintf(
                    'Product %s', $p->getName()
                )
            )
            ->setDefaultSort(['id' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->showEntityActionsInlined();
    }

    // Fields
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name', 'Name');
        yield SlugField::new('slug')
            ->setTargetFieldName('name')
            ->hideOnIndex();
        yield TextEditorField::new('description', 'Description');
        yield MoneyField::new('price', 'Price')
            ->setCurrency('USD')
            ->setStoredAsCents(false);
        yield IntegerField::new('stock', 'Stock');
        yield AssociationField::new('category', 'Category');
            
        yield ImageField::new('imageFilename', 'Product Image')
            ->setBasePath('uploads/images/products')
            ->setUploadDir('public/images/products')
            ->setUploadedFileNamePattern('uploads/[slug].[extension]')
            ->setRequired($pageName === Crud::PAGE_NEW);
        yield BooleanField::new('isActive', 'Active');
    }
    // ─────────────────────────────────────────
    // filters
    // ─────────────────────────────────────────
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('isActive');
        
    }
    // Actions
    
    
    
}
