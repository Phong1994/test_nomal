<?php

class LECM_Model_Seo_Opencart_Mivamerchant_Custom
{	
	const SEO_ENABLE = TRUE; //Enable Search Engine Friendly URLs
	const HYPER_LINK = false; //Use Hyphens (-) instead of Underscores (_)
	
    public function getCategoriesSeoExport($cart, $category, $categoriesExt) {
		$result = array ();
		$notice = $cart->getNotice ();
		$store_id = 1;
		if (self::SEO_ENABLE) {
			$path = '';
			if (self::HYPER_LINK) {
				$path = str_replace ( ' ', '-', strtolower ( $path ) );
			}
			$path = $category ['CATEGORY_CODE'] . '.html';
		} else {
			$path = 'SearchResults.asp?Cat=' . $category ['categoryid'];
		}
		$result [] = array (
				'store_id' => $store_id,
				'request_path' => $path 
		);
		return $result;
	}

    public function categorySeoImport($cart, $convert, $category, $categoriesExt)
    {
        return false;
    }

    public function afterCategorySeoImport($cart, $category_id, $convert, $category, $categoriesExt)
    {
        return false;
    }

    public function getProductSeoExport($cart, $product, $productsExt) {
		$result = array ();
		$notice = $cart->getNotice ();
		$store_id = 1;
		if (self::SEO_ENABLE) {
			$path = '';
			// change special character to space
			$path = preg_replace ( '/[^a-zA-Z0-9 _-]+/', ' ', $path );
			// change multi space to single space
			$path = preg_replace ( '/\s+/', ' ', $path );
			// change space to hyphen
			$path = str_replace ( ' ', '-', $path );
			// change multi hyphen to single hyphen
			$path = preg_replace ( '/-+/', '-', $path );
			if (self::HYPER_LINK) {
				$path = str_replace ( '_', '-', $path );
			}
			if ($product ['CATEGORY_CODES']) {
				$categories = explode ( ',', $product ['CATEGORY_CODES'] );
				foreach ( $categories as $category ) {
					$path = $category . '/' . $product ['PRODUCT_CODE'] . '.html';
					$result [] = array (
						'store_id' => $store_id,
						'request_path' => $path 
					);
				}
			}
			$path = $product ['PRODUCT_CODE'] . '.html';
			$result [] = array (
				'store_id' => $store_id,
				'request_path' => $path 
			);			
		} else {
			$path = 'ProductDetails.asp?ProductCode=' . $product ['PRODUCT_CODE'];
			$result [] = array (
					'store_id' => $store_id,
					'request_path' => $path
			);
		}		
		
		return $result;
	}
	public function productSeoImport($cart, $convert, $product, $productsExt) {
		return false;
	}
	public function afterProductSeoImport($cart, $product_id, $convert, $product, $productsExt) {
		return false;
	}
}