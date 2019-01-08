<?php

class LECM_Model_Seo_Oscommerce_Oscommerce_Default
{
    public function getCategoriesSeoExport($cart, $category, $categoriesExt)
    {
        return false;
    }

    public function categorySeoImport($cart, $convert, $category, $categoriesExt)
    {
        return false;
    }

    public function afterCategorySeoImport($cart, $category_id, $convert, $category, $categoriesExt)
    {
        return false;
    }

    public function getProductSeoExport($cart, $product, $productsExt)
    {
        return false;
    }

    public function productSeoImport($cart, $convert, $product, $productsExt)
    {
        return false;
    }

    public function afterProductSeoImport($cart, $product_id, $convert, $product, $productsExt)
    {
        return false;
    }
}